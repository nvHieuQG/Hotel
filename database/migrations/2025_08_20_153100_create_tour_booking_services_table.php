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
        Schema::create('tour_booking_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_booking_id')->constrained('tour_bookings')->onDelete('cascade');
            $table->string('service_type'); // transport, guide, meal, entertainment, other
            $table->string('service_name');
            $table->decimal('unit_price', 12, 2);
            $table->integer('quantity')->default(1);
            $table->decimal('total_price', 12, 2);
            $table->text('notes')->nullable();
            $table->string('status')->default('active'); // active, cancelled
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_booking_services');
    }
};
