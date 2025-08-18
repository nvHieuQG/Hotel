<?php

namespace App\Console\Commands;

use App\Models\Promotion;
use App\Events\PromotionExpired;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckExpiredPromotions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'promotions:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired promotions and broadcast events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expired promotions...');
        
        // Lấy các khuyến mại vừa hết hạn (trong vòng 1 phút)
        $expiredPromotions = Promotion::where('is_active', true)
            ->where('expired_at', '<=', Carbon::now())
            ->where('expired_at', '>', Carbon::now()->subMinute())
            ->get();
            
        if ($expiredPromotions->count() > 0) {
            foreach ($expiredPromotions as $promotion) {
                // Deactivate promotion
                $promotion->update(['is_active' => false]);
                
                // Broadcast expired event
                event(new PromotionExpired($promotion));
                
                $this->info("Promotion '{$promotion->title}' (ID: {$promotion->id}) has expired and been deactivated.");
            }
            
            $this->info("Total expired promotions processed: {$expiredPromotions->count()}");
        } else {
            $this->info('No expired promotions found.');
        }
        
        return 0;
    }
}
