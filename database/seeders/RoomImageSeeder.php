<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\RoomImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class RoomImageSeeder extends Seeder
{
    public function run(): void
    {
        $relativeDir = 'client/images'; // scan existing folder
        $dir = public_path($relativeDir);

        if (!is_dir($dir)) {
            $this->command?->warn("[RoomImageSeeder] Directory not found: {$dir}. Skipped.");
            return;
        }

        // collect images
        $extensions = ['jpg', 'jpeg', 'png', 'webp'];
        $files = collect(File::files($dir))
            ->filter(function ($file) use ($extensions) {
                $extOk = in_array(strtolower($file->getExtension()), $extensions, true);
                // only files named like room-*.ext
                $name = strtolower($file->getFilename());
                $patternOk = str_starts_with($name, 'room-');
                return $extOk && $patternOk;
            })
            ->values();

        if ($files->isEmpty()) {
            $this->command?->warn('[RoomImageSeeder] No images found. Skipped.');
            return;
        }

        $rooms = Room::query()->orderBy('id')->get();
        if ($rooms->isEmpty()) {
            $this->command?->warn('[RoomImageSeeder] No rooms found. Skipped.');
            return;
        }

        $byRoom = [];
        $pool = [];

        // helper: extract room id from filename patterns
        $extractId = function (string $name): ?int {
            // patterns supported: room-101.*, room_101_*, 101_*, 101-*
            $n = strtolower($name);
            // remove extension first
            $n = preg_replace('/\.[a-z0-9]+$/i', '', $n);

            // prefer room-101*
            if (preg_match('/^room[-_](\d+)/', $n, $m)) {
                return (int)$m[1];
            }
            // fallback: leading number
            if (preg_match('/^(\d{1,6})($|[-_])/', $n, $m)) {
                return (int)$m[1];
            }
            return null;
        };

        foreach ($files as $file) {
            $basename = $file->getFilename();
            $relPath = $relativeDir . '/' . $basename; // save relative to public

            $roomId = $extractId($basename);
            if ($roomId && $rooms->contains('id', $roomId)) {
                $byRoom[$roomId] = $byRoom[$roomId] ?? [];
                $byRoom[$roomId][] = $relPath;
            } else {
                $pool[] = $relPath;
            }
        }

        // Insert images per room from mapped files
        $created = 0;
        foreach ($rooms as $room) {
            $paths = $byRoom[$room->id] ?? [];
            $isFirst = true;
            foreach ($paths as $p) {
                if (!RoomImage::where('room_id', $room->id)->where('image_url', $p)->exists()) {
                    RoomImage::create([
                        'room_id' => $room->id,
                        'image_url' => $p,
                        'is_primary' => $isFirst,
                    ]);
                    $created++;
                    $isFirst = false;
                }
            }
        }

        // Distribute remaining pool images round-robin, prefer rooms without images
        $roomsByNeed = $rooms->sortBy(fn($r) => $r->images()->count())->values();
        $i = 0;
        foreach ($pool as $p) {
            $room = $roomsByNeed[$i % $roomsByNeed->count()];
            $already = RoomImage::where('room_id', $room->id)->where('image_url', $p)->exists();
            if (!$already) {
                $isPrimary = $room->images()->count() === 0; // make first as primary if none
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_url' => $p,
                    'is_primary' => $isPrimary,
                ]);
                $created++;
            }
            $i++;
        }

        $this->command?->info("[RoomImageSeeder] Created {$created} room image records from '{$relativeDir}'.");
    }
}
