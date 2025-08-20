<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Interfaces\Services\VatInvoiceServiceInterface;
use Illuminate\Support\Facades\Log;

class TestVatEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vat:test-email {booking_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test gửi email VAT invoice cho một booking cụ thể';

    /**
     * Execute the console command.
     */
    public function handle(VatInvoiceServiceInterface $vatService)
    {
        $bookingId = $this->argument('booking_id');
        
        try {
            $booking = Booking::with(['user', 'room.roomType'])->find($bookingId);
            
            if (!$booking) {
                $this->error("Không tìm thấy booking với ID: {$bookingId}");
                return 1;
            }

            $this->info("Đang test gửi email VAT cho booking: {$booking->booking_id}");
            $this->info("Khách hàng: {$booking->user->name} ({$booking->user->email})");
            
            if (empty($booking->vat_invoice_info)) {
                $this->warn("Booking này chưa có thông tin VAT invoice. Tạo thông tin mẫu...");
                
                // Tạo thông tin VAT mẫu để test
                $booking->update([
                    'vat_invoice_info' => [
                        'companyName' => 'Công ty Test',
                        'taxCode' => '0123456789',
                        'companyAddress' => '123 Đường Test, Quận Test, TP.HCM',
                        'receiverEmail' => $booking->user->email,
                        'receiverName' => $booking->user->name,
                        'receiverPhone' => '0123456789',
                        'note' => 'Test email VAT invoice'
                    ],
                    'vat_invoice_status' => 'pending'
                ]);
                
                $this->info("Đã tạo thông tin VAT mẫu");
            }

            // Tạo hóa đơn VAT
            $this->info("Đang tạo hóa đơn VAT...");
            $filePath = $vatService->generateVatInvoice($booking);
            
            if (!$filePath) {
                $this->error("Không thể tạo hóa đơn VAT");
                return 1;
            }
            
            $this->info("Đã tạo hóa đơn VAT: {$filePath}");

            // Gửi email
            $this->info("Đang gửi email...");
            $success = $vatService->sendVatInvoiceEmail($booking);
            
            if ($success) {
                $this->info("✅ Đã gửi email VAT thành công!");
                $this->info("Trạng thái: {$booking->fresh()->vat_invoice_status}");
                $this->info("Thời gian gửi: " . $booking->fresh()->vat_invoice_sent_at);
            } else {
                $this->error("❌ Không thể gửi email VAT");
            }

            return 0;
            
        } catch (\Exception $e) {
            $this->error("Có lỗi xảy ra: " . $e->getMessage());
            Log::error("Test VAT email failed: " . $e->getMessage());
            return 1;
        }
    }
}
