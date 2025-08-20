<?php

namespace App\Services;

use App\Interfaces\Services\VatInvoiceServiceInterface;
use App\Models\Booking;
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

    public function ensureLegalPaymentCompliance(Booking $booking): void
    {
        // Tính tổng phải thu theo cách admin tính
        $ci = $booking->check_in_date; $co = $booking->check_out_date;
        $nights = $ci && $co ? $ci->copy()->startOfDay()->diffInDays($co->copy()->startOfDay()) : 0;
        $nightly = (int)($booking->room->roomType->price ?? $booking->room->price ?? 0);
        $roomCost = max(0, $nights) * $nightly;
        $roomChange = (float) $booking->roomChanges()->whereIn('status', ['approved','completed'])->sum('price_difference');
        $guestSurcharge = max(0, (float)($booking->surcharge ?? 0) - $roomChange);
        $services = (float)($booking->extra_services_total ?? 0) + (float)($booking->total_services_price ?? 0);
        $totalBeforeDiscount = $roomCost + $guestSurcharge + $roomChange + $services;
        $totalDiscount = (float) $booking->payments()->where('status', '!=', 'failed')->sum('discount_amount');
        $totalDue = $totalBeforeDiscount - $totalDiscount;

        // Chỉ kiểm tra nghiêm ngặt nếu từ 5 triệu trở lên
        if ($totalDue >= 5000000) {
            $valid = $booking->payments()
                ->where('status','completed')
                ->where(function($q){
                    $q->where('payment_method','bank_transfer')->orWhere('payment_method','company_card');
                })->exists();
            if (!$valid) {
                throw new \RuntimeException('Theo quy định hiện hành, hóa đơn từ 5.000.000đ phải thanh toán bằng thẻ/tài khoản công ty hoặc chuyển khoản công ty. Vui lòng liên hệ để được hỗ trợ.');
            }
        }
        // Dưới 5 triệu: cho phép thanh toán cá nhân, nhưng sẽ báo khách chuyển khoản công ty sau
    }

    public function generateVatInvoice(Booking $booking): ?string
    {
        try {
            $this->ensureLegalPaymentCompliance($booking);

            // Xóa file VAT cũ của booking
            $this->deleteOldPdfFiles($booking->booking_id);

            $info = (array) ($booking->vat_invoice_info ?? []);
            $ci = $booking->check_in_date; $co = $booking->check_out_date;
            $nights = $ci && $co ? $ci->copy()->startOfDay()->diffInDays($co->copy()->startOfDay()) : 0;
            $nightly = (int)($booking->room->roomType->price ?? $booking->room->price ?? 0);
            $roomCost = max(0, $nights) * $nightly;
            $discount = (float) $booking->payments()->where('status','!=','failed')->sum('discount_amount');
            if ($discount <= 0 && (float)($booking->promotion_discount ?? 0) > 0) { $discount = (float)$booking->promotion_discount; }
            $services = (float)($booking->extra_services_total ?? 0) + (float)($booking->total_services_price ?? 0);
            $surcharge = (float)($booking->surcharge ?? 0);
            
            // Tổng cộng đã bao gồm VAT 10%
            $grandTotal = $roomCost + $services + $surcharge - $discount;
            
            // Tính ngược lại: giá trước VAT = tổng cộng / (1 + VAT rate)
            $vatRate = 0.1;
            $subtotal = round($grandTotal / (1 + $vatRate));
            $vatAmount = $grandTotal - $subtotal;

            $html = view('emails.vat-invoice-template', compact('booking','info','nightly','nights','roomCost','discount','services','surcharge','subtotal','vatRate','vatAmount','grandTotal'))->render();

            // Lưu PDF với cấu hình giống luồng tạm trú
            $filename = 'vat_invoice_' . $booking->booking_id . '_' . date('Y-m-d_H-i-s') . '.pdf';
            $pathRel = 'vat_invoices/' . $filename;
            $pathAbs = storage_path('app/public/' . $pathRel);
            Storage::disk('public')->makeDirectory('vat_invoices');
            $this->createPdfFromHtml($html, $pathAbs);

            $booking->update([
                'vat_invoice_status' => 'generated',
                'vat_invoice_generated_at' => now(),
                'vat_invoice_file_path' => 'public/' . $pathRel,
            ]);

            return 'public/' . $pathRel;
        } catch (\Throwable $e) {
            Log::error('VAT invoice generation failed: '.$e->getMessage());
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

            Mail::send('emails.vat-invoice', [
                'booking' => $booking,
                'info' => $info,
            ], function ($message) use ($to, $name, $booking) {
                $message->to($to, $name)
                    ->subject('Hóa đơn VAT cho đặt phòng ' . $booking->booking_id);
                $attachment = storage_path('app/' . $booking->vat_invoice_file_path);
                if (is_file($attachment)) {
                    $message->attach($attachment);
                }
            });

            $booking->update([
                'vat_invoice_status' => 'sent',
                'vat_invoice_sent_at' => now(),
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Send VAT invoice failed: '.$e->getMessage());
            return false;
        }
    }

    private function deleteOldPdfFiles(string $bookingId): void
    {
        try {
            $files = Storage::disk('public')->files('vat_invoices');
            foreach ($files as $file) {
                if (str_contains($file, 'vat_invoice_' . $bookingId . '_') && str_ends_with($file, '.pdf')) {
                    Storage::disk('public')->delete($file);
                    Log::info('Deleted old VAT invoice file: ' . $file);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Cannot delete old VAT invoice files: '.$e->getMessage());
        }
    }

    private function createPdfFromHtml(string $html, string $outputPath): void
    {
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => false,
            'defaultFont' => 'DejaVu Sans',
            'dpi' => 96,
        ]);
        $pdf->save($outputPath);
        if (!file_exists($outputPath)) {
            throw new \RuntimeException('VAT PDF was not created');
        }
    }
}


