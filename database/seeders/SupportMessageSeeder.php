<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupportMessage;
use App\Models\User;

class SupportMessageSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy user đầu tiên để tạo conversations
        $user = User::first();

        if (!$user) {
            $this->command->info('Không có user nào để tạo support messages!');
            return;
        }

        // Tạo các conversations mẫu
        $conversations = [
            [
                'subject' => 'Hỏi về đặt phòng',
                'messages' => [
                    ['message' => 'Xin chào, tôi cần hỗ trợ đặt phòng', 'sender_type' => 'user', 'is_read' => false],
                    ['message' => 'Chào bạn, chúng tôi có thể giúp gì cho bạn?', 'sender_type' => 'admin', 'is_read' => true],
                    ['message' => 'Tôi muốn đặt phòng từ ngày mai', 'sender_type' => 'user', 'is_read' => false],
                ]
            ],
            [
                'subject' => 'Vấn đề về thanh toán',
                'messages' => [
                    ['message' => 'Tôi đã thanh toán nhưng chưa nhận được xác nhận', 'sender_type' => 'user', 'is_read' => false],
                    ['message' => 'Tôi đã thanh toán qua thẻ tín dụng', 'sender_type' => 'user', 'is_read' => false],
                ]
            ],
            [
                'subject' => 'Hủy đặt phòng',
                'messages' => [
                    ['message' => 'Tôi muốn hủy đặt phòng đã đặt', 'sender_type' => 'user', 'is_read' => true],
                    ['message' => 'Chúng tôi đã hủy đặt phòng cho bạn', 'sender_type' => 'admin', 'is_read' => true],
                    ['message' => 'Cảm ơn bạn', 'sender_type' => 'user', 'is_read' => true],
                ]
            ],
            [
                'subject' => 'Hỏi về dịch vụ spa',
                'messages' => [
                    ['message' => 'Khách sạn có dịch vụ spa không?', 'sender_type' => 'user', 'is_read' => true],
                    ['message' => 'Có, chúng tôi có spa rất tốt', 'sender_type' => 'admin', 'is_read' => true],
                    ['message' => 'Giá cả như thế nào?', 'sender_type' => 'user', 'is_read' => false],
                ]
            ],
            [
                'subject' => 'Đổi ngày check-in',
                'messages' => [
                    ['message' => 'Tôi muốn đổi ngày check-in từ ngày mai sang tuần sau', 'sender_type' => 'user', 'is_read' => false],
                ]
            ],
        ];

        foreach ($conversations as $conversationData) {
            $conversationId = SupportMessage::generateConversationId();

            foreach ($conversationData['messages'] as $messageData) {
                SupportMessage::create([
                    'sender_id' => $messageData['sender_type'] === 'user' ? $user->id : 1, // Admin ID = 1
                    'sender_type' => $messageData['sender_type'],
                    'subject' => $conversationData['subject'],
                    'conversation_id' => $conversationId,
                    'message' => $messageData['message'],
                    'is_read' => $messageData['is_read'],
                ]);
            }
        }

        $this->command->info('Đã tạo ' . count($conversations) . ' conversations mẫu!');
    }
}
