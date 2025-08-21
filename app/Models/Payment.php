<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'tour_booking_id',
        'promotion_id',
        'method',
        'amount',
        'discount_amount',
        'promotion_code',
        'currency',
        'status',
        'transaction_id',
        'gateway_response',
        'gateway_code',
        'gateway_message',
        'paid_at',
        'gateway_name'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the booking that owns the payment.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Get the tour booking that owns the payment.
     */
    public function tourBooking()
    {
        return $this->belongsTo(TourBooking::class, 'tour_booking_id');
    }

    /**
     * Get the related booking (either regular booking or tour booking)
     */
    public function getRelatedBookingAttribute()
    {
        // Try to get regular booking first
        $booking = $this->booking;
        if ($booking) {
            return $booking;
        }
        
        // If not found, try to get tour booking
        return $this->tourBooking;
    }

    /**
     * Get the status text for display
     */
    public function getStatusTextAttribute()
    {
        return match ($this->status) {
            'pending' => 'Chờ thanh toán',
            'processing' => 'Đang xử lý',
            'completed' => 'Thanh toán thành công',
            'failed' => 'Thanh toán thất bại',
            'cancelled' => 'Đã hủy',
            'refunded' => 'Đã hoàn tiền',
            default => 'Không xác định'
        };
    }

    /**
     * Get the status color for display
     */
    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            'refunded' => 'info',
            default => 'secondary'
        };
    }

    /**
     * Check if payment is successful
     */
    public function isSuccessful()
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment is pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Check if payment is failed
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Format amount for display
     */
    public function getFormattedAmountAttribute()
    {
        return number_format((float) $this->amount, 0, ',', '.') . ' VND';
    }

    public function getFormattedDiscountAttribute()
    {
        return number_format((float) $this->discount_amount, 0, ',', '.') . ' VND';
    }
}
