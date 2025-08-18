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
        Schema::table('bookings', function (Blueprint $table) {
            // JSON details of selected extra services on confirmation page
            $table->json('extra_services')->nullable()->after('surcharge');
            // Total extra services cost calculated on frontend and validated server-side
            $table->decimal('extra_services_total', 12, 2)->default(0)->after('extra_services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'extra_services_total')) {
                $table->dropColumn('extra_services_total');
            }
            if (Schema::hasColumn('bookings', 'extra_services')) {
                $table->dropColumn('extra_services');
            }
        });
    }
};
