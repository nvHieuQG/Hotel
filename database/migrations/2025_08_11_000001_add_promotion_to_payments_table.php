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
        if (!Schema::hasTable('payments')) {
            return; // Skip if payments table not created yet
        }

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'promotion_id')) {
                $table->foreignId('promotion_id')->nullable()->after('booking_id')
                    ->constrained('promotions')->nullOnDelete();
            }
            if (!Schema::hasColumn('payments', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('amount');
            }

            // Add index if not present (safe to try)
            try { $table->index('promotion_id'); } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'promotion_id')) {
                // Drop FK and column safely
                try { $table->dropConstrainedForeignId('promotion_id'); } catch (\Throwable $e) {}
            }
            if (Schema::hasColumn('payments', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};
