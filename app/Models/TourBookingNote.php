<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourBookingNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_booking_id',
        'user_id',
        'content',
        'type',
        'visibility',
        'is_internal'
    ];

    protected $casts = [
        'is_internal' => 'boolean',
    ];

    /**
     * Get the tour booking that owns the note.
     */
    public function tourBooking()
    {
        return $this->belongsTo(TourBooking::class);
    }

    /**
     * Get the user that created the note.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope để lấy ghi chú theo loại
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope để lấy ghi chú theo visibility
     */
    public function scopeOfVisibility($query, $visibility)
    {
        return $query->where('visibility', 'visibility');
    }

    /**
     * Scope để lấy ghi chú công khai
     */
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    /**
     * Scope để lấy ghi chú nội bộ
     */
    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    /**
     * Lấy tên hiển thị của loại ghi chú
     */
    public function getTypeTextAttribute()
    {
        return match($this->type) {
            'customer' => 'Khách hàng',
            'staff' => 'Nhân viên',
            'admin' => 'Quản lý',
            default => 'Không xác định'
        };
    }

    /**
     * Lấy tên hiển thị của visibility
     */
    public function getVisibilityTextAttribute()
    {
        return match($this->visibility) {
            'public' => 'Công khai',
            'internal' => 'Nội bộ',
            default => 'Không xác định'
        };
    }
}
