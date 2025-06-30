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
     * Lấy tất cả reviews của phòng
     */
    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Lấy reviews đã được duyệt của phòng
     */
    public function approvedReviews()
    {
        return $this->reviews()->approved();
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
     * Lấy rating trung bình của phòng
     */
    public function getAverageRatingAttribute()
    {
        $rating = $this->approvedReviews()->avg('rating');
        return $rating ? round($rating, 1) : 0;
    }

    /**
     * Lấy số lượng reviews của phòng
     */
    public function getReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
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
} 