<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'capacity'
    ];

    /**
     * Get the rooms for the room type.
     */
    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    /**
     * Get the reviews for the room type.
     */
    public function reviews()
    {
        return $this->hasMany(RoomTypeReview::class);
    }

    /**
     * Get the approved reviews for the room type.
     */
    public function approvedReviews()
    {
        return $this->hasMany(RoomTypeReview::class)->where('status', 'approved');
    }

    /**
     * Get the average rating for the room type.
     */
    public function getAverageRatingAttribute()
    {
        return $this->approvedReviews()->avg('rating') ?? 0;
    }

    /**
     * Get the reviews count for the room type.
     */
    public function getReviewsCountAttribute()
    {
        return $this->approvedReviews()->count();
    }

    /**
     * Get the services for the room type.
     */
    public function services()
    {
        return $this->belongsToMany(Service::class, 'room_type_services');
    }

    /**
     * Get the promotions that can be applied to this room type.
     */
    public function promotions()
    {
        return $this->belongsToMany(Promotion::class, 'promotion_room_type', 'room_type_id', 'promotion_id')
                    ->withTimestamps();
    }

    /**
     * Get active promotions for this room type.
     */
    public function activePromotions()
    {
        return $this->promotions()
                    ->where('is_active', true)
                    ->where(function($query) {
                        $query->whereNull('valid_from')
                              ->orWhere('valid_from', '<=', now());
                    })
                    ->where('expired_at', '>', now())
                    ->where(function($query) {
                        $query->whereNull('usage_limit')
                              ->orWhereRaw('used_count < usage_limit');
                    });
    }
} 