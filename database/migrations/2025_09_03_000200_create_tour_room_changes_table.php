<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_room_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_booking_id')->constrained('tour_bookings');
            $table->unsignedBigInteger('from_room_id');
            $table->unsignedBigInteger('to_room_id')->nullable();
            $table->unsignedBigInteger('suggested_to_room_id')->nullable();
            $table->integer('price_difference')->default(0);
            $table->string('status', 20)->default('pending'); // pending|approved|rejected|completed
            $table->string('reason')->nullable();
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['tour_booking_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_room_changes');
    }
};


