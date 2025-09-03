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
        Schema::table('tour_booking_rooms', function (Blueprint $table) {
            $table->json('assigned_room_ids')->nullable()->after('guest_details');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_booking_rooms', function (Blueprint $table) {
            $table->dropColumn('assigned_room_ids');
        });
    }
};


