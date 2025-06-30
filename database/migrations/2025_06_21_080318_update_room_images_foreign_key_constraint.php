<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('room_images', function (Blueprint $table) {
            // Xóa foreign key cũ
            $table->dropForeign(['room_id']);
            
            // Thêm foreign key mới với cascade delete
            $table->foreign('room_id')
                  ->references('id')
                  ->on('rooms')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('room_images', function (Blueprint $table) {
            // Xóa foreign key mới
            $table->dropForeign(['room_id']);
            
            // Thêm lại foreign key cũ
            $table->foreign('room_id')
                  ->references('id')
                  ->on('rooms');
        });
    }
};