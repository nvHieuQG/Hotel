<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourBooking extends Model
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
        'tour_name',
        'total_guests',
        'total_rooms',
        'check_in_date',
        'check_out_date',
        'total_price',
        'status',
        'special_requests',
        'tour_details',
        'payment_status',
        'preferred_payment_method',
        'promotion_code',
        'promotion_discount',
        'final_price',
        'promotion_id',
        'need_vat_invoice',
        'company_name',
        'company_tax_code',
        'company_address',
        'company_email',
        'company_phone',
        'vat_invoice_number',
        'vat_invoice_created_at',
        'vat_invoice_file_path',
        'vat_invoice_status',
        'vat_invoice_generated_at',
        'vat_invoice_sent_at',
        'guest_identity_info'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'tour_details' => 'array',
        'guest_identity_info' => 'array',
        'vat_invoice_created_at' => 'datetime',
        'vat_invoice_generated_at' => 'datetime',
        'vat_invoice_sent_at' => 'datetime',
    ];

    /**
     * Get the user that owns the tour booking.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the tour booking rooms for this tour booking.
     */
    public function tourBookingRooms()
    {
        return $this->hasMany(TourBookingRoom::class);
    }

    /**
     * Get the tour booking services for this tour booking.
     */
    public function tourBookingServices()
    {
        return $this->hasMany(TourBookingService::class);
    }

    /**
     * Get the tour booking notes for this tour booking.
     */
    public function tourBookingNotes()
    {
        return $this->hasMany(TourBookingNote::class);
    }

    /**
     * Get the promotion applied to this tour booking.
     */
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get the payments for this tour booking.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'tour_booking_id');
    }

    /**
     * Check if tour booking has any payments.
     */
    public function hasPayments()
    {
        return $this->payments()->exists();
    }

    /**
     * Check if tour booking has successful payment.
     */
    public function hasSuccessfulPayment()
    {
        return $this->payments()->where('status', 'completed')->exists();
    }

    /**
     * Get total paid amount.
     */
    public function getTotalPaidAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Get total discount amount (tổng giảm giá từ promotion).
     */
    public function getTotalDiscountAttribute()
    {
        return $this->promotion_discount ?? 0;
    }

    /**
     * Get discount percentage (phần trăm giảm giá).
     */
    public function getDiscountPercentageAttribute()
    {
        if ($this->total_amount_before_discount > 0 && $this->promotion_discount > 0) {
            return round(($this->promotion_discount / $this->total_amount_before_discount) * 100, 1);
        }
        return 0;
    }

    /**
     * Check if has discount (kiểm tra có giảm giá không).
     */
    public function getHasDiscountAttribute()
    {
        return $this->promotion_discount > 0;
    }

    /**
     * Get total services amount (tổng tiền dịch vụ).
     */
    public function getTotalServicesAmountAttribute()
    {
        return $this->tourBookingServices->sum('total_price');
    }

    /**
     * Get total rooms amount (tổng tiền phòng).
     */
    public function getTotalRoomsAmountAttribute()
    {
        return $this->tourBookingRooms->sum('total_price');
    }

    /**
     * Get total amount before discount (tổng tiền trước giảm giá).
     */
    public function getTotalAmountBeforeDiscountAttribute()
    {
        return $this->total_rooms_amount + $this->total_services_amount;
    }

    /**
     * Get base room price (giá phòng cơ bản).
     */
    public function getBaseRoomPriceAttribute()
    {
        return $this->total_rooms_amount;
    }

    /**
     * Get total booking price (tổng giá trị booking bao gồm phòng và dịch vụ).
     */
    public function getTotalBookingPriceAttribute()
    {
        // Tính tổng thực tế = giá phòng + dịch vụ admin thêm
        return $this->total_rooms_amount + $this->total_services_amount;
    }

    /**
     * Get outstanding amount (số tiền còn lại).
     */
    public function getOutstandingAmountAttribute()
    {
        return $this->final_amount - $this->total_paid;
    }

    /**
     * Get final amount (giá cuối sau giảm giá).
     */
    public function getFinalAmountAttribute()
    {
        // Nếu có promotion_discount, tính lại giá cuối
        if ($this->promotion_discount > 0) {
            return $this->total_amount_before_discount - $this->promotion_discount;
        }
        
        // Nếu không có giảm giá, giá cuối = tổng tiền gốc
        return $this->total_amount_before_discount;
    }

    /**
     * Check if tour booking is fully paid.
     */
    public function isFullyPaid()
    {
        return $this->total_paid >= $this->final_amount;
    }

    /**
     * Get check-in date only (date part).
     */
    public function getCheckInDateOnlyAttribute()
    {
        return $this->check_in_date ? $this->check_in_date->format('Y-m-d') : null;
    }

    /**
     * Get check-out date only (date part).
     */
    public function getCheckOutDateOnlyAttribute()
    {
        return $this->check_out_date ? $this->check_out_date->format('Y-m-d') : null;
    }

    /**
     * Get status text for display.
     */
    public function getStatusTextAttribute()
    {
        $statusMap = [
            'pending' => 'Chờ xác nhận',
            'pending_payment' => 'Chờ thanh toán',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã check-in',
            'checked_out' => 'Đã check-out',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'no_show' => 'Không đến'
        ];

        return $statusMap[$this->status] ?? $this->status;
    }

    /**
     * Generate unique booking ID.
     */
    public static function generateBookingId()
    {
        do {
            $bookingId = 'TOUR' . date('Ymd') . strtoupper(substr(md5(uniqid()), 0, 6));
        } while (static::where('booking_id', $bookingId)->exists());

        return $bookingId;
    }

    /**
     * Calculate total nights.
     */
    public function getTotalNightsAttribute()
    {
        if (!$this->check_in_date || !$this->check_out_date) {
            return 0;
        }
        return $this->check_in_date->diffInDays($this->check_out_date);
    }

    /**
     * Check if VAT invoice is needed.
     */
    public function needsVatInvoice()
    {
        return $this->need_vat_invoice && $this->company_name && $this->company_tax_code;
    }

    /**
     * Check if tour booking can be checked in.
     */
    public function canCheckIn()
    {
        return in_array($this->status, ['confirmed', 'pending_payment']) && 
               $this->check_in_date && 
               $this->check_in_date->isToday();
    }

    /**
     * Check if tour booking can be checked out.
     */
    public function canCheckOut()
    {
        return in_array($this->status, ['checked_in']) && 
               $this->check_out_date && 
               $this->check_out_date->isToday();
    }

    /**
     * Check if tour booking can be cancelled.
     */
    public function canCancel()
    {
        return in_array($this->status, ['pending', 'pending_payment', 'confirmed']);
    }

    /**
     * Check if tour booking can be completed.
     */
    public function canComplete()
    {
        return in_array($this->status, ['checked_out']);
    }

    /**
     * Get VAT invoice status.
     */
    public function getVatInvoiceStatusAttribute()
    {
        if (!$this->need_vat_invoice) {
            return 'Không cần';
        }
        
        if ($this->vat_invoice_number) {
            return 'Đã xuất';
        }
        
        return 'Chờ xuất';
    }

    /**
     * Get payment status (trạng thái thanh toán).
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
     * Get payment status text (text hiển thị trạng thái thanh toán).
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
     * Get payment status icon (icon cho trạng thái thanh toán).
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

    /**
     * Get payment status badge class (class cho badge trạng thái thanh toán).
     */
    public function getPaymentStatusBadgeClassAttribute()
    {
        if ($this->isFullyPaid()) {
            return 'bg-success';
        }
        
        if ($this->total_paid > 0) {
            return 'bg-warning';
        }
        
        return 'bg-danger';
    }

    /**
     * Get status badge class (class cho badge trạng thái).
     */
    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'pending' => 'bg-warning',
            'pending_payment' => 'bg-info',
            'confirmed' => 'bg-success',
            'checked_in' => 'bg-primary',
            'checked_out' => 'bg-secondary',
            'completed' => 'bg-info',
            'cancelled' => 'bg-danger',
            'no_show' => 'bg-dark',
            default => 'bg-secondary'
        };
    }
}
