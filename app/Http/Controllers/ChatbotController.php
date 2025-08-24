<?php

namespace App\Http\Controllers;

use App\Services\GeminiChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ChatbotController extends Controller
{
    protected $chatbotService;

    public function __construct(GeminiChatbotService $chatbotService)
    {
        $this->chatbotService = $chatbotService;
    }

    /**
     * Hiển thị trang chatbot
     */
    public function index()
    {
        return view('client.chatbot.index');
    }

    /**
     * Xử lý tin nhắn từ user và trả về phản hồi từ AI
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $userMessage = $request->input('message');
        
        // Lấy lịch sử hội thoại từ session
        $conversationHistory = Session::get('chat_history', []);
        
        // Thêm tin nhắn của user vào lịch sử
        $conversationHistory[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => now()
        ];

        try {
            // Lấy phản hồi từ chatbot
            $botResponse = $this->chatbotService->generateResponse($userMessage, $conversationHistory);
            
            // Thêm phản hồi của bot vào lịch sử
            $conversationHistory[] = [
                'role' => 'assistant',
                'content' => $botResponse,
                'timestamp' => now()
            ];

            // Lưu lịch sử vào session (giữ tối đa 20 tin nhắn)
            if (count($conversationHistory) > 20) {
                $conversationHistory = array_slice($conversationHistory, -20);
            }
            Session::put('chat_history', $conversationHistory);

            return response()->json([
                'success' => true,
                'reply' => $botResponse,
                'timestamp' => now()->format('H:i'),
                'conversation_history' => $conversationHistory
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'reply' => 'Xin lỗi, tôi gặp vấn đề kỹ thuật. Vui lòng thử lại sau.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy lịch sử chat
     */
    public function getChatHistory()
    {
        $conversationHistory = Session::get('chat_history', []);
        
        return response()->json([
            'success' => true,
            'history' => $conversationHistory
        ]);
    }

    /**
     * Xóa lịch sử chat
     */
    public function clearChatHistory()
    {
        Session::forget('chat_history');
        
        return response()->json([
            'success' => true,
            'message' => 'Đã xóa lịch sử chat'
        ]);
    }
}
