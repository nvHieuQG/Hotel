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
        Schema::table('bookings', function (Blueprint $table) {
            $table->unsignedBigInteger('promotion_id')->nullable()->after('surcharge');
            $table->decimal('promotion_discount', 10, 2)->default(0)->after('promotion_id');
            $table->string('promotion_code')->nullable()->after('promotion_discount');
            
            $table->foreign('promotion_id')->references('id')->on('promotions')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn(['promotion_id', 'promotion_discount', 'promotion_code']);
        });
    }
};
