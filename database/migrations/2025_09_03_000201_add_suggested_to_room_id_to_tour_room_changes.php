<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_room_changes', function (Blueprint $table) {
            if (!Schema::hasColumn('tour_room_changes', 'suggested_to_room_id')) {
                $table->unsignedBigInteger('suggested_to_room_id')->nullable()->after('to_room_id');
            }
            if (Schema::hasColumn('tour_room_changes', 'to_room_id')) {
                $table->unsignedBigInteger('to_room_id')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tour_room_changes', function (Blueprint $table) {
            if (Schema::hasColumn('tour_room_changes', 'suggested_to_room_id')) {
                $table->dropColumn('suggested_to_room_id');
            }
        });
    }
};


