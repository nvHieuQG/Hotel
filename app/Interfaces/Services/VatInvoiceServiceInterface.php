<?php

namespace App\Interfaces\Services;

use App\Models\Booking;

interface VatInvoiceServiceInterface
{
    /**
     * Lưu thông tin yêu cầu xuất hóa đơn VAT từ khách hàng
     * $info gồm: companyName, taxCode, companyAddress, receiverEmail, receiverName, receiverPhone, note
     */
    public function saveClientVatRequest(Booking $booking, array $info): void;

    /** Tạo file PDF hóa đơn VAT (mẫu hợp pháp) và lưu đường dẫn vào booking */
    public function generateVatInvoice(Booking $booking): ?string;

    /** Gửi email hóa đơn VAT đến khách hàng và/hoặc kế toán */
    public function sendVatInvoiceEmail(Booking $booking): bool;

    /** Kiểm tra ràng buộc phương thức thanh toán theo quy định (>= 5 triệu sau khuyến mãi) */
    public function ensureLegalPaymentCompliance(Booking $booking): void;
}


