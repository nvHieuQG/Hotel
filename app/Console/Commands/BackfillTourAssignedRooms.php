<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TourBookingRoom;
use App\Models\RoomType;

class BackfillTourAssignedRooms extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tour:backfill-assigned-rooms {--dry-run : Chạy thử, không ghi DB}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gán cố định assigned_room_ids cho các TourBookingRoom còn thiếu, dựa vào phòng trống theo khoảng ngày';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = (bool)$this->option('dry-run');

        $q = TourBookingRoom::with(['tourBooking', 'roomType.rooms'])
            ->whereNull('assigned_room_ids');

        $count = $q->count();
        $this->info("Found {$count} TourBookingRoom to backfill.");

        $updated = 0;
        $skipped = 0;

        $q->chunkById(100, function ($rows) use (&$updated, &$skipped, $dryRun) {
            foreach ($rows as $tbr) {
                $tb = $tbr->tourBooking;
                $rt = $tbr->roomType;
                if (!$tb || !$rt) { $skipped++; continue; }

                $assigned = [];
                $rooms = ($rt->rooms ?? collect())->sortBy('id')->values();
                foreach ($rooms as $room) {
                    if (count($assigned) >= (int)$tbr->quantity) break;
                    if ($room->isStrictlyAvailableForRange($tb->check_in_date, $tb->check_out_date)) {
                        $assigned[] = $room->id;
                    }
                }

                if (count($assigned) === 0) { $skipped++; continue; }

                if ($dryRun) {
                    $this->line("[DRY] TBR #{$tbr->id} => rooms " . implode(',', $assigned));
                    continue;
                }

                $tbr->update(['assigned_room_ids' => $assigned]);
                $updated++;
                $this->line("Updated TBR #{$tbr->id} => rooms " . implode(',', $assigned));
            }
        });

        $this->info("Done. Updated: {$updated}, Skipped: {$skipped}.");
        return Command::SUCCESS;
    }
}


