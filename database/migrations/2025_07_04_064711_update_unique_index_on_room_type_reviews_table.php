<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('room_type_reviews', function (Blueprint $table) {
            // Xoá unique cũ
            $table->dropUnique('room_type_reviews_user_id_room_type_id_unique');
            // Thêm unique mới
            $table->unique(['user_id', 'booking_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_type_reviews', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'booking_id']);
            $table->unique(['user_id', 'room_type_id']);
        });
    }
};
