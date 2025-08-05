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
            // Cập nhật enum status để bao gồm tất cả các trạng thái
            $table->enum('status', ['pending', 'pending_payment', 'confirmed', 'checked_in', 'checked_out', 'cancelled', 'completed', 'no_show'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Revert về enum cũ
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->change();
        });
    }
};
