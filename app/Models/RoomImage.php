<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        $url = $this->image_url ?? '';

        // Trường hợp URL tuyệt đối
        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        // Trường hợp đã là đường dẫn trong public (ví dụ: client/images/room-1.jpg)
        if (Str::startsWith($url, ['client/'])) {
            return asset($url);
        }

        // Nếu đã có prefix storage/ thì giữ nguyên
        if (Str::startsWith($url, ['storage/'])) {
            return asset($url);
        }

        // Nếu bị lưu kèm 'public/' ở đầu, bỏ đi vì asset() đã trỏ vào public
        if (Str::startsWith($url, ['public/'])) {
            return asset(Str::after($url, 'public/'));
        }

        // Mặc định: coi như file được lưu ở storage/app/public
        return asset('storage/' . ltrim($url, '/'));
    }
}