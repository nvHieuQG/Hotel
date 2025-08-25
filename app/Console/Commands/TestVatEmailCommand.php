<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Interfaces\Services\VatInvoiceServiceInterface;
use Illuminate\Support\Facades\Log;

class TestVatEmailCommand extends Command
{
    protected $signature = 'test:vat-email {booking_id?}';
    protected $description = 'Test chức năng gửi email hóa đơn VAT';

    public function handle(VatInvoiceServiceInterface $vatService)
    {
        $this->info('🧪 Bắt đầu test chức năng gửi email hóa đơn VAT...');
        
        try {
            // Tìm booking để test
            $bookingId = $this->argument('booking_id');
            
            if ($bookingId) {
                $booking = Booking::find($bookingId);
                if (!$booking) {
                    $this->error("❌ Không tìm thấy booking với ID: {$bookingId}");
                    return 1;
                }
            } else {
                // Tìm booking có thông tin VAT
                $booking = Booking::whereNotNull('vat_invoice_info')
                    ->where('vat_invoice_status', '!=', 'sent')
                    ->first();
                
                if (!$booking) {
                    $this->warn("⚠️ Không tìm thấy booking nào có thông tin VAT để test");
                    $this->info("Tạo thông tin VAT mẫu cho booking đầu tiên...");
                    
                    $booking = Booking::first();
                    if (!$booking) {
                        $this->error("❌ Không có booking nào trong hệ thống");
                        return 1;
                    }
                }
            }
            
            $this->info("📋 Sử dụng booking: {$booking->booking_id} (ID: {$booking->id})");
            $this->info("👤 Khách hàng: {$booking->user->name} ({$booking->user->email})");
            
            // Kiểm tra thông tin VAT
            if (empty($booking->vat_invoice_info)) {
                $this->warn("⚠️ Booking này chưa có thông tin VAT invoice. Tạo thông tin mẫu...");
                
                // Tạo thông tin VAT mẫu để test
                $booking->update([
                    'vat_invoice_info' => [
                        'companyName' => 'Công ty Test VAT',
                        'taxCode' => '0123456789',
                        'companyAddress' => '123 Đường Test, Quận Test, TP.HCM',
                        'receiverEmail' => $booking->user->email,
                        'receiverName' => $booking->user->name,
                        'receiverPhone' => '0123456789',
                        'note' => 'Test email VAT invoice với logic mới'
                    ],
                    'vat_invoice_status' => 'pending'
                ]);
                
                $this->info("✅ Đã tạo thông tin VAT mẫu");
            } else {
                $this->info("✅ Đã có thông tin VAT: " . ($booking->vat_invoice_info['companyName'] ?? 'N/A'));
            }

            // Kiểm tra trạng thái hiện tại
            $this->info("📊 Trạng thái hiện tại: {$booking->vat_invoice_status}");
            if ($booking->vat_invoice_file_path) {
                $this->info("📄 File hiện tại: {$booking->vat_invoice_file_path}");
            }

            // Tạo hóa đơn VAT
            $this->info("🔄 Đang tạo hóa đơn VAT...");
            $filePath = $vatService->generateVatInvoice($booking);
            
            if (!$filePath) {
                $this->error("❌ Không thể tạo hóa đơn VAT");
                return 1;
            }
            
            $this->info("✅ Đã tạo hóa đơn VAT: {$filePath}");
            $this->info("📊 Trạng thái sau khi tạo: " . $booking->fresh()->vat_invoice_status);

            // Gửi email
            $this->info("📧 Đang gửi email...");
            $success = $vatService->sendVatInvoiceEmail($booking);
            
            if ($success) {
                $this->info("✅ Đã gửi email VAT thành công!");
                $this->info("📊 Trạng thái: " . $booking->fresh()->vat_invoice_status);
                $this->info("⏰ Thời gian gửi: " . $booking->fresh()->vat_invoice_sent_at);
                
                // Hiển thị thông tin email
                $info = $booking->vat_invoice_info;
                $this->info("📮 Thông tin email:");
                $this->info("   - Người nhận: " . ($info['receiverName'] ?? 'N/A'));
                $this->info("   - Email: " . ($info['receiverEmail'] ?? 'N/A'));
                $this->info("   - Công ty: " . ($info['companyName'] ?? 'N/A'));
                
            } else {
                $this->error("❌ Không thể gửi email VAT");
                
                // Kiểm tra log để tìm lỗi
                $this->warn("🔍 Kiểm tra log để tìm lỗi...");
                $this->info("📁 Log file: storage/logs/laravel.log");
            }

            // Kiểm tra file đã được tạo
            $fullPath = storage_path('app/' . $booking->vat_invoice_file_path);
            if (file_exists($fullPath)) {
                $fileSize = filesize($fullPath);
                $this->info("📄 File PDF đã được tạo:");
                $this->info("   - Đường dẫn: {$fullPath}");
                $this->info("   - Kích thước: " . number_format($fileSize) . " bytes");
                $this->info("   - Có thể đọc: " . (is_readable($fullPath) ? '✅' : '❌'));
            } else {
                $this->warn("⚠️ File PDF không tồn tại tại: {$fullPath}");
            }

            $this->info("🎉 Test hoàn thành!");
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Có lỗi xảy ra: " . $e->getMessage());
            Log::error('Test VAT email command failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
}
