<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_id',
        'image_url',
        'is_primary'
    ];

    protected $casts = [
        'is_primary' => 'boolean'
    ];

    /**
     * Lấy phòng mà ảnh này thuộc về
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Accessor để lấy URL đầy đủ của ảnh
     */
    public function getFullImageUrlAttribute()
    {
        return asset('storage/' . $this->image_url);
    }
}