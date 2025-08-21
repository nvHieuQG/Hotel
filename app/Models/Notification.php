<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'message',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    /**
     * Get the user that owns the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for unread notifications.
     */
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    /**
     * Scope for read notifications.
     */
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->update(['is_read' => true]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread()
    {
        $this->update(['is_read' => false]);
    }
}
