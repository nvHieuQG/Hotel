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
    protected $signature = 'cleanup:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cleanup pending bookings and payments that have not been completed within 30 minutes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of pending bookings and payments...');

        // Dispatch the cleanup job (bao gồm cả bookings và payments)
        CleanupPendingBookings::dispatch();

        $this->info('Cleanup job dispatched successfully.');

        return Command::SUCCESS;
    }
}
