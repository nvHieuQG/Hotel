<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourRoomChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_booking_id',
        'from_room_id',
        'to_room_id',
        'suggested_to_room_id',
        'price_difference',
        'status',
        'reason',
        'customer_note',
        'admin_note',
        'requested_by',
        'approved_by',
        'approved_at',
        'completed_at',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tourBooking()
    {
        return $this->belongsTo(TourBooking::class);
    }

    public function fromRoom()
    {
        return $this->belongsTo(Room::class, 'from_room_id');
    }

    public function toRoom()
    {
        return $this->belongsTo(Room::class, 'to_room_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}


