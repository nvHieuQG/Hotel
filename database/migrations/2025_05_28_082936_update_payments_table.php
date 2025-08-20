<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No-op migration to satisfy sequence; original changes already applied elsewhere.
        // You can add columns to `payments` here if needed.
        Schema::table('payments', function (Blueprint $table) {
            // Ensure table exists before running noop to avoid errors
        });
    }

    public function down(): void
    {
        // No rollback necessary for no-op migration
    }
};

 