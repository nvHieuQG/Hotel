<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->json('vat_invoice_info')->nullable()->after('registration_sent_at');
            $table->enum('vat_invoice_status', ['pending', 'generated', 'sent'])->default('pending')->after('vat_invoice_info');
            $table->timestamp('vat_invoice_generated_at')->nullable()->after('vat_invoice_status');
            $table->timestamp('vat_invoice_sent_at')->nullable()->after('vat_invoice_generated_at');
            $table->string('vat_invoice_file_path')->nullable()->after('vat_invoice_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['vat_invoice_info', 'vat_invoice_status', 'vat_invoice_generated_at', 'vat_invoice_sent_at', 'vat_invoice_file_path']);
        });
    }
};


