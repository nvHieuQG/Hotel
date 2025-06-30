<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Sửa dữ liệu: chỉ giữ lại ảnh đầu tiên làm ảnh chính cho mỗi phòng
        $rooms = DB::table('rooms')->get();
        
        foreach ($rooms as $room) {
            $images = DB::table('room_images')
                ->where('room_id', $room->id)
                ->orderBy('id', 'asc')
                ->get();
            
            if ($images->count() > 0) {
                // Bỏ tất cả ảnh chính
                DB::table('room_images')
                    ->where('room_id', $room->id)
                    ->update(['is_primary' => false]);
                
                // Đặt ảnh đầu tiên làm ảnh chính
                DB::table('room_images')
                    ->where('id', $images->first()->id)
                    ->update(['is_primary' => true]);
            }
        }
    }

    public function down(): void
    {
        // Không cần rollback vì đây là sửa dữ liệu
    }
};