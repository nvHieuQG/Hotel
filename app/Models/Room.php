<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'room_type_id',
        'floor',
        'room_number',
        'status',
        'price',
        'capacity',
    ];

    /**
     * Lấy loại phòng
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Lấy tất cả booking của phòng
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }


    
    /**
     * Lấy hình ảnh của phòng
     */
    public function images()
    {
        return $this->hasMany(RoomImage::class);
    }

    /**
     * Lấy ảnh chính của phòng
     */
    public function primaryImage()
    {
        return $this->hasOne(RoomImage::class)->where('is_primary', true);
    }

    /**
     * Lấy ảnh đầu tiên của phòng (fallback)
     */
    public function firstImage()
    {
        return $this->hasOne(RoomImage::class)->orderBy('is_primary', 'desc')->orderBy('id', 'asc');
    }
    
    // /**
    //  * Lấy dịch vụ của phòng
    //  */
    // public function services()
    // {
    //     return $this->belongsToMany(Service::class, 'room_services');
    // }
    
    /**
     * Accessor để lấy tên phòng (kết hợp từ loại phòng và số phòng)
     */
    public function getNameAttribute()
    {
        return $this->roomType->name . ' - ' . $this->room_number;
    }
    
    /**
     * Accessor để lấy giá phòng từ loại phòng
     */
    public function getPriceAttribute($value)
    {
        return $value ?? $this->roomType->price;
    }
    
    /**
     * Accessor để lấy mô tả phòng từ loại phòng
     */
    public function getDescriptionAttribute()
    {
        return $this->roomType->description;
    }
    
    /**
     * Accessor để lấy sức chứa từ loại phòng
     */
    public function getCapacityAttribute($value)
    {
        return $value ?? $this->roomType->capacity;
    }

    
    /**
     * Accessor để lấy tên phòng đầy đủ (Tầng + Số phòng)
     */
    public function getFullNameAttribute()
    {
        return 'Tầng ' . $this->floor . ' - Phòng ' . $this->room_number;
    }

    /**
     * Accessor để lấy tên phòng ngắn gọn
     */
    public function getShortNameAttribute()
    {
        return $this->floor . $this->room_number;
    }

    /**
     * Scope để lọc theo tầng
     */
    public function scopeByFloor($query, $floor)
    {
        return $query->where('floor', $floor);
    }

    /**
     * Scope để lọc theo loại phòng
     */
    public function scopeByType($query, $roomTypeId)
    {
        return $query->where('room_type_id', $roomTypeId);
    }

    /**
     * Scope để lọc theo trạng thái
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Lấy rating trung bình của phòng (từ loại phòng)
     */
    public function getAverageRatingAttribute()
    {
        return $this->roomType->average_rating;
    }

    /**
     * Lấy số lượng reviews của phòng (từ loại phòng)
     */
    public function getReviewsCountAttribute()
    {
        return $this->roomType->reviews_count;
    }

    /**
     * Lấy số sao hiển thị (1-5)
     */
    public function getStarsAttribute()
    {
        return round($this->average_rating);
    }

    /**
     * Lấy phần trăm rating (cho hiển thị progress bar)
     */
    public function getRatingPercentageAttribute()
    {
        return ($this->average_rating / 5) * 100;
    }
    public function getStatusForDisplayAttribute()
    {
        $latestBooking = $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'pending_payment'])
            ->orderByDesc('created_at')
            ->first();

        if ($latestBooking) {
            if (in_array($latestBooking->status, ['pending', 'pending_payment'])) {
                // Nếu quá 30 phút thì bỏ qua
                if (now()->diffInMinutes($latestBooking->created_at) > 30) {
                    return 'available';
                }
                return 'pending';
            }
            if ($latestBooking->status === 'confirmed') {
                return 'booked';
            }
        }
        return $this->status; // fallback
    }

    public function getStatusForDate($date)
    {
        $day = \Carbon\Carbon::parse($date)->toDateString();

        // 1. Ưu tiên booking đã xác nhận (confirmed) trùng NGÀY
        $confirmed = $this->bookings
            ->where('status', 'confirmed')
            ->filter(function($booking) use ($day) {
                $inDate = \Carbon\Carbon::parse($booking->check_in_date)->toDateString();
                $outDate = \Carbon\Carbon::parse($booking->check_out_date)->toDateString();
                return $inDate <= $day && $outDate > $day; // inclusive start, exclusive end
            })
            ->first();

        if ($confirmed) {
            return 'booked';
        }

        // 2. Nếu không có, kiểm tra booking chờ xác nhận (pending, pending_payment) trùng NGÀY
        $pending = $this->bookings
            ->whereIn('status', ['pending', 'pending_payment'])
            ->filter(function($booking) use ($day) {
                $inDate = \Carbon\Carbon::parse($booking->check_in_date)->toDateString();
                $outDate = \Carbon\Carbon::parse($booking->check_out_date)->toDateString();
                return $inDate <= $day && $outDate > $day; // inclusive start, exclusive end
            })
            ->sortByDesc('created_at')
            ->first();

        if ($pending) {
            if ($pending->created_at->diffInMinutes(now()) > 30) {
                return 'available';
            }
            return 'pending';
        }

        // 3. Nếu không có booking nào, trả về trạng thái gốc
        return $this->status;
    }

} 