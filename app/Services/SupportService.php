<?php

namespace App\Services;

use App\Interfaces\Services\SupportServiceInterface;
use App\Models\SupportMessage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupportService implements SupportServiceInterface
{
    public function sendMessage($conversationId, $senderId, $senderType, $message, $subject = null, array $attachment = null)
    {
        $payload = [
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'subject' => $subject,
            'conversation_id' => $conversationId,
            'message' => $message,
            'is_read' => $senderType === 'admin' ? true : false,
        ];

        if ($attachment) {
            $payload['attachment_path'] = $attachment['path'] ?? null;
            $payload['attachment_name'] = $attachment['name'] ?? null;
            $payload['attachment_type'] = $attachment['type'] ?? null;
            $payload['attachment_size'] = $attachment['size'] ?? null;
        }

        return SupportMessage::create($payload);
    }

    public function getNewMessages($conversationId, $lastId)
    {
        return SupportMessage::where('conversation_id', $conversationId)
            ->where('id', '>', $lastId)
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }

    public function createFirstMessage($userId, $subject, $firstMessage, array $attachment = null)
    {
        // Kiểm tra xem user đã có conversation chưa
        $existingConversation = SupportMessage::where('sender_id', $userId)
            ->where('sender_type', 'user')
            ->orderBy('created_at', 'desc')
            ->first();

        if ($existingConversation) {
            // Nếu user đã có conversation, sử dụng conversation đó
            $conversationId = $existingConversation->conversation_id;

            // Gửi tin nhắn mới vào conversation hiện có
            $payload = [
                'sender_id' => $userId,
                'sender_type' => 'user',
                'subject' => $subject,
                'conversation_id' => $conversationId,
                'message' => $firstMessage,
                'is_read' => false,
            ];

            if ($attachment) {
                $payload['attachment_path'] = $attachment['path'] ?? null;
                $payload['attachment_name'] = $attachment['name'] ?? null;
                $payload['attachment_type'] = $attachment['type'] ?? null;
                $payload['attachment_size'] = $attachment['size'] ?? null;
            }

            $message = SupportMessage::create($payload);

            return [
                'conversation_id' => $conversationId,
                'message' => $message
            ];
        }

        // Nếu user chưa có conversation, tạo mới
        $conversationId = SupportMessage::generateConversationId();

        return DB::transaction(function () use ($userId, $subject, $firstMessage, $conversationId, $attachment) {
            $payload = [
                'sender_id' => $userId,
                'sender_type' => 'user',
                'subject' => $subject,
                'conversation_id' => $conversationId,
                'message' => $firstMessage,
                'is_read' => false,
            ];

            if ($attachment) {
                $payload['attachment_path'] = $attachment['path'] ?? null;
                $payload['attachment_name'] = $attachment['name'] ?? null;
                $payload['attachment_type'] = $attachment['type'] ?? null;
                $payload['attachment_size'] = $attachment['size'] ?? null;
            }

            $message = SupportMessage::create($payload);

            return [
                'conversation_id' => $conversationId,
                'message' => $message
            ];
        });
    }

    public function getAllConversations()
    {
        return SupportMessage::select('conversation_id', 'subject', 'sender_id', 'sender_type', 'message', 'created_at')
            ->with('user')
            ->whereIn('id', function($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('support_messages')
                    ->groupBy('conversation_id');
            })
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('conversation_id')
            ->map(function($messages) {
                $lastMessage = $messages->first();
                $user = $lastMessage->user;

                return [
                    'conversation_id' => $lastMessage->conversation_id,
                    'subject' => $lastMessage->subject,
                    'user' => $user,
                    'last_message' => $lastMessage->message,
                    'created_at' => $lastMessage->created_at,
                    'unread_count' => SupportMessage::where('conversation_id', $lastMessage->conversation_id)
                        ->where('sender_type', 'user')
                        ->where('is_read', false)
                        ->count()
                ];
            });
    }

    public function getConversationMessages($conversationId)
    {
        return SupportMessage::where('conversation_id', $conversationId)
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }

    public function markMessagesAsRead($conversationId, $userId = null)
    {
        $query = SupportMessage::where('conversation_id', $conversationId)
            ->where('sender_type', 'user')
            ->where('is_read', false);

        if ($userId) {
            $query->where('sender_id', $userId);
        }

        return $query->update(['is_read' => true]);
    }

    public function getUnreadCount()
    {
        return SupportMessage::where('sender_type', 'user')
            ->where('is_read', false)
            ->count();
    }

    public function getUserConversation($userId)
    {
        return SupportMessage::where('sender_id', $userId)
            ->where('sender_type', 'user')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getUserConversationMessages($userId)
    {
        $conversation = $this->getUserConversation($userId);
        if (!$conversation) {
            return collect();
        }

        return SupportMessage::where('conversation_id', $conversation->conversation_id)
            ->with('user')
            ->orderBy('created_at')
            ->get();
    }
}
