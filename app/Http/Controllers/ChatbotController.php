<?php

namespace App\Http\Controllers;

use App\Services\GeminiChatbotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        // Kiểm tra authentication
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Vui lòng đăng nhập để sử dụng chatbot');
        }

        return view('client.chatbot.index');
    }

    /**
     * Xử lý tin nhắn từ user và trả về phản hồi từ AI
     */
    public function sendMessage(Request $request)
    {
        // Kiểm tra authentication
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized',
                'message' => 'Vui lòng đăng nhập để sử dụng chatbot'
            ], 401);
        }

        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $userMessage = $request->input('message');
        $userId = Auth::id();
        
        // Lấy lịch sử hội thoại từ session
        $sessionKey = "chat_history_{$userId}";
        $conversationHistory = Session::get($sessionKey, []);
        
        // Thêm tin nhắn của user vào lịch sử
        $conversationHistory[] = [
            'role' => 'user',
            'content' => $userMessage,
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'user_id' => $userId
        ];

        try {
            // Giới hạn conversation history để tránh lỗi token limit
            $limitedHistory = array_slice($conversationHistory, -5); // Chỉ giữ 5 tin nhắn gần nhất
            
            // Lấy phản hồi từ chatbot
            $botResponse = $this->chatbotService->generateResponse($userMessage, $limitedHistory);
            
            // Thêm phản hồi của bot vào lịch sử
            $conversationHistory[] = [
                'role' => 'assistant',
                'content' => $botResponse,
                'timestamp' => now()->format('Y-m-d H:i:s'),
                'user_id' => $userId
            ];

            // Lưu lịch sử vào session (giữ tối đa 10 tin nhắn thay vì 20)
            if (count($conversationHistory) > 10) {
                $conversationHistory = array_slice($conversationHistory, -10);
            }
            Session::put($sessionKey, $conversationHistory);

            // Log để debug
            Log::info('Chat message processed', [
                'user_id' => $userId,
                'message_length' => strlen($userMessage),
                'response_length' => strlen($botResponse),
                'history_count' => count($conversationHistory)
            ]);

            return response()->json([
                'success' => true,
                'reply' => $botResponse,
                'timestamp' => now()->format('H:i'),
                'conversation_history' => $conversationHistory
            ]);

        } catch (\Exception $e) {
            Log::error('Error in sendMessage', [
                'user_id' => $userId ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

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
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized'
            ], 401);
        }

        $userId = Auth::id();
        $sessionKey = "chat_history_{$userId}";
        $conversationHistory = Session::get($sessionKey, []);
        
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
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized'
            ], 401);
        }

        $userId = Auth::id();
        $sessionKey = "chat_history_{$userId}";
        Session::forget($sessionKey);
        
        return response()->json([
            'success' => true,
            'message' => 'Đã xóa lịch sử chat'
        ]);
    }

    /**
     * Lưu chat history từ localStorage vào session
     */
    public function saveToSession(Request $request)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized'
            ], 401);
        }

        $request->validate([
            'history' => 'required|array'
        ]);

        $userId = Auth::id();
        $sessionKey = "chat_history_{$userId}";
        $history = $request->input('history');

        // Validate và format history
        $formattedHistory = [];
        foreach ($history as $message) {
            if (isset($message['role']) && isset($message['content'])) {
                $formattedHistory[] = [
                    'role' => $message['role'],
                    'content' => $message['content'],
                    'timestamp' => $message['timestamp'] ?? now()->format('Y-m-d H:i:s'),
                    'user_id' => $userId
                ];
            }
        }

        // Giữ tối đa 20 tin nhắn
        if (count($formattedHistory) > 20) {
            $formattedHistory = array_slice($formattedHistory, -20);
        }

        Session::put($sessionKey, $formattedHistory);

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu lịch sử chat',
            'history_count' => count($formattedHistory)
        ]);
    }

    /**
     * Lấy thông tin user hiện tại
     */
    public function getUserInfo()
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'error' => 'unauthorized'
            ], 401);
        }

        $user = Auth::user();
        
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email
            ]
        ]);
    }


}
