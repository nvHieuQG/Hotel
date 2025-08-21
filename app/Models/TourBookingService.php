<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourBookingService extends Model
{
    use HasFactory;

    protected $fillable = [
        'tour_booking_id',
        'service_type',
        'service_name',
        'unit_price',
        'quantity',
        'total_price',
        'notes',
        'status'
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'quantity' => 'integer',
    ];

    /**
     * Get the tour booking that owns the service.
     */
    public function tourBooking()
    {
        return $this->belongsTo(TourBooking::class);
    }

    /**
     * Get the formatted unit price.
     */
    public function getFormattedUnitPriceAttribute()
    {
        return number_format($this->unit_price, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Get the formatted total price.
     */
    public function getFormattedTotalPriceAttribute()
    {
        return number_format($this->total_price, 0, ',', '.') . ' VNĐ';
    }

    /**
     * Get the service type text.
     */
    public function getServiceTypeTextAttribute()
    {
        return match($this->service_type) {
            'transport' => 'Vận chuyển',
            'guide' => 'Hướng dẫn viên',
            'meal' => 'Bữa ăn',
            'entertainment' => 'Giải trí',
            'other' => 'Khác',
            default => $this->service_type
        };
    }
}
