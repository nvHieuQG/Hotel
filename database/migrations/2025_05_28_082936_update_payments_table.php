<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Skip entirely if payments table doesn't exist yet (due to timestamp order)
        if (!Schema::hasTable('payments')) {
            return;
        }

        // Kiểm tra nếu các cột đã tồn tại thì không làm gì
        if (
            Schema::hasColumn('payments', 'payment_method') && 
            Schema::hasColumn('payments', 'currency') && 
            Schema::hasColumn('payments', 'transaction_id')
        ) {
            // vẫn có thể cần đảm bảo index tồn tại nhưng tránh lỗi nếu đã có
            // Tiếp tục xuống để thêm index an toàn hoặc return sớm
            // return; // uncomment nếu muốn no-op hoàn toàn khi đủ cột
        }

        Schema::table('payments', function (Blueprint $table) {
            // Thêm cột payment_method mới nếu chưa có
            if (!Schema::hasColumn('payments', 'payment_method')) {
                $table->string('payment_method', 50)->nullable()->after('id');
            }

            // Thêm các cột mới nếu chưa có
            if (!Schema::hasColumn('payments', 'currency')) {
                $table->string('currency', 3)->default('VND')->after('amount');
            }
            if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'gateway_response')) {
                $table->json('gateway_response')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('payments', 'gateway_code')) {
                $table->string('gateway_code')->nullable()->after('gateway_response');
            }
            if (!Schema::hasColumn('payments', 'gateway_message')) {
                $table->text('gateway_message')->nullable()->after('gateway_code');
            }
            if (!Schema::hasColumn('payments', 'paid_at')) {
                $table->timestamp('paid_at')->nullable()->after('gateway_message');
            }
            if (!Schema::hasColumn('payments', 'gateway_name')) {
                $table->string('gateway_name')->nullable()->after('paid_at');
            }
            if (!Schema::hasColumn('payments', 'updated_at')) {
                $table->timestamp('updated_at')->nullable()->after('created_at');
            }

            // Thêm indexes nếu chưa có (Laravel không có API check index dễ dàng; 
            // thêm lại thường an toàn nếu tên index khác nhau. Ở đây ta giữ nguyên.)
            try { $table->index(['booking_id', 'status']); } catch (\Throwable $e) {}
            try { $table->index('transaction_id'); } catch (\Throwable $e) {}
            try { $table->index('payment_method'); } catch (\Throwable $e) {}
        });

        // Cập nhật dữ liệu từ cột method sang payment_method nếu cần
        if (Schema::hasColumn('payments', 'method') && Schema::hasColumn('payments', 'payment_method')) {
            DB::statement('UPDATE payments SET payment_method = method WHERE payment_method IS NULL');
        }

        // Xóa cột method cũ nếu tồn tại
        if (Schema::hasColumn('payments', 'method')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropColumn('method');
            });
        }

        // Cập nhật enum status nếu cần
        if (Schema::hasColumn('payments', 'status')) {
            $columns = DB::select("SHOW COLUMNS FROM payments LIKE 'status'");
            if (!empty($columns)) {
                $currentStatus = $columns[0];
                if (strpos($currentStatus->Type, 'pending,processing,completed,failed,cancelled,refunded') === false) {
                    DB::statement("ALTER TABLE payments MODIFY COLUMN status ENUM('pending', 'processing', 'completed', 'failed', 'cancelled', 'refunded') DEFAULT 'pending'");
                }
            }
        }
    }

    public function down(): void
    {
        // No rollback necessary for no-op migration
    }
};