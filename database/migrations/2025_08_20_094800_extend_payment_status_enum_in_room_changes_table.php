<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Extend ENUM to include refund states
        DB::statement("ALTER TABLE room_changes 
            MODIFY COLUMN payment_status 
            ENUM('not_required','pending','paid_at_reception','refund_pending','refunded') 
            NOT NULL DEFAULT 'not_required'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Normalize values that won't exist in the old ENUM
        DB::statement("UPDATE room_changes 
            SET payment_status = 'not_required' 
            WHERE payment_status IN ('refund_pending','refunded')");

        // Revert ENUM to old set
        DB::statement("ALTER TABLE room_changes 
            MODIFY COLUMN payment_status 
            ENUM('not_required','pending','paid_at_reception') 
            NOT NULL DEFAULT 'not_required'");
    }
};
