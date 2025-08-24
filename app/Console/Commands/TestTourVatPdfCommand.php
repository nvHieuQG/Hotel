<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TourBooking;
use App\Services\VatInvoiceService;
use Illuminate\Support\Facades\Log;

class TestTourVatPdfCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:tour-vat-pdf {tour_booking_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test tạo PDF VAT cho tour booking';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tourBookingId = $this->argument('tour_booking_id');
        
        try {
            $this->info("Đang tìm tour booking ID: {$tourBookingId}");
            
            $tourBooking = TourBooking::with(['user', 'tourBookingRooms.roomType', 'tourBookingServices'])
                ->find($tourBookingId);
            
            if (!$tourBooking) {
                $this->error("Không tìm thấy tour booking với ID: {$tourBookingId}");
                return 1;
            }
            
            $this->info("Tìm thấy tour booking: {$tourBooking->tour_name}");
            $this->info("Trạng thái: {$tourBooking->status}");
            $this->info("Cần VAT: " . ($tourBooking->need_vat_invoice ? 'Có' : 'Không'));
            
            if (!$tourBooking->need_vat_invoice) {
                $this->error("Tour booking này không yêu cầu VAT invoice");
                return 1;
            }
            
            // Kiểm tra thông tin công ty
            $this->info("Thông tin công ty:");
            $this->info("- Tên: " . ($tourBooking->company_name ?? 'N/A'));
            $this->info("- MST: " . ($tourBooking->company_tax_code ?? 'N/A'));
            $this->info("- Email: " . ($tourBooking->company_email ?? 'N/A'));
            $this->info("- Địa chỉ: " . ($tourBooking->company_address ?? 'N/A'));
            
            // Kiểm tra thanh toán
            $vatInvoiceService = app(VatInvoiceService::class);
            $paymentInfo = $vatInvoiceService->getTourPaymentStatusInfo($tourBooking);
            
            $this->info("Thông tin thanh toán:");
            $this->info("- Tổng tiền: " . number_format($paymentInfo['totalDue']) . " VNĐ");
            $this->info("- Đã thanh toán: " . number_format($paymentInfo['totalPaid']) . " VNĐ");
            $this->info("- Còn lại: " . number_format($paymentInfo['remainingAmount']) . " VNĐ");
            $this->info("- Đã thanh toán đủ: " . ($paymentInfo['isFullyPaid'] ? 'Có' : 'Không'));
            
            if (!$paymentInfo['isFullyPaid']) {
                $this->warn("Khách chưa thanh toán đủ tiền. Không thể tạo VAT invoice.");
                return 1;
            }
            
            $this->info("Đang tạo PDF VAT invoice...");
            
            // Tạo PDF
            $filePath = $vatInvoiceService->generateTourVatInvoice($tourBooking);
            
            if ($filePath) {
                $this->info("✅ Tạo PDF thành công!");
                $this->info("File path: {$filePath}");
                
                // Kiểm tra file
                $fullPath = storage_path('app/public/' . str_replace('public/', '', $filePath));
                if (file_exists($fullPath)) {
                    $fileSize = filesize($fullPath);
                    $this->info("File size: " . number_format($fileSize) . " bytes");
                    $this->info("File exists: Có");
                    $this->info("File readable: " . (is_readable($fullPath) ? 'Có' : 'Không'));
                } else {
                    $this->error("❌ File không tồn tại tại: {$fullPath}");
                }
                
                // Refresh tour booking
                $tourBooking->refresh();
                $this->info("Database updated:");
                $this->info("- VAT file path: " . ($tourBooking->vat_invoice_file_path ?? 'N/A'));
                $this->info("- VAT generated at: " . ($tourBooking->vat_invoice_generated_at ?? 'N/A'));
                
            } else {
                $this->error("❌ Không thể tạo PDF VAT invoice");
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Lỗi: " . $e->getMessage());
            Log::error('TestTourVatPdfCommand error: ' . $e->getMessage(), [
                'tour_booking_id' => $tourBookingId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
}
