<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('room_changes')) {
            return;
        }
        // Ensure the columns exist before running raw updates
        $needed = ['payment_status', 'price_difference', 'status'];
        foreach ($needed as $col) {
            if (!Schema::hasColumn('room_changes', $col)) {
                return;
            }
        }

        // Cập nhật payment_status cho các room changes có chênh lệch giá > 0
        DB::statement("
            UPDATE room_changes 
            SET payment_status = 'pending' 
            WHERE price_difference > 0 
            AND status IN ('approved', 'completed')
        ");
        
        // Đảm bảo các room changes không có chênh lệch giá được set đúng
        DB::statement("
            UPDATE room_changes 
            SET payment_status = 'not_required' 
            WHERE price_difference <= 0
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('room_changes') || !Schema::hasColumn('room_changes', 'payment_status')) {
            return;
        }
        DB::statement("UPDATE room_changes SET payment_status = 'not_required'");
    }
};
