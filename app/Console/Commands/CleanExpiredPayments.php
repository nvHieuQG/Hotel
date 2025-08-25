<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\TourBooking;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanExpiredPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payments:clean-expired {--minutes=30 : Số phút để xác định giao dịch hết hạn}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Xóa các giao dịch thanh toán pending quá thời gian quy định cho cả regular booking và tour booking';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutes = $this->option('minutes');
        $cutoffTime = Carbon::now()->subMinutes($minutes);
        
        $this->info("Đang xóa các giao dịch pending quá {$minutes} phút...");
        
        // Xóa giao dịch regular booking quá hạn
        $expiredRegularPayments = Payment::where('booking_id', '!=', null)
            ->where('tour_booking_id', null)
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoffTime)
            ->get();
            
        $regularCount = 0;
        foreach ($expiredRegularPayments as $payment) {
            $payment->delete();
            $regularCount++;
        }
        
        // Xóa giao dịch tour booking quá hạn
        $expiredTourPayments = Payment::where('tour_booking_id', '!=', null)
            ->where('booking_id', null)
            ->where('status', 'pending')
            ->where('created_at', '<', $cutoffTime)
            ->get();
            
        $tourCount = 0;
        foreach ($expiredTourPayments as $payment) {
            $payment->delete();
            $tourCount++;
        }
        
        $totalCount = $regularCount + $tourCount;
        
        if ($totalCount > 0) {
            $this->info("Đã xóa {$totalCount} giao dịch quá hạn:");
            $this->info("- Regular booking: {$regularCount} giao dịch");
            $this->info("- Tour booking: {$tourCount} giao dịch");
            
            Log::info('CleanExpiredPayments command executed', [
                'regular_count' => $regularCount,
                'tour_count' => $tourCount,
                'total_count' => $totalCount,
                'cutoff_time' => $cutoffTime->toDateTimeString()
            ]);
        } else {
            $this->info("Không có giao dịch nào quá hạn để xóa.");
        }
        
        return 0;
    }
}
