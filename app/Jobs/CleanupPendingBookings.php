<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupPendingBookings implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // ===== CLEANUP PENDING BOOKINGS =====
            // Tìm các booking có trạng thái pending_payment và tạo hơn 30 phút trước
            $pendingBookings = Booking::where('status', 'pending_payment')
                ->where('created_at', '<', Carbon::now()->subMinutes(30))
                ->get();

            $cancelledCount = 0;
            foreach ($pendingBookings as $booking) {
                try {
                    // Cập nhật trạng thái thành cancelled
                    $booking->update([
                        'status' => 'cancelled'
                    ]);

                    // Tạo ghi chú hệ thống
                    $booking->notes()->create([
                        'user_id' => $booking->user_id,
                        'content' => 'Đặt phòng đã bị hủy tự động do không thanh toán trong thời gian quy định (30 phút)',
                        'type' => 'system',
                        'visibility' => 'internal',
                        'is_internal' => true
                    ]);

                    $cancelledCount++;

                    Log::info('Auto-cancelled pending booking', [
                        'booking_id' => $booking->id,
                        'user_id' => $booking->user_id,
                        'created_at' => $booking->created_at
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error cancelling pending booking', [
                        'booking_id' => $booking->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // ===== CLEANUP PENDING PAYMENTS =====
            // Tìm các payment có trạng thái pending và tạo hơn 30 phút trước
            $pendingPayments = Payment::where('status', 'pending')
                ->where('created_at', '<', Carbon::now()->subMinutes(30))
                ->get();

            $deletedPendingCount = 0;
            foreach ($pendingPayments as $payment) {
                try {
                    // Xóa payment pending cũ
                    $payment->delete();

                    $deletedPendingCount++;

                    Log::info('Auto-deleted pending payment', [
                        'payment_id' => $payment->id,
                        'booking_id' => $payment->booking_id,
                        'amount' => $payment->amount,
                        'created_at' => $payment->created_at
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error deleting pending payment', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Tìm các payment có trạng thái failed và tạo hơn 30 phút trước
            $failedPayments = Payment::where('status', 'failed')
                ->where('created_at', '<', Carbon::now()->subMinutes(30))
                ->get();

            $deletedFailedCount = 0;
            foreach ($failedPayments as $payment) {
                try {
                    // Xóa payment failed cũ
                    $payment->delete();

                    $deletedFailedCount++;

                    Log::info('Auto-deleted failed payment', [
                        'payment_id' => $payment->id,
                        'booking_id' => $payment->booking_id,
                        'amount' => $payment->amount,
                        'created_at' => $payment->created_at
                    ]);
                } catch (\Exception $e) {
                    Log::error('Error deleting failed payment', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Log tổng kết
            Log::info('Cleanup completed', [
                'cancelled_bookings_count' => $cancelledCount,
                'deleted_pending_payments_count' => $deletedPendingCount,
                'deleted_failed_payments_count' => $deletedFailedCount,
                'total_processed' => $pendingBookings->count() + $pendingPayments->count() + $failedPayments->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CleanupPendingBookings job', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
