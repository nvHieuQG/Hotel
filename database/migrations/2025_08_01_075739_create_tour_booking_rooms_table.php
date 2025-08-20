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
        Schema::create('tour_booking_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('room_type_id')->constrained()->onDelete('cascade');
            $table->integer('quantity'); // Số lượng phòng loại này
            $table->integer('guests_per_room'); // Số khách mỗi phòng
            $table->decimal('price_per_room', 12, 2); // Giá mỗi phòng
            $table->decimal('total_price', 12, 2); // Tổng tiền cho loại phòng này
            $table->text('guest_details')->nullable(); // Chi tiết khách (JSON)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_booking_rooms');
    }
};
