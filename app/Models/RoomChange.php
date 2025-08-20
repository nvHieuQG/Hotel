<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomChange extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'old_room_id',
        'new_room_id',
        'reason',
        'status',
        'price_difference',
        'requested_by',
        'approved_by',
        'admin_note',
        'customer_note',
        'approved_at',
        'completed_at',
        'payment_status',
        'paid_at',
        'paid_by',
    ];

    protected $casts = [
        'price_difference' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    // Relationships
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function oldRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'old_room_id');
    }

    public function newRoom(): BelongsTo
    {
        return $this->belongsTo(Room::class, 'new_room_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function requiresPayment(): bool
    {
        return $this->price_difference > 0;
    }

    public function isPaymentPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isRefundPending(): bool
    {
        return $this->payment_status === 'refund_pending';
    }

    public function isRefunded(): bool
    {
        return $this->payment_status === 'refunded';
    }

    public function isPaymentNotRequired(): bool
    {
        return $this->payment_status === 'not_required';
    }

    public function isPaidAtReception(): bool
    {
        return $this->payment_status === 'paid_at_reception';
    }

    public function getStatusText(): string
    {
        return match($this->status) {
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            default => 'Không xác định'
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'rejected' => 'danger',
            'completed' => 'success',
            'cancelled' => 'secondary',
            default => 'light'
        };
    }

    public function getPaymentStatusText(): string
    {
        return match($this->payment_status) {
            'not_required' => 'Không cần thanh toán',
            'pending' => 'Chờ thanh toán tại quầy',
            'refund_pending' => 'Chờ hoàn tiền tại quầy',
            'paid_at_reception' => 'Đã thanh toán tại quầy',
            'refunded' => 'Đã hoàn tiền tại quầy',
            default => 'Không xác định'
        };
    }

    public function getPaymentStatusColor(): string
    {
        return match($this->payment_status) {
            'not_required' => 'secondary',
            'pending' => 'warning',
            'refund_pending' => 'info',
            'paid_at_reception' => 'success',
            'refunded' => 'success',
            default => 'light'
        };
    }
}
