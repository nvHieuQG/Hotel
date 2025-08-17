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
        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'promotion_id')) {
                $table->foreignId('promotion_id')->nullable()->after('booking_id')
                    ->constrained('promotions')->nullOnDelete();
            }
            if (!Schema::hasColumn('payments', 'discount_amount')) {
                $table->decimal('discount_amount', 10, 2)->default(0)->after('amount');
            }

            $table->index('promotion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'promotion_id')) {
                $table->dropConstrainedForeignId('promotion_id');
            }
            if (Schema::hasColumn('payments', 'discount_amount')) {
                $table->dropColumn('discount_amount');
            }
        });
    }
};


