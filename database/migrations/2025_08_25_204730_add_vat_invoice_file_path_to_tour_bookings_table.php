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
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->string('vat_invoice_file_path')->nullable()->after('vat_invoice_created_at');
            $table->string('vat_invoice_status')->default('pending')->after('vat_invoice_file_path');
            $table->timestamp('vat_invoice_generated_at')->nullable()->after('vat_invoice_status');
            $table->timestamp('vat_invoice_sent_at')->nullable()->after('vat_invoice_generated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'vat_invoice_file_path',
                'vat_invoice_status',
                'vat_invoice_generated_at',
                'vat_invoice_sent_at'
            ]);
        });
    }
};
