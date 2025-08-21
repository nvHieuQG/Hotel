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
            // Thêm các cột mới
            $table->string('currency', 3)->default('VND')->after('amount');
            $table->string('transaction_id')->nullable()->after('status');
            $table->json('gateway_response')->nullable()->after('transaction_id');
            $table->string('gateway_code')->nullable()->after('gateway_response');
            $table->text('gateway_message')->nullable()->after('gateway_code');
            $table->timestamp('paid_at')->nullable()->after('gateway_message');
            $table->string('gateway_name')->nullable()->after('paid_at');

            // Thêm indexes
            $table->index(['booking_id', 'status']);
            $table->index('transaction_id');
            $table->index('method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Xóa indexes
            $table->dropIndex(['booking_id', 'status']);
            $table->dropIndex(['transaction_id']);
            $table->dropIndex(['method']);

            // Xóa các cột mới
            $table->dropColumn([
                'currency',
                'transaction_id',
                'gateway_response',
                'gateway_code',
                'gateway_message',
                'paid_at',
                'gateway_name'
            ]);
        });
    }
};
