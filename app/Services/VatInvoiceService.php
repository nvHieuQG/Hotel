<?php

namespace App\Services;

use App\Interfaces\Services\VatInvoiceServiceInterface;
use App\Models\Booking;
use App\Models\TourBooking;
use App\Models\Payment;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class VatInvoiceService implements VatInvoiceServiceInterface
{
    public function saveClientVatRequest(Booking $booking, array $info): void
    {
        $booking->update([
            'vat_invoice_info' => $info,
            'vat_invoice_status' => 'pending',
        ]);
    }

    /**
     * Lưu thông tin yêu cầu xuất hóa đơn VAT từ khách hàng tour booking
     */
    public function saveTourClientVatRequest(TourBooking $tourBooking, array $info): void
    {
        $tourBooking->update([
            'need_vat_invoice' => true,
            'company_name' => $info['company_name'] ?? $info['companyName'] ?? '',
            'company_tax_code' => $info['company_tax_code'] ?? $info['taxCode'] ?? '',
            'company_address' => $info['company_address'] ?? $info['companyAddress'] ?? '',
            'company_email' => $info['company_email'] ?? $info['receiverEmail'] ?? '',
            'company_phone' => $info['company_phone'] ?? $info['receiverPhone'] ?? '',
        ]);
    }

    /**
     * Lấy đường dẫn đầy đủ của file VAT invoice
     */
    private function getVatInvoiceFullPath(string $filePath): string
    {
        return public_path('storage/' . str_replace('public/', '', $filePath));
    }

    /**
     * Kiểm tra điều kiện xuất hóa đơn VAT cho regular booking
     */
    public function ensureLegalPaymentCompliance(Booking $booking): void
    {
        // Sử dụng BookingPriceCalculationService để tính toán nhất quán với admin
        $priceService = app(\App\Services\BookingPriceCalculationService::class);
        $priceData = $priceService->calculateRegularBookingTotal($booking);
        
        // Tổng tiền phải thu (đã bao gồm tất cả)
        $totalDue = $priceData['fullTotal'];

        // Kiểm tra xem khách đã thanh toán hết chưa
        $totalPaid = $booking->payments()
            ->where('status', 'completed')
            ->sum('amount');
        
        if ($totalPaid < $totalDue) {
            throw new \RuntimeException('Khách hàng chưa thanh toán đủ tiền. Tổng tiền: ' . number_format($totalDue) . ' VNĐ, Đã thanh toán: ' . number_format($totalPaid) . ' VNĐ. Vui lòng thanh toán đủ trước khi xuất hóa đơn VAT.');
        }

        // Nếu từ 5 triệu trở lên, chỉ thông báo (không chặn)
        if ($totalDue >= 5000000) {
            // Log thông báo về quy định
            Log::info('VAT invoice compliance notice', [
                'booking_id' => $booking->id,
                'total_amount' => $totalDue,
                'message' => 'Hóa đơn từ 5.000.000đ - Thông báo về quy định chuyển khoản công ty'
            ]);
            
            // Không throw exception, chỉ thông báo
            // Admin vẫn có thể tạo hóa đơn VAT bình thường
        }
        
        // Dưới 5 triệu hoặc đã thanh toán đủ: cho phép tạo hóa đơn VAT
    }

    /**
     * Kiểm tra điều kiện xuất hóa đơn VAT cho tour booking
     * Ghi chú: Không chặn việc tạo hóa đơn VAT khi chưa thanh toán đủ tiền
     */
    public function ensureTourLegalPaymentCompliance(TourBooking $tourBooking): void
    {
        // Tính toán giá trị sử dụng các biến mới
        $roomCost = $tourBooking->tourBookingRooms->sum('total_price');
        $services = $tourBooking->tourBookingServices->sum('total_price');
        $discount = $tourBooking->promotion_discount ?? 0;
        
        // Tổng cộng đã bao gồm VAT 10% - sử dụng final_price nếu có
        if ($tourBooking->final_price && $tourBooking->final_price > 0) {
            $totalDue = $tourBooking->final_price;
        } else {
            $totalDue = $roomCost + $services - $discount;
        }

        // Kiểm tra xem khách đã thanh toán hết chưa (chỉ để thông báo, không chặn)
        $totalPaid = $tourBooking->payments()
            ->where('status', 'completed')
            ->sum('amount');
        
        if ($totalPaid < $totalDue) {
            // Chỉ ghi log thông báo, không throw exception
            Log::warning('Tour VAT invoice payment notice', [
                'tour_booking_id' => $tourBooking->id,
                'total_due' => $totalDue,
                'total_paid' => $totalPaid,
                'remaining' => $totalDue - $totalPaid,
                'message' => 'Khách hàng chưa thanh toán đủ tiền, nhưng vẫn cho phép tạo hóa đơn VAT'
            ]);
            
            // Không throw exception, tiếp tục tạo hóa đơn VAT
        } else {
            Log::info('Tour VAT invoice payment compliance check passed', [
                'tour_booking_id' => $tourBooking->id,
                'total_due' => $totalDue,
                'total_paid' => $totalPaid,
                'message' => 'Khách hàng đã thanh toán đủ tiền'
            ]);
        }

        // Nếu từ 5 triệu trở lên, chỉ thông báo (không chặn)
        if ($totalDue >= 5000000) {
            // Log thông báo về quy định
            Log::info('Tour VAT invoice compliance notice', [
                'tour_booking_id' => $tourBooking->id,
                'total_amount' => $totalDue,
                'message' => 'Hóa đơn từ 5.000.000đ - Thông báo về quy định chuyển khoản công ty'
            ]);
            
            // Không throw exception, chỉ thông báo
            // Admin vẫn có thể tạo hóa đơn VAT bình thường
        }
        
        // Luôn cho phép tạo hóa đơn VAT, không chặn
    }

    /**
     * Kiểm tra trạng thái thanh toán và trả về thông tin cho view (regular booking)
     */
    public function getPaymentStatusInfo(Booking $booking): array
    {
        $priceService = app(\App\Services\BookingPriceCalculationService::class);
        $priceData = $priceService->calculateRegularBookingTotal($booking);
        
        $totalDue = $priceData['fullTotal'];
        $totalPaid = $booking->payments()
            ->where('status', 'completed')
            ->sum('amount');
        
        $isFullyPaid = $totalPaid >= $totalDue;
        $remainingAmount = max(0, $totalDue - $totalPaid);
        $isHighValue = $totalDue >= 5000000;
        
        return [
            'totalDue' => $totalDue,
            'totalPaid' => $totalPaid,
            'isFullyPaid' => $isFullyPaid,
            'remainingAmount' => $remainingAmount,
            'isHighValue' => $isHighValue,
            'canGenerateVat' => $isFullyPaid
        ];
    }

    /**
     * Kiểm tra trạng thái thanh toán và trả về thông tin cho view (tour booking)
     */
    public function getTourPaymentStatusInfo(TourBooking $tourBooking): array
    {
        // Tính toán giá trị sử dụng các biến mới
        $roomCost = $tourBooking->tourBookingRooms->sum('total_price');
        $services = $tourBooking->tourBookingServices->sum('total_price');
        $discount = $tourBooking->promotion_discount ?? 0;
        
        // Tổng cộng đã bao gồm VAT 10% - sử dụng final_price nếu có
        if ($tourBooking->final_price && $tourBooking->final_price > 0) {
            $totalDue = $tourBooking->final_price;
        } else {
            $totalDue = $roomCost + $services - $discount;
        }

        $totalPaid = $tourBooking->payments()
            ->where('status', 'completed')
            ->sum('amount');
        
        $isFullyPaid = $totalPaid >= $totalDue;
        $remainingAmount = max(0, $totalDue - $totalPaid);
        $isHighValue = $totalDue >= 5000000;
        
        return [
            'totalDue' => $totalDue,
            'totalPaid' => $totalPaid,
            'isFullyPaid' => $isFullyPaid,
            'remainingAmount' => $remainingAmount,
            'isHighValue' => $isHighValue,
            'canGenerateVat' => $isFullyPaid,
            'roomCost' => $roomCost,
            'services' => $services,
            'discount' => $discount
        ];
    }

    public function generateVatInvoice(Booking $booking): ?string
    {
        try {
            $this->ensureLegalPaymentCompliance($booking);

            // Xóa file VAT cũ của booking
            $this->deleteOldPdfFiles($booking->booking_id);

            $info = (array) ($booking->vat_invoice_info ?? []);
            
            // Sử dụng BookingPriceCalculationService để tính toán nhất quán với admin
            $priceService = app(\App\Services\BookingPriceCalculationService::class);
            $priceData = $priceService->calculateRegularBookingTotal($booking);
            
            // Lấy các thành phần giá từ service
            $nights = $priceData['nights'];
            $nightly = $priceData['nightly'];
            $roomCost = $priceData['finalRoomCost'];
            $services = $priceData['svcTotal'];
            $guestSurcharge = $priceData['guestSurcharge'];
            $totalDiscount = $priceData['totalDiscount'];
            
            // Tổng cộng đã bao gồm VAT 10%
            $grandTotal = $priceData['fullTotal'];
            
            // Tính ngược lại: giá trước VAT = tổng cộng / (1 + VAT rate)
            $vatRate = 0.1;
            $subtotal = round($grandTotal / (1 + $vatRate));
            $vatAmount = $grandTotal - $subtotal;

            $html = view('emails.vat-invoice-template', compact('booking','info','nightly','nights','roomCost','totalDiscount','services','guestSurcharge','subtotal','vatRate','vatAmount','grandTotal'))->render();

            // Lưu PDF với cấu hình đúng
            $filename = 'vat_invoice_' . $booking->booking_id . '_' . date('Y-m-d_H-i-s') . '.pdf';
            $pathRel = 'vat_invoices/' . $filename;
            $pathAbs = storage_path('app/public/' . $pathRel);
            
            // Tạo thư mục nếu chưa có
            Storage::disk('public')->makeDirectory('vat_invoices');
            
            // Tạo PDF
            $this->createPdfFromHtml($html, $pathAbs);
            
            // Kiểm tra file đã được tạo
            if (!file_exists($pathAbs)) {
                throw new \RuntimeException('Không thể tạo file PDF hóa đơn VAT');
            }

            $booking->update([
                'vat_invoice_status' => 'generated',
                'vat_invoice_generated_at' => now(),
                'vat_invoice_file_path' => 'public/' . $pathRel,
            ]);

            // Log thành công
            Log::info('VAT invoice PDF created successfully', [
                'booking_id' => $booking->id,
                'file_path' => 'public/' . $pathRel,
                'file_size' => filesize($pathAbs)
            ]);

            return 'public/' . $pathRel;
        } catch (\Throwable $e) {
            Log::error('VAT invoice generation failed: '.$e->getMessage());
            return null;
        }
    }

    /**
     * Tạo hóa đơn VAT cho tour booking
     */
    public function generateTourVatInvoice(TourBooking $tourBooking): ?string
    {
        try {
            $this->ensureTourLegalPaymentCompliance($tourBooking);

            // Xóa file VAT cũ của tour booking
            $this->deleteOldPdfFiles($tourBooking->id);

            // Tính toán giá trị sử dụng các biến mới
            $nights = $tourBooking->check_in_date && $tourBooking->check_out_date 
                ? $tourBooking->check_in_date->copy()->startOfDay()->diffInDays($tourBooking->check_out_date->copy()->startOfDay()) 
                : 0;
            
            // Sử dụng các biến mới đã được tính toán
            $roomCost = $tourBooking->tourBookingRooms->sum('total_price');
            $services = $tourBooking->tourBookingServices->sum('total_price');
            $discount = $tourBooking->promotion_discount ?? 0;
            
            // Tổng cộng đã bao gồm VAT 10% - sử dụng final_price nếu có
            if ($tourBooking->final_price && $tourBooking->final_price > 0) {
                $grandTotal = $tourBooking->final_price;
            } else {
                $grandTotal = $roomCost + $services - $discount;
            }
            
            // Tính ngược lại: giá trước VAT = tổng cộng / (1 + VAT rate)
            $vatRate = 0.1;
            $subtotal = round($grandTotal / (1 + $vatRate));
            $vatAmount = $grandTotal - $subtotal;

            // Chuẩn bị thông tin công ty
            $info = [
                'companyName' => $tourBooking->company_name ?? 'N/A',
                'taxCode' => $tourBooking->company_tax_code ?? 'N/A',
                'companyAddress' => $tourBooking->company_address ?? 'N/A',
                'receiverEmail' => $tourBooking->company_email ?? $tourBooking->user->email ?? 'N/A',
                'receiverName' => $tourBooking->company_name ?? $tourBooking->user->name ?? 'N/A',
            ];

            $html = view('emails.tour-vat-invoice-template', compact(
                'tourBooking', 'info', 'nights', 'roomCost', 'discount', 
                'services', 'subtotal', 'vatRate', 'vatAmount', 'grandTotal'
            ))->render();

            // Lưu PDF vào thư mục public/storage/vat_invoices
            $filename = 'tour_vat_invoice_' . $tourBooking->id . '_' . date('Y-m-d_H-i-s') . '.pdf';
            $pathRel = 'vat_invoices/' . $filename;
            $pathAbs = public_path('storage/' . $pathRel);
            
            // Tạo thư mục nếu chưa có
            $directory = dirname($pathAbs);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    throw new \RuntimeException("Không thể tạo thư mục: {$directory}");
                }
            }
            
            $this->createPdfFromHtml($html, $pathAbs);

            // Kiểm tra file đã được tạo thành công
            if (!file_exists($pathAbs)) {
                throw new \RuntimeException("File PDF không tồn tại sau khi tạo: {$pathAbs}");
            }
            
            $fileSize = filesize($pathAbs);
            if ($fileSize === false || $fileSize === 0) {
                throw new \RuntimeException("File PDF rỗng hoặc không thể đọc kích thước: {$pathAbs}");
            }

            // Cập nhật file path vào database (giống regular booking)
            $updateResult = $tourBooking->update([
                'vat_invoice_status' => 'generated',
                'vat_invoice_generated_at' => now(),
                'vat_invoice_file_path' => 'public/' . $pathRel,
            ]);
            
            // Refresh dữ liệu để đảm bảo cập nhật thành công
            $tourBooking->refresh();
            
            // Kiểm tra xem cập nhật có thành công không
            if (!$updateResult) {
                Log::error('Failed to update tour booking database', [
                    'tour_booking_id' => $tourBooking->id,
                    'vat_invoice_file_path' => 'public/' . $pathRel
                ]);
                throw new \RuntimeException('Không thể cập nhật database tour booking');
            }
            
            Log::info('Tour VAT invoice database updated successfully', [
                'tour_booking_id' => $tourBooking->id,
                'vat_invoice_file_path' => 'public/' . $pathRel,
                'db_file_path' => $tourBooking->vat_invoice_file_path,
                'update_result' => $updateResult,
                'file_exists' => file_exists($pathAbs),
                'file_size' => $fileSize
            ]);

            Log::info('Tour VAT invoice PDF created successfully', [
                'tour_booking_id' => $tourBooking->id,
                'file_path' => 'public/' . $pathRel,
                'full_path' => $pathAbs,
                'file_exists' => file_exists($pathAbs),
                'file_size' => file_exists($pathAbs) ? filesize($pathAbs) : 0
            ]);

            return 'public/' . $pathRel;
        } catch (\Throwable $e) {
            Log::error('Tour VAT invoice generation failed: '.$e->getMessage());
            return null;
        }
    }

    public function sendVatInvoiceEmail(Booking $booking): bool
    {
        try {
            if ($booking->vat_invoice_status === 'pending' || empty($booking->vat_invoice_file_path)) {
                $this->generateVatInvoice($booking);
            }
            if (empty($booking->vat_invoice_file_path)) return false;

            $info = (array) ($booking->vat_invoice_info ?? []);
            $to = $info['receiverEmail'] ?? $booking->user->email;
            $name = $info['receiverName'] ?? $booking->user->name;

            // Sử dụng BookingPriceCalculationService để tính toán nhất quán
            $priceService = app(\App\Services\BookingPriceCalculationService::class);
            $priceData = $priceService->calculateRegularBookingTotal($booking);
            
            // Tính toán VAT
            $grandTotal = $priceData['fullTotal'];
            $vatRate = 0.1;
            $subtotal = round($grandTotal / (1 + $vatRate));
            $vatAmount = $grandTotal - $subtotal;

            Mail::send('emails.vat-invoice', [
                'booking' => $booking,
                'info' => $info,
                'priceData' => $priceData,
                'subtotal' => $subtotal,
                'vatAmount' => $vatAmount,
                'grandTotal' => $grandTotal,
            ], function ($message) use ($to, $name, $booking) {
                $message->to($to, $name)
                    ->subject('Hóa đơn VAT - Đặt phòng #' . $booking->booking_id)
                    ->attach($this->getVatInvoiceFullPath($booking->vat_invoice_file_path), [
                        'as' => 'vat_invoice_' . $booking->booking_id . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
            });

            // Cập nhật trạng thái đã gửi email
            $booking->update([
                'vat_invoice_sent_at' => now(),
            ]);

            Log::info('VAT invoice email sent successfully', [
                'booking_id' => $booking->id,
                'recipient' => $to,
                'file_path' => $booking->vat_invoice_file_path
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('VAT invoice email failed: ' . $e->getMessage(), [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Gửi email hóa đơn VAT cho tour booking
     * Hoạt động giống hệt như regular booking
     */
    public function sendTourVatInvoiceEmail(TourBooking $tourBooking): bool
    {
        try {
            // Tự động tạo hóa đơn VAT nếu chưa có (giống regular booking)
            if (empty($tourBooking->vat_invoice_file_path)) {
                $filePath = $this->generateTourVatInvoice($tourBooking);
                if (!$filePath) {
                    Log::error('Failed to generate VAT invoice for email', [
                        'tour_booking_id' => $tourBooking->id
                    ]);
                    return false;
                }
                $tourBooking->refresh(); // Refresh để lấy file path mới
            }
            
            if (empty($tourBooking->vat_invoice_file_path)) {
                Log::error('VAT invoice file path is empty after generation', [
                    'tour_booking_id' => $tourBooking->id
                ]);
                return false;
            }

            // Chuẩn bị thông tin người nhận (giống regular booking)
            $info = [
                'receiverEmail' => $tourBooking->company_email ?? $tourBooking->user->email,
                'receiverName' => $tourBooking->company_name ?? $tourBooking->user->name,
            ];

            // Tính toán giá trị VAT (giống regular booking)
            $roomCost = $tourBooking->tourBookingRooms->sum('total_price');
            $services = $tourBooking->tourBookingServices->sum('total_price');
            $discount = $tourBooking->promotion_discount ?? 0;
            
            // Tổng cộng đã bao gồm VAT 10% - sử dụng final_price nếu có
            if ($tourBooking->final_price && $tourBooking->final_price > 0) {
                $grandTotal = $tourBooking->final_price;
            } else {
                $grandTotal = $roomCost + $services - $discount;
            }
            
            // Tính ngược lại: giá trước VAT = tổng cộng / (1 + VAT rate)
            $vatRate = 0.1;
            $subtotal = round($grandTotal / (1 + $vatRate));
            $vatAmount = $grandTotal - $subtotal;

            // Chuẩn bị dữ liệu cho email (giống regular booking)
            $priceData = [
                'nights' => $tourBooking->check_in_date && $tourBooking->check_out_date 
                    ? $tourBooking->check_in_date->copy()->startOfDay()->diffInDays($tourBooking->check_out_date->copy()->startOfDay()) 
                    : 0,
                'roomCost' => $roomCost,
                'services' => $services,
                'discount' => $discount,
                'grandTotal' => $grandTotal,
                'subtotal' => $subtotal,
                'vatAmount' => $vatAmount,
                'vatRate' => $vatRate
            ];

            Log::info('Attempting to send Tour VAT invoice email', [
                'tour_booking_id' => $tourBooking->id,
                'receiver_email' => $info['receiverEmail'],
                'file_path' => $tourBooking->vat_invoice_file_path,
                'price_data' => $priceData
            ]);

            // Gửi email với template và dữ liệu đầy đủ (giống regular booking)
            Mail::send('emails.tour-vat-invoice', [
                'tourBooking' => $tourBooking,
                'info' => $info,
                'priceData' => $priceData,
                'subtotal' => $subtotal,
                'vatAmount' => $vatAmount,
                'grandTotal' => $grandTotal,
            ], function ($message) use ($info, $tourBooking) {
                $message->to($info['receiverEmail'], $info['receiverName'])
                    ->subject('Hóa đơn VAT - Tour Booking #' . $tourBooking->id)
                    ->attach($this->getVatInvoiceFullPath($tourBooking->vat_invoice_file_path), [
                        'as' => 'tour_vat_invoice_' . $tourBooking->id . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
            });

            // Cập nhật trạng thái đã gửi email (giống regular booking)
            $tourBooking->update([
                'vat_invoice_sent_at' => now(),
            ]);

            Log::info('Tour VAT invoice email sent successfully', [
                'tour_booking_id' => $tourBooking->id,
                'recipient' => $info['receiverEmail'],
                'file_path' => $tourBooking->vat_invoice_file_path
            ]);

            return true;
        } catch (\Throwable $e) {
            Log::error('Tour VAT invoice email failed: ' . $e->getMessage(), [
                'tour_booking_id' => $tourBooking->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Xóa file PDF cũ
     * Xử lý cả regular booking và tour booking
     */
    private function deleteOldPdfFiles(string $identifier): void
    {
        try {
            // Tìm và xóa file cũ trong thư mục public/storage/vat_invoices
            $vatInvoicesDir = public_path('storage/vat_invoices');
            if (is_dir($vatInvoicesDir)) {
                // Xóa file tour booking VAT invoice
                $tourFiles = glob($vatInvoicesDir . '/tour_vat_invoice_' . $identifier . '_*.pdf');
                foreach ($tourFiles as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                        Log::info('Deleted old Tour VAT invoice file', ['file' => $file, 'identifier' => $identifier]);
                    }
                }
                
                // Xóa file regular booking VAT invoice
                $regularFiles = glob($vatInvoicesDir . '/vat_invoice_' . $identifier . '_*.pdf');
                foreach ($regularFiles as $file) {
                    if (file_exists($file)) {
                        unlink($file);
                        Log::info('Deleted old Regular VAT invoice file', ['file' => $file, 'identifier' => $identifier]);
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Failed to delete old VAT invoice files', ['error' => $e->getMessage(), 'identifier' => $identifier]);
        }
    }

    /**
     * Tạo PDF từ HTML
     */
    private function createPdfFromHtml(string $html, string $outputPath): void
    {
        try {
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            
            // Tạo thư mục nếu chưa có
            $directory = dirname($outputPath);
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    throw new \RuntimeException("Không thể tạo thư mục: {$directory}");
                }
            }
            
            // Kiểm tra quyền ghi
            if (!is_writable($directory)) {
                throw new \RuntimeException("Thư mục không có quyền ghi: {$directory}");
            }
            
            // Tạo PDF
            if (!$pdf->save($outputPath)) {
                throw new \RuntimeException("Không thể lưu file PDF: {$outputPath}");
            }
            
            // Kiểm tra file đã được tạo thành công
            if (!file_exists($outputPath)) {
                throw new \RuntimeException("File không tồn tại sau khi tạo: {$outputPath}");
            }
            
            $fileSize = filesize($outputPath);
            if ($fileSize === false || $fileSize === 0) {
                throw new \RuntimeException("File rỗng hoặc không thể đọc kích thước: {$outputPath}");
            }
            
            Log::info('PDF created successfully', [
                'output_path' => $outputPath,
                'file_size' => $fileSize,
                'file_exists' => file_exists($outputPath),
                'file_readable' => is_readable($outputPath)
            ]);
            
        } catch (\Throwable $e) {
            Log::error('PDF creation failed: ' . $e->getMessage(), [
                'output_path' => $outputPath,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}


