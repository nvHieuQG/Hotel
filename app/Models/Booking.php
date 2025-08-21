<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'booking_id',
        'room_id',
        'check_in_date',
        'check_out_date',
        'status',
        'price',
        'surcharge',
        'extra_services',
        'extra_services_total',
        'adults_count',
        'children_count',
        'infants_count',
        'promotion_id',
        'promotion_discount',
        'promotion_code',
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
        'registration_sent_at',
        // VAT invoice fields
        'vat_invoice_info',
        'vat_invoice_status',
        'vat_invoice_generated_at',
        'vat_invoice_sent_at',
        'vat_invoice_file_path'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_date' => 'datetime',
        'check_out_date' => 'datetime',
        'guest_birth_date' => 'date',
        'extra_services' => 'array',
        'extra_services_total' => 'decimal:2',
        'registration_generated_at' => 'datetime',
        'registration_sent_at' => 'datetime',
        'vat_invoice_info' => 'array',
        'vat_invoice_generated_at' => 'datetime',
        'vat_invoice_sent_at' => 'datetime',
    ];

    /**
     * Get the user that owns the booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the room that is booked.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get the review for this booking.
     */
    public function review()
    {
        return $this->hasOne(RoomTypeReview::class);
    }

    /**
     * Get the promotion applied to this booking.
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get the final price after applying promotion discount.
     */
    public function getFinalPriceAttribute()
    {
        return $this->price - $this->promotion_discount;
    }

    /**
     * Get the total amount including surcharge and promotion discount.
     */
    public function getTotalAmountAttribute()
    {
        return $this->price + $this->surcharge - $this->promotion_discount;
    }

    /**
     * Accessor cho ngày check-in (chỉ lấy phần date)
     */
    public function getCheckInDateOnlyAttribute()
    {
        return $this->check_in_date ? $this->check_in_date->format('Y-m-d') : null;
    }

    /**
     * Get the notes for this booking.
     */
    public function notes()
    {
        return $this->hasMany(BookingNote::class);
    }

    /**
     * Get the public notes for this booking.
     */
    public function publicNotes()
    {
        return $this->hasMany(BookingNote::class)->public();
    }

    /**
     * Get the internal notes for this booking.
     */
    public function internalNotes()
    {
        return $this->hasMany(BookingNote::class)->internal();
    }

    /**
     * Lấy danh sách dịch vụ đã thêm cho booking này.
     */
    public function bookingServices()
    {
        return $this->hasMany(BookingService::class);
    }

    /**
     * Lấy danh sách dịch vụ thuộc loại phòng.
     */
    public function roomTypeServices()
    {
        return $this->hasMany(BookingService::class)->roomType();
    }

    /**
     * Lấy danh sách dịch vụ bổ sung (được thêm thủ công cho booking này).
     */
    public function additionalServices()
    {
        return $this->hasMany(BookingService::class)->additional();
    }

    /**
     * Lấy các dịch vụ có sẵn từ loại phòng nhưng chưa được thêm vào booking.
     */
    public function getAvailableRoomTypeServices()
    {
        $roomTypeId = $this->room->room_type_id;
        $existingServiceIds = $this->bookingServices()->pluck('service_id')->toArray();

        return \App\Models\Service::whereHas('roomTypes', function ($query) use ($roomTypeId) {
            $query->where('room_type_id', $roomTypeId);
        })->whereNotIn('id', $existingServiceIds)->get();
    }

    /**
     * Get the room changes for this booking.
     */
    public function roomChanges()
    {
        return $this->hasMany(RoomChange::class);
    }

    /**
     * Get the pending room change for this booking.
     */
    public function pendingRoomChange()
    {
        return $this->hasOne(RoomChange::class)->pending();
    }

    /**
     * Get the payments for this booking.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Get the latest payment for this booking.
     */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    /**
     * Check if booking has successful payment
     */
    public function hasSuccessfulPayment()
    {
        return $this->payments()->where('status', 'completed')->exists();
    }

    /**
     * Get the total amount paid for this booking
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Check if booking is fully paid
     */
    public function isFullyPaid()
    {
        return $this->total_paid >= $this->total_booking_price;
    }

    /**
     * Tính tổng giá tiền của các dịch vụ trong booking.
     */
    public function getTotalServicesPriceAttribute()
    {
        return $this->bookingServices()->sum('total_price');
    }

    /**
     * Tính tổng giá trị của booking bao gồm cả tiền phòng và dịch vụ.
     */
    public function getTotalBookingPriceAttribute()
    {
        // Tính tổng thực tế = giá phòng + phụ phí + dịch vụ khách chọn + dịch vụ admin thêm
        $adminServicesTotal = $this->bookingServices()->sum('total_price');
        return $this->price + $adminServicesTotal;
    }

    /**
     * Lấy giá phòng cơ bản (không bao gồm dịch vụ và phụ phí)
     */
    public function getBaseRoomPriceAttribute()
    {
        // Tính giá phòng cơ bản = tổng giá - phụ phí - dịch vụ khách chọn
        $basePrice = $this->price - $this->surcharge - $this->extra_services_total;
        return max(0, $basePrice); // Đảm bảo không âm
    }

    /**
     * Accessor cho ngày check-out (chỉ lấy phần date)
     */
    public function getCheckOutDateOnlyAttribute()
    {
        return $this->check_out_date ? $this->check_out_date->format('Y-m-d') : null;
    }

    /**
     * Accessor cho giờ check-in (chỉ lấy phần time)
     */
    public function getCheckInTimeAttribute()
    {
        return $this->check_in_date ? $this->check_in_date->format('H:i') : null;
    }

    /**
     * Accessor cho giờ check-out (chỉ lấy phần time)
     */
    public function getCheckOutTimeAttribute()
    {
        return $this->check_out_date ? $this->check_out_date->format('H:i') : null;
    }

    /**
     * Accessor for total_price
     */
    public function getTotalPriceAttribute()
    {
        return $this->price;
    }

    /**
     * Get the number of guests.
     */
    public function getGuestsAttribute()
    {
        // Mặc định là 2 khách nếu không có thông tin
        return 2;
    }

    /**
     * Kiểm tra xem booking đã hoàn thành chưa (check-out xong)
     */
    public function isCompleted()
    {
        return $this->status === 'completed' ||
            ($this->check_out_date && $this->check_out_date->isPast());
    }

    /**
     * Lấy trạng thái hiển thị
     */
    public function getStatusTextAttribute()
    {
        return match ($this->status) {
            'pending' => 'Chờ xác nhận',
            'pending_payment' => 'Chờ thanh toán',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã nhận phòng',
            'checked_out' => 'Đã trả phòng',
            'completed' => 'Đã hoàn thành',
            'cancelled' => 'Đã hủy',
            'no_show' => 'Khách không đến',
            default => 'Không xác định'
        };
    }

    /**
     * Lấy trạng thái hiển thị của giấy đăng ký
     */
    public function getRegistrationStatusTextAttribute()
    {
        return match($this->registration_status) {
            'pending' => 'Chưa tạo',
            'generated' => 'Đã tạo',
            'sent' => 'Đã gửi',
            default => 'Không xác định'
        };
    }

    /**
     * Kiểm tra xem có thể tạo giấy đăng ký tạm chú tạm vắng không
     */
    public function canGenerateRegistration(): bool
    {
        return $this->status === 'confirmed' && 
               $this->guest_full_name && 
               $this->guest_id_number;
    }

    /**
     * Kiểm tra xem đã có đầy đủ thông tin căn cước chưa
     */
    public function hasCompleteIdentityInfo(): bool
    {
        return !empty($this->guest_full_name) && 
               !empty($this->guest_id_number) && 
               !empty($this->guest_birth_date) && 
               !empty($this->guest_gender) && 
               !empty($this->guest_nationality) && 
               !empty($this->guest_permanent_address);
    }

    /**
     * Lấy thông tin khách sạn (có thể cấu hình từ config)
     */
    public function getHotelInfo(): array
    {
        return [
            'name' => config('hotel.name', 'Marron Hotel'),
            'address' => config('hotel.address', '123 Đường ABC, Quận XYZ, TP.HCM'),
            'phone' => config('hotel.phone', '028-1234-5678'),
            'email' => config('hotel.email', 'info@marronhotel.com'),
            'license_number' => config('hotel.license_number', 'GP123456789'),
            'tax_code' => config('hotel.tax_code', '0123456789'),
            'representative' => config('hotel.representative', 'Nguyễn Văn A'),
            'representative_position' => config('hotel.representative_position', 'Giám đốc'),
            'representative_id' => config('hotel.representative_id', '012345678901'),
        ];
    }

    /**
     * Lấy trạng thái thanh toán tổng quát của booking
     */
    public function getPaymentStatusAttribute()
    {
        // Nếu không có payment nào
        if ($this->payments()->count() === 0) {
            return 'unpaid';
        }

        // Kiểm tra xem có payment thành công không
        $hasSuccessfulPayment = $this->payments()->where('status', 'completed')->exists();
        if ($hasSuccessfulPayment) {
            return 'paid';
        }

        // Kiểm tra xem có payment đang xử lý không
        $hasProcessingPayment = $this->payments()->where('status', 'processing')->exists();
        if ($hasProcessingPayment) {
            return 'processing';
        }

        // Kiểm tra xem có payment pending không
        $hasPendingPayment = $this->payments()->where('status', 'pending')->exists();
        if ($hasPendingPayment) {
            return 'pending';
        }

        // Kiểm tra xem có payment failed không
        $hasFailedPayment = $this->payments()->where('status', 'failed')->exists();
        if ($hasFailedPayment) {
            return 'failed';
        }

        // Kiểm tra xem có payment cancelled không
        $hasCancelledPayment = $this->payments()->where('status', 'cancelled')->exists();
        if ($hasCancelledPayment) {
            return 'cancelled';
        }

        // Kiểm tra xem có payment refunded không
        $hasRefundedPayment = $this->payments()->where('status', 'refunded')->exists();
        if ($hasRefundedPayment) {
            return 'refunded';
        }

        return 'unpaid';
    }

    /**
     * Lấy text hiển thị trạng thái thanh toán
     */
    public function getPaymentStatusTextAttribute()
    {
        return match ($this->payment_status) {
            'paid' => 'Đã thanh toán',
            'processing' => 'Đang xử lý',
            'pending' => 'Chờ thanh toán',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
            'unpaid' => 'Chưa thanh toán',
            default => 'Không xác định'
        };
    }

    /**
     * Lấy icon cho trạng thái thanh toán
     */
    public function getPaymentStatusIconAttribute()
    {
        return match ($this->payment_status) {
            'paid' => 'fas fa-check-circle',
            'processing' => 'fas fa-clock',
            'pending' => 'fas fa-hourglass-half',
            'failed' => 'fas fa-times-circle',
            'cancelled' => 'fas fa-ban',
            'refunded' => 'fas fa-undo',
            'unpaid' => 'fas fa-minus-circle',
            default => 'fas fa-question-circle'
        };
    }
} 