<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;

class CheckBookingDetailsCommand extends Command
{
    protected $signature = 'booking:check {id}';
    protected $description = 'Kiểm tra chi tiết booking và phương thức thanh toán';

    public function handle()
    {
        $id = $this->argument('id');
        $booking = Booking::with(['user', 'room.roomType', 'payments'])->find($id);
        
        if (!$booking) {
            $this->error("Không tìm thấy booking với ID: {$id}");
            return 1;
        }

        $this->info("=== CHI TIẾT BOOKING ===");
        $this->line("ID: {$booking->id}");
        $this->line("Booking ID: {$booking->booking_id}");
        $this->line("User: {$booking->user->name} ({$booking->user->email})");
        $this->line("Room: " . ($booking->room->roomType->name ?? 'N/A'));
        $this->line("Check-in: {$booking->check_in_date}");
        $this->line("Check-out: {$booking->check_out_date}");
        
        // Tính toán chi phí
        $ci = $booking->check_in_date;
        $co = $booking->check_out_date;
        $nights = $ci && $co ? $ci->copy()->startOfDay()->diffInDays($co->copy()->startOfDay()) : 0;
        $nightly = (int)($booking->room->roomType->price ?? $booking->room->price ?? 0);
        $roomCost = max(0, $nights) * $nightly;
        $services = (float)($booking->extra_services_total ?? 0) + (float)($booking->total_services_price ?? 0);
        $surcharge = (float)($booking->surcharge ?? 0);
        $discount = (float) $booking->payments()->where('status','!=','failed')->sum('discount_amount');
        $subtotal = $roomCost + $services + $surcharge - $discount;
        $vatRate = 0.1;
        $vatAmount = max(0, round($subtotal * $vatRate));
        $grandTotal = $subtotal + $vatAmount;

        $this->info("\n=== TÍNH TOÁN CHI PHÍ ===");
        $this->line("Số đêm: {$nights}");
        $this->line("Giá phòng/đêm: " . number_format($nightly) . " VNĐ");
        $this->line("Tiền phòng: " . number_format($roomCost) . " VNĐ");
        $this->line("Dịch vụ: " . number_format($services) . " VNĐ");
        $this->line("Phụ phí: " . number_format($surcharge) . " VNĐ");
        $this->line("Giảm giá: " . number_format($discount) . " VNĐ");
        $this->line("Tổng trước VAT: " . number_format($subtotal) . " VNĐ");
        $this->line("VAT (10%): " . number_format($vatAmount) . " VNĐ");
        $this->line("TỔNG CỘNG: " . number_format($grandTotal) . " VNĐ");

        // Kiểm tra quy định 5 triệu
        if ($grandTotal >= 5000000) {
            $this->warn("\n⚠️  HÓA ĐƠN TỪ 5.000.000đ - KIỂM TRA PHƯƠNG THỨC THANH TOÁN");
            
            $validPayment = $booking->payments()
                ->where('status','completed')
                ->where(function($q){
                    $q->where('payment_method','bank_transfer')
                      ->orWhere('payment_method','company_card');
                })->exists();
            
            if ($validPayment) {
                $this->info("✅ Phương thức thanh toán hợp lệ");
            } else {
                $this->error("❌ Phương thức thanh toán KHÔNG hợp lệ");
                $this->line("Yêu cầu: Thẻ/tài khoản công ty hoặc chuyển khoản công ty");
            }
        }

        // Thông tin thanh toán
        $this->info("\n=== THÔNG TIN THANH TOÁN ===");
        $payments = $booking->payments;
        if ($payments->isEmpty()) {
            $this->warn("Chưa có thanh toán nào");
        } else {
            foreach ($payments as $payment) {
                $this->line("Payment ID: {$payment->id}");
                $this->line("  - Method: {$payment->payment_method}");
                $this->line("  - Amount: " . number_format($payment->amount) . " VNĐ");
                $this->line("  - Status: {$payment->status}");
                $this->line("  - Discount: " . number_format($payment->discount_amount) . " VNĐ");
                $this->line("---");
            }
        }

        // Thông tin VAT
        $this->info("\n=== THÔNG TIN VAT ===");
        $this->line("Status: {$booking->vat_invoice_status}");
        $this->line("File path: " . ($booking->vat_invoice_file_path ?? 'null'));
        if ($booking->vat_invoice_info) {
            $this->line("Company: " . ($booking->vat_invoice_info['companyName'] ?? 'N/A'));
            $this->line("Tax Code: " . ($booking->vat_invoice_info['taxCode'] ?? 'N/A'));
            $this->line("Email: " . ($booking->vat_invoice_info['receiverEmail'] ?? 'N/A'));
        }

        return 0;
    }
}
