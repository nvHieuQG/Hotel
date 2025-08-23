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
        if (!Schema::hasTable('room_changes')) {
            return; // Skip if table not yet created
        }

        if (!Schema::hasColumn('room_changes', 'payment_status')) {
            Schema::table('room_changes', function (Blueprint $table) {
                $table->enum('payment_status', ['not_required', 'pending', 'paid_at_reception'])
                      ->default('not_required')
                      ->after('status');
                $table->timestamp('paid_at')->nullable()->after('payment_status');
                $table->unsignedBigInteger('paid_by')->nullable()->after('paid_at');

                $table->foreign('paid_by')->references('id')->on('users');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('room_changes')) {
            return;
        }

        if (Schema::hasColumn('room_changes', 'paid_by')) {
            Schema::table('room_changes', function (Blueprint $table) {
                $table->dropForeign(['paid_by']);
            });
        }

        $cols = array_filter(['payment_status','paid_at','paid_by'], fn($c)=> Schema::hasColumn('room_changes',$c));
        if (!empty($cols)) {
            Schema::table('room_changes', function (Blueprint $table) use ($cols) {
                $table->dropColumn($cols);
            });
        }
    }
};
