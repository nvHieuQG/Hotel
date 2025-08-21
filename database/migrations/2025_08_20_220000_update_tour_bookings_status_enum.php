<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Thêm các cột mới cho check-in/check-out time
        Schema::table('tour_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('tour_bookings', 'check_in_time')) {
                $table->time('check_in_time')->nullable()->after('check_out_date');
            }
            if (!Schema::hasColumn('tour_bookings', 'check_out_time')) {
                $table->time('check_out_time')->nullable()->after('check_in_time');
            }
        });
        
        // Thay đổi cột status từ enum sang string để hỗ trợ các trạng thái mới
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->string('status_new')->default('pending')->after('status');
        });
        
        // Copy dữ liệu từ cột cũ
        DB::statement("UPDATE tour_bookings SET status_new = status");
        
        // Xóa cột cũ và đổi tên cột mới
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->renameColumn('status_new', 'status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa các cột mới
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->dropColumn(['check_in_time', 'check_out_time']);
        });
        
        // Revert về enum cũ
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->enum('status_old', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending')->after('status');
        });
        
        // Copy dữ liệu về cột enum
        DB::statement("UPDATE tour_bookings SET status_old = CASE 
            WHEN status IN ('pending', 'confirmed', 'cancelled', 'completed') THEN status 
            ELSE 'pending' 
        END");
        
        // Xóa cột string và đổi tên cột enum
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->renameColumn('status_old', 'status');
        });
    }
};
