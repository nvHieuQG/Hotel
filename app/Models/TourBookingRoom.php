<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourBookingRoom extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tour_booking_id',
        'room_type_id',
        'quantity',
        'guests_per_room',
        'price_per_room',
        'total_price',
        'guest_details',
        'assigned_room_ids'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'guest_details' => 'array',
        'assigned_room_ids' => 'array',
    ];

    /**
     * Get the tour booking that owns this tour booking room.
     */
    public function tourBooking()
    {
        return $this->belongsTo(TourBooking::class);
    }

    /**
     * Get the room type for this tour booking room.
     */
    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }

    /**
     * Get the assigned rooms for this tour booking room.
     */
    public function getAssignedRoomsAttribute()
    {
        if (empty($this->assigned_room_ids)) {
            return collect();
        }
        
        return Room::with('roomType')->whereIn('id', $this->assigned_room_ids)->get();
    }

    /**
     * Get total guests for this room type.
     */
    public function getTotalGuestsAttribute()
    {
        return $this->quantity * $this->guests_per_room;
    }

    /**
     * Get formatted price per room.
     */
    public function getFormattedPricePerRoomAttribute()
    {
        return number_format($this->price_per_room, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Get formatted total price.
     */
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 0, ',', '.') . ' VNĐ';
    }
}
