<?php

namespace App\Jobs;

use App\Models\Booking;
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

            Log::info('Cleanup pending bookings completed', [
                'cancelled_count' => $cancelledCount,
                'total_processed' => $pendingBookings->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error in CleanupPendingBookings job', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
