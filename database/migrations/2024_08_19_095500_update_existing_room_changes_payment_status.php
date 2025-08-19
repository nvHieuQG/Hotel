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
        DB::statement("UPDATE room_changes SET payment_status = 'not_required'");
    }
};
