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
        if (!Schema::hasTable('tour_bookings')) return;

        Schema::table('tour_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('tour_bookings', 'promotion_id')) {
                // Place after promotion_code only if the column exists
                if (Schema::hasColumn('tour_bookings', 'promotion_code')) {
                    $table->foreignId('promotion_id')->nullable()->after('promotion_code')
                        ->constrained('promotions')->onDelete('set null');
                } else {
                    $table->foreignId('promotion_id')->nullable()
                        ->constrained('promotions')->onDelete('set null');
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('tour_bookings')) return;

        Schema::table('tour_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('tour_bookings', 'promotion_id')) {
                try { $table->dropForeign(['promotion_id']); } catch (\Throwable $e) {}
                try { $table->dropColumn('promotion_id'); } catch (\Throwable $e) {}
            }
        });
    }
};
