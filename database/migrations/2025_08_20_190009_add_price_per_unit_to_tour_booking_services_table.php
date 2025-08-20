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
        Schema::table('tour_booking_services', function (Blueprint $table) {
            // Thêm cột price_per_unit sau service_name
            $table->decimal('price_per_unit', 12, 2)->default(0)->after('service_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_booking_services', function (Blueprint $table) {
            // Xóa cột price_per_unit
            $table->dropColumn('price_per_unit');
        });
    }
};
