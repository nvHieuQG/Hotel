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
            // Promotion fields
            $table->string('promotion_code')->nullable()->after('preferred_payment_method');
            $table->decimal('promotion_discount', 12, 2)->default(0)->after('promotion_code');
            $table->decimal('final_price', 12, 2)->nullable()->after('promotion_discount');
            
            // VAT invoice fields
            $table->boolean('need_vat_invoice')->default(false)->after('final_price');
            $table->string('company_name')->nullable()->after('need_vat_invoice');
            $table->string('company_tax_code')->nullable()->after('company_name');
            $table->string('company_address')->nullable()->after('company_tax_code');
            $table->string('company_email')->nullable()->after('company_address');
            $table->string('company_phone')->nullable()->after('company_email');
            $table->string('vat_invoice_number')->nullable()->after('company_phone');
            $table->timestamp('vat_invoice_created_at')->nullable()->after('vat_invoice_number');
            
            // Guest identity fields (tạm trú tạm vắng)
            $table->json('guest_identity_info')->nullable()->after('vat_invoice_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tour_bookings', function (Blueprint $table) {
            $table->dropColumn([
                'promotion_code',
                'promotion_discount', 
                'final_price',
                'need_vat_invoice',
                'company_name',
                'company_tax_code',
                'company_address',
                'company_email',
                'company_phone',
                'vat_invoice_number',
                'vat_invoice_created_at',
                'guest_identity_info'
            ]);
        });
    }
};
