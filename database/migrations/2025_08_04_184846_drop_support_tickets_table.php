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
        // Xóa foreign key constraint trước
        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropForeign(['ticket_id']);
            $table->dropColumn('ticket_id');
        });

        // Xóa bảng support_tickets
        Schema::dropIfExists('support_tickets');

        // Thêm các cột mới vào support_messages
        Schema::table('support_messages', function (Blueprint $table) {
            $table->string('subject')->nullable()->after('sender_type');
            $table->string('conversation_id')->nullable()->after('subject');
            $table->index('conversation_id');
            $table->index(['sender_id', 'sender_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Xóa các cột mới
        Schema::table('support_messages', function (Blueprint $table) {
            $table->dropIndex(['conversation_id']);
            $table->dropIndex(['sender_id', 'sender_type']);
            $table->dropColumn(['subject', 'conversation_id']);
        });

        // Tạo lại bảng support_tickets
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('subject');
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->timestamps();
        });

        // Thêm lại cột ticket_id
        Schema::table('support_messages', function (Blueprint $table) {
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
        });
    }
};
