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
        Schema::table('room_changes', function (Blueprint $table) {
            $table->enum('payment_status', ['not_required', 'pending', 'paid_at_reception'])
                  ->default('not_required')
                  ->after('status');
            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->unsignedBigInteger('paid_by')->nullable()->after('paid_at');
            
            $table->foreign('paid_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_changes', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['payment_status', 'paid_at', 'paid_by']);
        });
    }
};
