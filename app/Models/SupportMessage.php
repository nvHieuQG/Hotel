<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id', 'sender_type', 'subject', 'conversation_id', 'message', 'is_read',
        'attachment_path', 'attachment_name', 'attachment_type', 'attachment_size'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // Relationship với User
    public function user()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    // Scope để lấy tin nhắn theo conversation
    public function scopeByConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    // Relationship với conversation (virtual)
    public function conversation()
    {
        return $this->hasOne(SupportMessage::class, 'conversation_id', 'conversation_id')
            ->where('sender_type', 'user')
            ->orderBy('created_at', 'asc')
            ->limit(1);
    }

    // Scope để lấy tin nhắn từ user
    public function scopeFromUser($query, $userId)
    {
        return $query->where('sender_id', $userId)->where('sender_type', 'user');
    }

    // Scope để lấy tin nhắn từ admin
    public function scopeFromAdmin($query)
    {
        return $query->where('sender_type', 'admin');
    }

    // Scope để lấy tin nhắn chưa đọc
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Tạo conversation ID mới
    public static function generateConversationId()
    {
        return 'conv_' . time() . '_' . rand(1000, 9999);
    }
}
