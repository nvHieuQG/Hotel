<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;

class CheckVatBookingsCommand extends Command
{
    protected $signature = 'vat:check-bookings';
    protected $description = 'Kiểm tra bookings có thông tin VAT';

    public function handle()
    {
        $this->info('Kiểm tra bookings có VAT info...');
        
        $bookings = Booking::whereNotNull('vat_invoice_info')->get();
        
        if ($bookings->isEmpty()) {
            $this->warn('Không có booking nào có thông tin VAT');
            return 0;
        }
        
        $this->info("Tìm thấy {$bookings->count()} booking có VAT info:");
        
        foreach ($bookings as $booking) {
            $this->line("ID: {$booking->id}, Booking ID: {$booking->booking_id}");
            $this->line("  - Status: {$booking->vat_invoice_status}");
            $this->line("  - File path: " . ($booking->vat_invoice_file_path ?? 'null'));
            $this->line("  - User: {$booking->user->name} ({$booking->user->email})");
            $this->line("---");
        }
        
        return 0;
    }
}
