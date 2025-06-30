<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
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
        'rating',
        'comment',
        'status',
        'is_anonymous'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rating' => 'integer',
        'is_anonymous' => 'boolean',
    ];

    /**
     * Get the user that wrote the review.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the booking associated with the review.
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Get the room that was reviewed.
     */
    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Scope để lấy reviews đã được duyệt
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope để lấy reviews theo phòng
     */
    public function scopeForRoom($query, $roomId)
    {
        return $query->where('room_id', $roomId);
    }

    /**
     * Lấy tên hiển thị của người đánh giá
     */
    public function getReviewerNameAttribute()
    {
        if ($this->is_anonymous) {
            return 'Khách hàng ẩn danh';
        }
        
        return $this->user->name;
    }

    /**
     * Lấy trạng thái hiển thị
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            default => 'Không xác định'
        };
    }

    /**
     * Kiểm tra xem review có thể được chỉnh sửa không
     */
    public function canBeEdited()
    {
        return $this->status === 'pending';
    }

    /**
     * Kiểm tra xem review có thể được xóa không
     */
    public function canBeDeleted()
    {
        return $this->status === 'pending';
    }
} 