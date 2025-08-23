<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('room_changes')) return;

        Schema::table('room_changes', function (Blueprint $table) {
            if (!Schema::hasColumn('room_changes', 'payment_status')) {
                $table->enum('payment_status', ['not_required','pending','paid_at_reception'])
                      ->default('not_required');
            }
            if (!Schema::hasColumn('room_changes', 'paid_at')) {
                $table->timestamp('paid_at')->nullable();
            }
            if (!Schema::hasColumn('room_changes', 'paid_by')) {
                $table->unsignedBigInteger('paid_by')->nullable();
                $table->foreign('paid_by')->references('id')->on('users');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('room_changes')) return;

        Schema::table('room_changes', function (Blueprint $table) {
            if (Schema::hasColumn('room_changes', 'paid_by')) {
                $table->dropForeign(['paid_by']);
            }
            $drop = [];
            foreach (['payment_status','paid_at','paid_by'] as $col) {
                if (Schema::hasColumn('room_changes', $col)) $drop[] = $col;
            }
            if ($drop) {
                $table->dropColumn($drop);
            }
        });
    }
};