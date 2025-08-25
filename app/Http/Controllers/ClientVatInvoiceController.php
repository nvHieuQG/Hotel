<?php

namespace App\Http\Controllers;

use App\Interfaces\Services\VatInvoiceServiceInterface;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientVatInvoiceController extends Controller
{
    public function __construct(private VatInvoiceServiceInterface $vatService) {}

    private function ensureOwnership(Booking $booking): void
    {
        abort_unless(Auth::check() && $booking->user_id === Auth::id(), 403);
    }

    public function request(Request $request, Booking $booking)
    {
        $this->ensureOwnership($booking);
        $data = $request->validate([
            'companyName' => 'required|string|max:255',
            'taxCode' => 'required|string|max:50',
            'companyAddress' => 'required|string|max:500',
            'receiverEmail' => 'required|email',
            'receiverName' => 'nullable|string|max:255',
            'receiverPhone' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:1000',
        ]);

        $this->vatService->saveClientVatRequest($booking, $data);

        return back()->with('success', 'Đã lưu yêu cầu xuất hóa đơn VAT.');
    }

    public function generate(Booking $booking)
    {
        $this->ensureOwnership($booking);
        $file = $this->vatService->generateVatInvoice($booking);
        return back()->with($file ? 'success' : 'error', $file ? 'Đã tạo hóa đơn VAT.' : 'Không thể tạo hóa đơn VAT.');
    }

    public function send(Booking $booking)
    {
        $this->ensureOwnership($booking);
        $ok = $this->vatService->sendVatInvoiceEmail($booking);
        return back()->with($ok ? 'success' : 'error', $ok ? 'Đã gửi email hóa đơn VAT.' : 'Không thể gửi email hóa đơn VAT.');
    }

    /**
     * Đề nghị tạo lại hóa đơn VAT
     */
    public function regenerate(Booking $booking)
    {
        $this->ensureOwnership($booking);
        
        // Cập nhật trạng thái để admin biết cần tạo lại
        $booking->update([
            'vat_invoice_status' => 'pending',
            'vat_invoice_file_path' => null,
            'vat_invoice_generated_at' => null,
            'vat_invoice_sent_at' => null,
        ]);

        return back()->with('success', 'Đã gửi yêu cầu tạo lại hóa đơn VAT. Nhân viên khách sạn sẽ xử lý trong thời gian sớm nhất.');
    }
}


