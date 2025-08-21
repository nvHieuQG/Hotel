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
        // Cập nhật enum values cho cột status
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'paid', 'failed', 'completed', 'processing', 'cod') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback về enum cũ
        DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'paid', 'failed') DEFAULT 'pending'");
    }
};
