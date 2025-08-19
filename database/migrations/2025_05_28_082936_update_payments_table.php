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
            // Đổi tên cột method thành payment_method
            $table->renameColumn('method', 'payment_method');

            // Thêm các cột mới
            $table->string('currency', 3)->default('VND')->after('amount');
            $table->string('transaction_id')->nullable()->after('status');
            $table->json('gateway_response')->nullable()->after('transaction_id');
            $table->string('gateway_code')->nullable()->after('gateway_response');
            $table->text('gateway_message')->nullable()->after('gateway_code');
            $table->timestamp('paid_at')->nullable()->after('gateway_message');
            $table->string('gateway_name')->nullable()->after('paid_at');
            $table->timestamp('updated_at')->nullable()->after('created_at');

            // Cập nhật enum status
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded'])->default('pending')->change();

            // Thêm indexes
            $table->index(['booking_id', 'status']);
            $table->index('transaction_id');
            $table->index('payment_method');
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
            $table->dropIndex(['payment_method']);

            // Xóa các cột mới
            $table->dropColumn([
                'currency',
                'transaction_id',
                'gateway_response',
                'gateway_code',
                'gateway_message',
                'paid_at',
                'gateway_name',
                'updated_at'
            ]);

            // Đổi tên cột về ban đầu
            $table->renameColumn('payment_method', 'method');

            // Khôi phục enum status
            $table->enum('status', ['pending', 'paid', 'failed'])->default('pending')->change();
        });
    }
};