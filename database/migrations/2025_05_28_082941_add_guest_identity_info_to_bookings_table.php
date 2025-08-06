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
            // Thông tin căn cước của khách
            $table->string('guest_full_name')->nullable()->after('price')->comment('Họ tên đầy đủ của khách');
            $table->string('guest_id_number')->nullable()->after('guest_full_name')->comment('Số căn cước công dân/CMND');
            $table->date('guest_birth_date')->nullable()->after('guest_id_number')->comment('Ngày sinh');
            $table->string('guest_gender')->nullable()->after('guest_birth_date')->comment('Giới tính');
            $table->string('guest_nationality')->nullable()->after('guest_gender')->comment('Quốc tịch');
            $table->string('guest_permanent_address')->nullable()->after('guest_nationality')->comment('Địa chỉ thường trú');
            $table->string('guest_current_address')->nullable()->after('guest_permanent_address')->comment('Địa chỉ tạm trú');
            $table->string('guest_phone')->nullable()->after('guest_current_address')->comment('Số điện thoại khách');
            $table->string('guest_email')->nullable()->after('guest_phone')->comment('Email khách');
            $table->string('guest_purpose_of_stay')->nullable()->after('guest_email')->comment('Mục đích lưu trú');
            $table->string('guest_vehicle_number')->nullable()->after('guest_purpose_of_stay')->comment('Biển số xe (nếu có)');
            $table->text('guest_notes')->nullable()->after('guest_vehicle_number')->comment('Ghi chú thêm về khách');
            
            // Thông tin người đặt phòng (có thể khác với khách)
            $table->string('booker_full_name')->nullable()->after('guest_notes')->comment('Họ tên người đặt phòng');
            $table->string('booker_id_number')->nullable()->after('booker_full_name')->comment('Số căn cước người đặt phòng');
            $table->string('booker_phone')->nullable()->after('booker_id_number')->comment('Số điện thoại người đặt phòng');
            $table->string('booker_email')->nullable()->after('booker_phone')->comment('Email người đặt phòng');
            $table->string('booker_relationship')->nullable()->after('booker_email')->comment('Mối quan hệ với khách');
            
            // Trạng thái giấy đăng ký tạm chú tạm vắng
            $table->enum('registration_status', ['pending', 'generated', 'sent'])->default('pending')->after('booker_relationship')->comment('Trạng thái giấy đăng ký');
            $table->timestamp('registration_generated_at')->nullable()->after('registration_status')->comment('Thời gian tạo giấy đăng ký');
            $table->timestamp('registration_sent_at')->nullable()->after('registration_generated_at')->comment('Thời gian gửi giấy đăng ký');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'guest_full_name',
                'guest_id_number', 
                'guest_birth_date',
                'guest_gender',
                'guest_nationality',
                'guest_permanent_address',
                'guest_current_address',
                'guest_phone',
                'guest_email',
                'guest_purpose_of_stay',
                'guest_vehicle_number',
                'guest_notes',
                'booker_full_name',
                'booker_id_number',
                'booker_phone',
                'booker_email',
                'booker_relationship',
                'registration_status',
                'registration_generated_at',
                'registration_sent_at'
            ]);
        });
    }
}; 