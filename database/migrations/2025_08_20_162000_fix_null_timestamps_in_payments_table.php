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
        // Cập nhật các payment records có created_at là null
        DB::table('payments')
            ->whereNull('created_at')
            ->update([
                'created_at' => now(),
                'updated_at' => now()
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Không cần rollback vì đây là data fix
    }
};
