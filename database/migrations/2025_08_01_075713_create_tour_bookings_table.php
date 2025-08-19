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
        Schema::create('tour_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('booking_id')->unique(); // Mã đặt phòng
            $table->string('tour_name'); // Tên tour
            $table->integer('total_guests'); // Tổng số khách
            $table->integer('total_rooms'); // Tổng số phòng cần đặt
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->decimal('total_price', 12, 2); // Tổng tiền
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
            $table->text('special_requests')->nullable(); // Yêu cầu đặc biệt
            $table->text('tour_details')->nullable(); // Chi tiết tour (JSON)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_bookings');
    }
};
