<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TourBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\TourVatInvoiceGeneratedMail;
use App\Mail\TourVatInvoiceRejectedMail;
use App\Services\TourVatInvoiceService;

class AdminTourVatInvoiceController extends Controller
{
    protected $tourVatInvoiceService;

    public function __construct(TourVatInvoiceService $tourVatInvoiceService)
    {
        $this->tourVatInvoiceService = $tourVatInvoiceService;
    }

    /**
     * Hiển thị danh sách yêu cầu VAT invoice
     */
    public function index()
    {
        $vatRequests = TourBooking::where('need_vat_invoice', true)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.tour-vat-invoices.index', compact('vatRequests'));
    }

    /**
     * Hiển thị chi tiết yêu cầu VAT invoice
     */
    public function show($id)
    {
        $tourBooking = TourBooking::where('need_vat_invoice', true)
            ->with(['user', 'tourBookingRooms.roomType'])
            ->findOrFail($id);

        return view('admin.tour-vat-invoices.show', compact('tourBooking'));
    }

    /**
     * Xử lý yêu cầu VAT invoice - tạo hóa đơn
     */
    public function generateVatInvoice(Request $request, $id)
    {
        $request->validate([
            'vat_invoice_number' => 'required|string|max:100|unique:tour_bookings,vat_invoice_number',
            'notes' => 'nullable|string|max:500'
        ]);

        $tourBooking = TourBooking::where('need_vat_invoice', true)
            ->findOrFail($id);

        // Kiểm tra thông tin công ty
        if (!$tourBooking->company_name || !$tourBooking->company_tax_code || !$tourBooking->company_email) {
            return back()->with('error', 'Thông tin công ty chưa đầy đủ để tạo hóa đơn VAT.');
        }

        try {
            DB::beginTransaction();

            // Cập nhật thông tin VAT invoice
            $tourBooking->update([
                'vat_invoice_number' => $request->vat_invoice_number,
                'vat_invoice_created_at' => now()
            ]);

            // Tạo file hóa đơn VAT sử dụng service
            try {
                $filePath = $this->tourVatInvoiceService->generateVatInvoice($tourBooking);
                if (!$filePath) {
                    throw new \Exception('Không thể tạo file hóa đơn VAT');
                }
                Log::info('VAT invoice file created successfully', [
                    'tour_booking_id' => $tourBooking->id,
                    'vat_invoice_number' => $request->vat_invoice_number,
                    'file_path' => $filePath
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to create VAT invoice file', [
                    'tour_booking_id' => $tourBooking->id,
                    'error' => $e->getMessage()
                ]);
                throw new \Exception('Không thể tạo file hóa đơn VAT: ' . $e->getMessage());
            }

            // Gửi email thông báo cho khách hàng
            $emailSent = false;
            try {
                Mail::to($tourBooking->company_email)
                    ->send(new TourVatInvoiceGeneratedMail($tourBooking));
                
                $emailSent = true;
                Log::info('Tour VAT invoice generated email sent', [
                    'tour_booking_id' => $tourBooking->id,
                    'vat_invoice_number' => $request->vat_invoice_number,
                    'company_email' => $tourBooking->company_email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send tour VAT invoice generated email', [
                    'tour_booking_id' => $tourBooking->id,
                    'company_email' => $tourBooking->company_email,
                    'error' => $e->getMessage(),
                    'mail_config' => [
                        'driver' => config('mail.default'),
                        'from_address' => config('mail.from.address'),
                        'from_name' => config('mail.from.name')
                    ]
                ]);
            }

            DB::commit();

            if ($emailSent) {
                return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                    ->with('success', 'Hóa đơn VAT đã được tạo thành công và email thông báo đã được gửi!');
            } else {
                return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                    ->with('success', 'Hóa đơn VAT đã được tạo thành công!')
                    ->with('warning', 'Email thông báo gửi thất bại. Vui lòng kiểm tra cấu hình email.');
            }

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error generating tour VAT invoice: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi tạo hóa đơn VAT: ' . $e->getMessage());
        }
    }

    /**
     * Từ chối yêu cầu VAT invoice
     */
    public function rejectVatRequest(Request $request, $id)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $tourBooking = TourBooking::where('need_vat_invoice', true)
            ->findOrFail($id);

        try {
            DB::beginTransaction();

            // Lưu email công ty trước khi xóa
            $companyEmail = $tourBooking->company_email;

            // Cập nhật trạng thái
            $tourBooking->update([
                'need_vat_invoice' => false,
                'company_name' => null,
                'company_tax_code' => null,
                'company_address' => null,
                'company_email' => null,
                'company_phone' => null
            ]);

            // Gửi email thông báo từ chối
            if ($companyEmail) {
                try {
                    Mail::to($companyEmail)
                        ->send(new TourVatInvoiceRejectedMail($tourBooking, $request->rejection_reason));
                    
                    Log::info('Tour VAT invoice request rejected email sent', [
                        'tour_booking_id' => $tourBooking->id,
                        'company_email' => $companyEmail,
                        'reason' => $request->rejection_reason
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send tour VAT invoice rejected email', [
                        'tour_booking_id' => $tourBooking->id,
                        'company_email' => $companyEmail,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Vẫn từ chối dù email thất bại
                    return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                        ->with('success', 'Yêu cầu VAT invoice đã được từ chối.')
                        ->with('warning', 'Email thông báo gửi thất bại. Vui lòng liên hệ khách hàng trực tiếp.');
                }
            }

            DB::commit();

            return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                ->with('success', 'Yêu cầu VAT invoice đã được từ chối và email thông báo đã được gửi!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error rejecting tour VAT invoice request: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi từ chối yêu cầu: ' . $e->getMessage());
        }
    }

    /**
     * Tải xuống hóa đơn VAT (nếu có)
     */
    public function downloadVatInvoice($id)
    {
        $tourBooking = TourBooking::findOrFail($id);

        if (!$tourBooking->vat_invoice_number) {
            return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                ->with('error', 'Hóa đơn VAT chưa được tạo.');
        }

        // Logic tải xuống hóa đơn VAT - sử dụng file path từ database
        if (empty($tourBooking->vat_invoice_file_path)) {
            return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                ->with('error', 'Hóa đơn VAT chưa có file. Vui lòng tạo lại file.');
        }
        
        $filePath = storage_path('app/' . $tourBooking->vat_invoice_file_path);
        
        if (!file_exists($filePath)) {
            try {
                // Tạo file mẫu nếu không tồn tại
                $this->tourVatInvoiceService->generateVatInvoice($tourBooking);
                $tourBooking->refresh(); // Refresh để lấy file path mới
                
                if (empty($tourBooking->vat_invoice_file_path)) {
                    return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                        ->with('error', 'Không thể tạo file hóa đơn VAT. Vui lòng liên hệ quản trị viên.');
                }
                
                $filePath = storage_path('app/' . $tourBooking->vat_invoice_file_path);
                if (!file_exists($filePath)) {
                    return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                        ->with('error', 'File hóa đơn VAT không tồn tại sau khi tạo. Vui lòng liên hệ quản trị viên.');
                }
            } catch (\Exception $e) {
                Log::error('Error creating VAT invoice file for download', [
                    'tour_booking_id' => $tourBooking->id,
                    'error' => $e->getMessage()
                ]);
                return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                    ->with('error', 'Lỗi khi tạo file hóa đơn VAT: ' . $e->getMessage());
            }
        }
        
        // Kiểm tra file có thể đọc được
        if (!is_readable($filePath)) {
            return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                ->with('error', 'File hóa đơn VAT không thể đọc được. Vui lòng liên hệ quản trị viên.');
        }
        
        // Kiểm tra file không rỗng
        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize === 0) {
            return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                ->with('error', 'File hóa đơn VAT rỗng. Vui lòng tạo lại file.');
        }
        
        try {
            return response()->download(
                $filePath,
                "VAT_Invoice_{$tourBooking->vat_invoice_number}.pdf",
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'attachment; filename="VAT_Invoice_' . $tourBooking->vat_invoice_number . '.pdf"'
                ]
            );
        } catch (\Exception $e) {
            Log::error('Error downloading VAT invoice file', [
                'tour_booking_id' => $tourBooking->id,
                'file_path' => $filePath,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                ->with('error', 'Lỗi khi tải xuống file hóa đơn VAT: ' . $e->getMessage());
        }
    }

    /**
     * Tạo lại file hóa đơn VAT
     */
    public function regenerateVatInvoice($id)
    {
        $tourBooking = TourBooking::findOrFail($id);

        if (!$tourBooking->vat_invoice_number) {
            return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                ->with('error', 'Hóa đơn VAT chưa được tạo.');
        }

        try {
            // Xóa file cũ nếu tồn tại
            $oldFilePath = storage_path("app/vat-invoices/tour-{$tourBooking->vat_invoice_number}.pdf");
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
                Log::info('Old VAT invoice file deleted', [
                    'tour_booking_id' => $tourBooking->id,
                    'file_path' => $oldFilePath
                ]);
            }

            // Tạo file mới
            $filePath = $this->tourVatInvoiceService->generateVatInvoice($tourBooking);
            
            if ($filePath) {
                // Kiểm tra file đã được tạo trong storage public
                $fullPath = storage_path('app/' . $filePath);
                if (file_exists($fullPath)) {
                    return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                        ->with('success', 'File hóa đơn VAT đã được tạo lại thành công!');
                } else {
                    return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                        ->with('error', 'File đã được tạo nhưng không tìm thấy trong hệ thống.');
                }
            } else {
                return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                    ->with('error', 'Không thể tạo lại file hóa đơn VAT.');
            }
            
        } catch (\Exception $e) {
            Log::error('Error regenerating VAT invoice file: ' . $e->getMessage());
            return redirect()->route('admin.tour-bookings.show', $tourBooking->id)
                ->with('error', 'Có lỗi xảy ra khi tạo lại file hóa đơn VAT: ' . $e->getMessage());
        }
    }

    /**
     * Tạo file hóa đơn VAT mẫu
     */
    private function createSampleVatInvoice(TourBooking $tourBooking)
    {
        try {
            $directory = storage_path('app/vat-invoices');
            
            // Tạo thư mục nếu không tồn tại
            if (!is_dir($directory)) {
                if (!mkdir($directory, 0755, true)) {
                    throw new \Exception("Không thể tạo thư mục: {$directory}");
                }
            }

            // Kiểm tra quyền ghi
            if (!is_writable($directory)) {
                throw new \Exception("Thư mục không có quyền ghi: {$directory}");
            }

            $filePath = storage_path("app/vat-invoices/tour-{$tourBooking->vat_invoice_number}.pdf");
            
            // Tạo PDF từ template HTML
            $html = view('emails.tour-vat-invoice-template', compact('tourBooking'))->render();
            
            // Tạo PDF sử dụng DomPDF
            $pdf = Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            
            // Lưu PDF
            if (!$pdf->save($filePath)) {
                throw new \Exception("Không thể tạo file PDF: {$filePath}");
            }
            
            // Kiểm tra file đã được tạo thành công
            if (!file_exists($filePath)) {
                throw new \Exception("File không tồn tại sau khi tạo: {$filePath}");
            }
            
            $fileSize = filesize($filePath);
            if ($fileSize === false || $fileSize === 0) {
                throw new \Exception("File rỗng hoặc không thể đọc kích thước: {$filePath}");
            }
            
            Log::info('VAT invoice PDF created successfully', [
                'tour_booking_id' => $tourBooking->id,
                'file_path' => $filePath,
                'file_size' => $fileSize,
                'file_exists' => file_exists($filePath),
                'file_readable' => is_readable($filePath)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create VAT invoice PDF', [
                'tour_booking_id' => $tourBooking->id,
                'error' => $e->getMessage(),
                'directory' => storage_path('app/vat-invoices'),
                'directory_exists' => is_dir(storage_path('app/vat-invoices')),
                'directory_writable' => is_writable(storage_path('app/vat-invoices')),
                'storage_path' => storage_path('app/vat-invoices'),
                'current_working_dir' => getcwd()
            ]);
            throw $e;
        }
    }

    /**
     * Tạo nội dung hóa đơn VAT
     */
    private function generateVatInvoiceContent(TourBooking $tourBooking)
    {
        $content = "HÓA ĐƠN VAT\n";
        $content .= str_repeat("=", 50) . "\n\n";
        
        $content .= "Số hóa đơn: {$tourBooking->vat_invoice_number}\n";
        $content .= "Ngày tạo: " . now()->format('d/m/Y H:i') . "\n";
        $content .= "Mã tour: {$tourBooking->booking_code}\n\n";
        
        $content .= "THÔNG TIN KHÁCH HÀNG\n";
        $content .= str_repeat("-", 30) . "\n";
        $content .= "Tên công ty: {$tourBooking->company_name}\n";
        $content .= "Mã số thuế: {$tourBooking->company_tax_code}\n";
        $content .= "Địa chỉ: {$tourBooking->company_address}\n";
        $content .= "Email: {$tourBooking->company_email}\n";
        $content .= "Điện thoại: {$tourBooking->company_phone}\n\n";
        
        $content .= "THÔNG TIN TOUR\n";
        $content .= str_repeat("-", 30) . "\n";
        $content .= "Tên tour: {$tourBooking->tour_name}\n";
        $content .= "Số khách: {$tourBooking->total_guests} người\n";
        $content .= "Số phòng: {$tourBooking->total_rooms} phòng\n";
        $content .= "Check-in: {$tourBooking->check_in_date->format('d/m/Y')}\n";
        $content .= "Check-out: {$tourBooking->check_out_date->format('d/m/Y')}\n";
        $content .= "Số đêm: " . $tourBooking->check_in_date->diffInDays($tourBooking->check_out_date) . " đêm\n\n";
        
        $content .= "CHI TIẾT GIÁ\n";
        $content .= str_repeat("-", 30) . "\n";
        $content .= "Tổng tiền phòng: " . number_format($tourBooking->total_rooms_amount, 0, ',', '.') . " VNĐ\n";
        $content .= "Tổng tiền dịch vụ: " . number_format($tourBooking->total_services_amount, 0, ',', '.') . " VNĐ\n";
        $content .= "Tổng cộng: " . number_format($tourBooking->total_amount_before_discount, 0, ',', '.') . " VNĐ\n";
        
        if ($tourBooking->promotion_discount > 0) {
            $content .= "Giảm giá: -" . number_format($tourBooking->promotion_discount, 0, ',', '.') . " VNĐ\n";
            $content .= "Giá cuối: " . number_format($tourBooking->final_amount, 0, ',', '.') . " VNĐ\n";
        }
        
        $content .= "Thuế VAT (10%): " . number_format($tourBooking->final_amount * 0.1, 0, ',', '.') . " VNĐ\n";
        $content .= "TỔNG CỘNG: " . number_format($tourBooking->final_amount * 1.1, 0, ',', '.') . " VNĐ\n\n";
        
        $content .= "Ghi chú:\n";
        $content .= "- Hóa đơn này được tạo tự động bởi hệ thống\n";
        $content .= "- Vui lòng giữ hóa đơn này để làm bằng chứng thanh toán\n";
        $content .= "- Mọi thắc mắc vui lòng liên hệ: 1900-xxxx\n\n";
        
        $content .= str_repeat("=", 50) . "\n";
        $content .= "Cảm ơn quý khách đã sử dụng dịch vụ của chúng tôi!\n";
        
        return $content;
    }

    /**
     * Xem trước hóa đơn VAT
     */
    public function previewVatInvoice($id)
    {
        try {
            $tourBooking = TourBooking::findOrFail($id);
            
            if (!$tourBooking) {
                abort(404, 'Không tìm thấy tour booking');
            }

            // Tạo hóa đơn VAT nếu chưa có
            if (empty($tourBooking->vat_invoice_file_path)) {
                $filePath = $this->tourVatInvoiceService->generateVatInvoice($tourBooking);
                if (!$filePath) {
                    abort(500, 'Không thể tạo hóa đơn VAT');
                }
                $tourBooking->refresh();
            }

            if (empty($tourBooking->vat_invoice_file_path)) {
                abort(500, 'Không thể tạo hóa đơn VAT');
            }

            $fullPath = storage_path('app/' . $tourBooking->vat_invoice_file_path);
            
            if (!file_exists($fullPath)) {
                abort(404, 'File hóa đơn VAT không tồn tại');
            }

            return response()->file($fullPath);
        } catch (\Exception $e) {
            Log::error('Error previewing Tour VAT invoice: ' . $e->getMessage());
            abort(500, 'Có lỗi xảy ra khi xem trước hóa đơn VAT');
        }
    }

    /**
     * Gửi email hóa đơn VAT
     */
    public function sendVatInvoice($id)
    {
        try {
            $tourBooking = TourBooking::findOrFail($id);
            $ok = $this->tourVatInvoiceService->sendVatInvoiceEmail($tourBooking);
            return back()->with($ok ? 'success' : 'error', $ok ? 'Đã gửi email hóa đơn VAT' : 'Không thể gửi email hóa đơn VAT');
        } catch (\Exception $e) {
            Log::error('Error sending Tour VAT invoice: ' . $e->getMessage());
            return back()->with('error', 'Có lỗi xảy ra khi gửi email hóa đơn VAT');
        }
    }

    /**
     * Thống kê VAT invoice
     */
    public function statistics()
    {
        $stats = [
            'total_requests' => TourBooking::where('need_vat_invoice', true)->count(),
            'pending_requests' => TourBooking::where('need_vat_invoice', true)
                ->whereNull('vat_invoice_number')
                ->count(),
            'generated_invoices' => TourBooking::whereNotNull('vat_invoice_number')->count(),
            'total_revenue' => TourBooking::whereNotNull('vat_invoice_number')
                ->sum('final_amount')
        ];

        return view('admin.tour-vat-invoices.statistics', compact('stats'));
    }
}
