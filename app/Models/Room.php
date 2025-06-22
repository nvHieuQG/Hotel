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
    public function getPriceAttribute()
    {
        return $this->roomType->price;
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
    public function getCapacityAttribute()
    {
        return $this->roomType->capacity;
    }
} 