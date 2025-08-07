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
        Schema::create('room_changes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('old_room_id')->constrained('rooms');
            $table->foreignId('new_room_id')->constrained('rooms');
            $table->text('reason')->nullable(); // Lý do đổi phòng
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed', 'cancelled'])->default('pending');
            $table->decimal('price_difference', 10, 2)->default(0); // Chênh lệch giá (có thể âm hoặc dương)
            $table->foreignId('requested_by')->constrained('users'); // Người yêu cầu (user hoặc admin)
            $table->foreignId('approved_by')->nullable()->constrained('users'); // Người duyệt (admin)
            $table->text('admin_note')->nullable(); // Ghi chú của admin
            $table->text('customer_note')->nullable(); // Ghi chú của khách
            $table->timestamp('approved_at')->nullable(); // Thời gian duyệt
            $table->timestamp('completed_at')->nullable(); // Thời gian hoàn thành
            $table->timestamps();
            
            // Index để tối ưu truy vấn
            $table->index(['booking_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_changes');
    }
};
