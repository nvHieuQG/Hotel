<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;

class FixPaymentMethodCommand extends Command
{
    protected $signature = 'payment:fix-method {booking_id}';
    protected $description = 'Sửa payment method để hợp lệ với quy định VAT';

    public function handle()
    {
        $bookingId = $this->argument('booking_id');
        
        $this->info("Đang sửa payment method cho booking ID: {$bookingId}");
        
        // Sửa payment method thành company_card
        $updated = Payment::where('booking_id', $bookingId)
            ->where('status', 'completed')
            ->update(['payment_method' => 'company_card']);
            
        if ($updated > 0) {
            $this->info("✅ Đã sửa {$updated} payment thành 'company_card'");
        } else {
            $this->warn("Không có payment nào được cập nhật");
        }
        
        // Kiểm tra lại
        $payments = Payment::where('booking_id', $bookingId)->get();
        $this->info("\n=== KIỂM TRA LẠI PAYMENTS ===");
        foreach ($payments as $payment) {
            $this->line("Payment ID: {$payment->id}");
            $this->line("  - Method: {$payment->payment_method}");
            $this->line("  - Amount: " . number_format($payment->amount) . " VNĐ");
            $this->line("  - Status: {$payment->status}");
        }
        
        $this->info("\nBây giờ hãy thử tạo hóa đơn VAT lại:");
        $this->line("php artisan vat:test-email {$bookingId}");
        
        return 0;
    }
}
