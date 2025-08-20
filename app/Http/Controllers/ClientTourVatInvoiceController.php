<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TourBooking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Mail\TourVatInvoiceMail;

class ClientTourVatInvoiceController extends Controller
{
    /**
     * Hiển thị form yêu cầu xuất hóa đơn VAT
     */
    public function showVatForm($id)
    {
        $tourBooking = TourBooking::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        return view('client.tour-booking.vat-invoice', compact('tourBooking'));
    }

    /**
     * Xử lý yêu cầu xuất hóa đơn VAT
     */
    public function requestVatInvoice(Request $request, $id)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'company_tax_code' => 'required|string|max:50',
            'company_address' => 'required|string|max:500',
            'company_email' => 'required|email|max:255',
            'company_phone' => 'required|string|max:20',
        ]);

        $tourBooking = TourBooking::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        // Cập nhật thông tin công ty
        $tourBooking->update([
            'need_vat_invoice' => true,
            'company_name' => $request->company_name,
            'company_tax_code' => $request->company_tax_code,
            'company_address' => $request->company_address,
            'company_email' => $request->company_email,
            'company_phone' => $request->company_phone,
        ]);

        // Gửi email thông báo cho admin
        try {
            Mail::to(config('mail.admin_email', 'admin@hotel.com'))
                ->send(new TourVatInvoiceMail($tourBooking));
            
            Log::info('Tour VAT invoice request sent', [
                'tour_booking_id' => $tourBooking->id,
                'company_name' => $request->company_name
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send tour VAT invoice email', [
                'tour_booking_id' => $tourBooking->id,
                'error' => $e->getMessage()
            ]);
        }

        return redirect()->route('tour-booking.show', $tourBooking->id)
            ->with('success', 'Yêu cầu xuất hóa đơn VAT đã được gửi. Chúng tôi sẽ xử lý trong thời gian sớm nhất.');
    }

    /**
     * Tải xuống hóa đơn VAT (nếu đã có)
     */
    public function downloadVatInvoice($id)
    {
        $tourBooking = TourBooking::where('id', $id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (!$tourBooking->vat_invoice_number) {
            return back()->with('error', 'Hóa đơn VAT chưa được xuất.');
        }

        // Kiểm tra file tồn tại
        $filePath = storage_path("app/vat-invoices/tour-{$tourBooking->vat_invoice_number}.pdf");
        
        if (!file_exists($filePath)) {
            // Tạo file mẫu nếu không tồn tại
            $this->createSampleVatInvoice($tourBooking);
            
            if (!file_exists($filePath)) {
                return back()->with('error', 'Không thể tạo file hóa đơn VAT. Vui lòng liên hệ quản trị viên.');
            }
        }

        return response()->download(
            $filePath,
            "VAT_Invoice_{$tourBooking->vat_invoice_number}.pdf"
        );
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
            
            Log::info('VAT invoice PDF created successfully for client', [
                'tour_booking_id' => $tourBooking->id,
                'file_path' => $filePath,
                'file_size' => filesize($filePath)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to create VAT invoice PDF for client', [
                'tour_booking_id' => $tourBooking->id,
                'error' => $e->getMessage(),
                'directory' => storage_path('app/vat-invoices'),
                'directory_exists' => is_dir(storage_path('app/vat-invoices')),
                'directory_writable' => is_writable(storage_path('app/vat-invoices'))
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
        
        return $content;
    }
}
