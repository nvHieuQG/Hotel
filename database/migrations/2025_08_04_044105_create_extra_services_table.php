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
        Schema::create('extra_services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('applies_to', ['adult','child','both'])->default('both'); // Áp dụng cho người lớn, trẻ em, hoặc cả hai
            $table->decimal('price_adult', 10, 2)->nullable();
            $table->decimal('price_child', 10, 2)->nullable();
            $table->enum('charge_type', ['per_person','per_night','per_service','per_hour','per_use'])->default('per_service');
            $table->boolean('is_active')->default(true);
            $table->unsignedTinyInteger('child_age_min')->nullable(); // Nếu muốn giới hạn độ tuổi trẻ em
            $table->unsignedTinyInteger('child_age_max')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('extra_services');
    }
};
