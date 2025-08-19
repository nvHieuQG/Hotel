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
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->enum('payment_status', ['completed', 'partial', 'pending', 'overdue'])->nullable()->after('status');
            $table->enum('preferred_payment_method', ['credit_card', 'bank_transfer', 'cash', 'online_payment'])->nullable()->after('payment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'preferred_payment_method']);
        });
    }
};
