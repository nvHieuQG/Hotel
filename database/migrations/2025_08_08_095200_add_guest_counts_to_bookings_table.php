<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'adults_count')) {
                $table->unsignedTinyInteger('adults_count')->default(1)->after('surcharge');
            }
            if (!Schema::hasColumn('bookings', 'children_count')) {
                $table->unsignedTinyInteger('children_count')->default(0)->after('adults_count');
            }
            if (!Schema::hasColumn('bookings', 'infants_count')) {
                $table->unsignedTinyInteger('infants_count')->default(0)->after('children_count');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'adults_count')) {
                $table->dropColumn('adults_count');
            }
            if (Schema::hasColumn('bookings', 'children_count')) {
                $table->dropColumn('children_count');
            }
            if (Schema::hasColumn('bookings', 'infants_count')) {
                $table->dropColumn('infants_count');
            }
        });
    }
};
