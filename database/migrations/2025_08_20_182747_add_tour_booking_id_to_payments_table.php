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
        Schema::table('payments', function (Blueprint $table) {
            // Thêm cột tour_booking_id
            $table->unsignedBigInteger('tour_booking_id')->nullable()->after('booking_id');
            
            // Thêm foreign key cho tour_booking_id
            $table->foreign('tour_booking_id')->references('id')->on('tour_bookings')->onDelete('cascade');
            
            // Sửa lại foreign key cho booking_id để nullable
            $table->dropForeign(['booking_id']);
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Xóa foreign key tour_booking_id
            $table->dropForeign(['tour_booking_id']);
            $table->dropColumn('tour_booking_id');
            
            // Khôi phục foreign key booking_id
            $table->dropForeign(['booking_id']);
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
        });
    }
};
