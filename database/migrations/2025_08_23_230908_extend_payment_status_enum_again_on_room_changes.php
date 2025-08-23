<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('room_changes') || !Schema::hasColumn('room_changes', 'payment_status')) {
            return;
        }

        DB::statement("ALTER TABLE room_changes 
            MODIFY COLUMN payment_status 
            ENUM('not_required','pending','paid_at_reception','refund_pending','refunded') 
            NOT NULL DEFAULT 'not_required'");
    }

    public function down(): void
    {
        if (!Schema::hasTable('room_changes') || !Schema::hasColumn('room_changes', 'payment_status')) {
            return;
        }

        // Chuẩn hóa giá trị trước khi thu hẹp ENUM lại
        DB::statement("UPDATE room_changes 
            SET payment_status = 'not_required' 
            WHERE payment_status IN ('refund_pending','refunded')");

        DB::statement("ALTER TABLE room_changes 
            MODIFY COLUMN payment_status 
            ENUM('not_required','pending','paid_at_reception') 
            NOT NULL DEFAULT 'not_required'");
    }
};