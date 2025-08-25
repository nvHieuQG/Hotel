<?php

namespace App\Http\Controllers;

use App\Services\SupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\SupportMessage;

class SupportController extends Controller
{
    protected $supportService;

    public function __construct(SupportService $supportService)
    {
        $this->supportService = $supportService;
    }

    public function index()
    {
        $userId = Auth::id();
        $conversations = $this->supportService->getAllConversations()
            ->filter(function($conversation) use ($userId) {
                return $conversation['user'] && $conversation['user']->id == $userId;
            });

        return view('support.index', compact('conversations'));
    }

    public function showConversation($conversationId)
    {
        $userId = Auth::id();
        $messages = $this->supportService->getConversationMessages($conversationId);

        // Kiểm tra quyền truy cập
        $firstMessage = $messages->first();
        if (!$firstMessage || $firstMessage->sender_id != $userId) {
            abort(403, 'Bạn không có quyền truy cập cuộc trò chuyện này');
        }

        // Đánh dấu tin nhắn đã đọc
        $this->supportService->markMessagesAsRead($conversationId, $userId);

        return view('support.show', compact('messages', 'conversationId'));
    }

    public function sendMessage(Request $request, $conversationId = null)
    {
        try {
            $request->validate([
                'message' => 'required_without:attachment|nullable|string|min:1|max:1000',
                'attachment' => 'nullable|file|max:5120|mimetypes:image/jpeg,image/png,application/pdf,application/zip,text/plain',
            ], [
                'message.required_without' => 'Vui lòng nhập tin nhắn hoặc chọn tệp đính kèm',
                'message.min' => 'Tin nhắn phải có ít nhất 1 ký tự',
                'message.max' => 'Tin nhắn không được quá 1000 ký tự',
                'attachment.file' => 'Tệp đính kèm không hợp lệ',
                'attachment.max' => 'Tệp đính kèm tối đa 5MB',
                'attachment.mimetypes' => 'Định dạng tệp không được hỗ trợ',
            ]);

            $messageText = trim((string)$request->input('message'));
            $attachmentMeta = null;

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $storedPath = $file->store('support_attachments', 'public');

                $attachmentMeta = [
                    'path' => $storedPath,
                    'name' => $file->getClientOriginalName(),
                    'type' => $file->getClientMimeType(),
                    'size' => $file->getSize(),
                ];
            }

            if(empty($messageText) && !$attachmentMeta) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vui lòng nhập tin nhắn hoặc chọn tệp đính kèm'
                    ], 400);
                }
                return redirect()->back()->withErrors(['message' => 'Vui lòng nhập tin nhắn hoặc chọn tệp đính kèm']);
            }

            $userId = Auth::id();
            Log::info("User {$userId} sending message: ".($messageText ?: '[attachment only]'));

            // Nếu không có conversation id, tạo conversation mới hoặc tìm conversation hiện có
            if (!$conversationId) {
                try {
                    $result = $this->supportService->createFirstMessage($userId, 'Hỗ trợ nhanh', $messageText, $attachmentMeta);
                    $conversationId = $result['conversation_id'];
                    $message = $result['message'];
                    Log::info("Created/found conversation {$conversationId} for user {$userId}");
                } catch (\Exception $e) {
                    Log::error('Error creating conversation with first message: ' . $e->getMessage());
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Không thể tạo cuộc trò chuyện. Vui lòng thử lại!'
                        ], 500);
                    }
                    return redirect()->back()->withErrors(['message' => 'Không thể tạo cuộc trò chuyện. Vui lòng thử lại!']);
                }
            } else {
                // Kiểm tra quyền truy cập conversation
                $firstMessage = SupportMessage::where('conversation_id', $conversationId)->first();
                if (!$firstMessage || $firstMessage->sender_id != $userId) {
                    Log::warning("User {$userId} tried to access conversation {$conversationId} without permission");
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Bạn không có quyền truy cập cuộc trò chuyện này!'
                        ], 403);
                    }
                    return redirect()->back()->withErrors(['message' => 'Bạn không có quyền truy cập cuộc trò chuyện này!']);
                }

                // Gửi tin nhắn mới
                try {
                    $message = $this->supportService->sendMessage(
                        $conversationId,
                        $userId,
                        'user',
                        $messageText,
                        null,
                        $attachmentMeta
                    );
                    Log::info("Message sent to conversation {$conversationId} by user {$userId}");
                } catch (\Exception $e) {
                    Log::error('Error sending support message: ' . $e->getMessage());
                    if ($request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Không thể gửi tin nhắn. Vui lòng thử lại!'
                        ], 500);
                    }
                    return redirect()->back()->withErrors(['message' => 'Không thể gửi tin nhắn. Vui lòng thử lại!']);
                }
            }

            // Nếu request yêu cầu JSON response (từ chat widget)
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'conversation_id' => $conversationId,
                    'message_id' => $message->id,
                    'message' => 'Tin nhắn đã được gửi',
                    'attachment' => $message->attachment_path ? [
                        'url' => asset('storage/'.$message->attachment_path),
                        'name' => $message->attachment_name,
                        'type' => $message->attachment_type,
                        'size' => $message->attachment_size,
                    ] : null,
                ]);
            }

            // Nếu không phải JSON request, redirect như cũ
            return redirect()->route('support.showConversation', $conversationId)->with('success', 'Đã gửi tin nhắn');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->errors()['message'][0] ?? 'Dữ liệu không hợp lệ'
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in sendMessage: ' . $e->getMessage());
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau!'
                ], 500);
            }
            return redirect()->back()->withErrors(['message' => 'Có lỗi xảy ra. Vui lòng thử lại sau!']);
        }
    }

    public function getNewMessages(Request $request, $conversationId)
    {
        try {
            $lastId = $request->get('last_id', 0);
            $userId = Auth::id();

            // Kiểm tra quyền truy cập
            $firstMessage = SupportMessage::where('conversation_id', $conversationId)->first();
            if (!$firstMessage || $firstMessage->sender_id != $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không có quyền truy cập'
                ], 403);
            }

            $messages = $this->supportService->getNewMessages($conversationId, $lastId);

            return response()->json([
                'success' => true,
                'messages' => $messages->map(function($msg) {
                    return [
                        'id' => $msg->id,
                        'message' => $msg->message,
                        'sender_type' => $msg->sender_type,
                        'created_at' => $msg->created_at,
                        'attachment' => $msg->attachment_path ? [
                            'url' => asset('storage/'.$msg->attachment_path),
                            'name' => $msg->attachment_name,
                            'type' => $msg->attachment_type,
                            'size' => $msg->attachment_size,
                        ] : null,
                    ];
                })
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting new messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra'
            ], 500);
        }
    }
}
