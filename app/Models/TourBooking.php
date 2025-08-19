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
        'preferred_payment_method'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'tour_details' => 'array',
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
     * Get the payments for this tour booking.
     */
    public function payments()
    {
        return $this->hasMany(Payment::class, 'booking_id');
    }

    /**
     * Get the latest payment for this tour booking.
     */
    public function latestPayment()
    {
        return $this->hasOne(Payment::class, 'booking_id', 'booking_id')->latest();
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
     * Check if tour booking is fully paid.
     */
    public function isFullyPaid()
    {
        return $this->total_paid >= $this->total_price;
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
            'confirmed' => 'Đã xác nhận',
            'cancelled' => 'Đã hủy',
            'completed' => 'Hoàn thành'
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
}
