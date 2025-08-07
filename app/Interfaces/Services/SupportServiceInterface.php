<?php

namespace App\Interfaces\Services;

interface SupportServiceInterface
{
    public function sendMessage($conversationId, $senderId, $senderType, $message, $subject = null);

    public function getNewMessages($conversationId, $lastId);

    public function createFirstMessage($userId, $subject, $firstMessage);

    public function getAllConversations();

    public function getConversationMessages($conversationId);

    public function markMessagesAsRead($conversationId, $userId = null);

    public function getUnreadCount();
}
