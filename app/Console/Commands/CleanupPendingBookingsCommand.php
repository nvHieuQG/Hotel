<?php

namespace App\Console\Commands;

use App\Jobs\CleanupPendingBookings;
use Illuminate\Console\Command;

class CleanupPendingBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:cleanup-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup pending bookings that have not been paid within 30 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of pending bookings...');

        // Dispatch the cleanup job
        CleanupPendingBookings::dispatch();

        $this->info('Cleanup job dispatched successfully.');

        return Command::SUCCESS;
    }
}
