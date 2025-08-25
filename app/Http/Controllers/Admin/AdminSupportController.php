<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SupportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\SupportMessage;

class AdminSupportController extends Controller
{
    protected $supportService;

    public function __construct(SupportService $supportService)
    {
        $this->supportService = $supportService;
    }

    public function index()
    {
        $conversations = $this->supportService->getAllConversations();
        return view('admin.support.index', compact('conversations'));
    }

    public function showConversation($conversationId)
    {
        $messages = $this->supportService->getConversationMessages($conversationId);

        if ($messages->isEmpty()) {
            abort(404, 'Không tìm thấy cuộc trò chuyện');
        }

        // Đánh dấu tin nhắn đã đọc
        $this->supportService->markMessagesAsRead($conversationId);

        // Lấy thông tin conversation
        $firstMessage = $messages->first();
        $conversation = [
            'id' => $conversationId,
            'subject' => $firstMessage->subject,
            'user' => $firstMessage->user,
            'created_at' => $firstMessage->created_at
        ];

        return view('admin.support.show', compact('messages', 'conversation'));
    }

    public function sendMessage(Request $request, $conversationId)
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

            if (empty($messageText) && !$attachmentMeta) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Vui lòng nhập tin nhắn hoặc chọn tệp đính kèm'
                    ], 400);
                }
                return redirect()->back()->withErrors(['message' => 'Vui lòng nhập tin nhắn hoặc chọn tệp đính kèm']);
            }

            // Kiểm tra conversation tồn tại
            $firstMessage = SupportMessage::where('conversation_id', $conversationId)->first();
            if (!$firstMessage) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cuộc trò chuyện không tồn tại!'
                    ], 404);
                }
                return redirect()->back()->withErrors(['message' => 'Cuộc trò chuyện không tồn tại!']);
            }

            // Chống gửi trùng trong khoảng thời gian ngắn
            $recentExisting = SupportMessage::where('conversation_id', $conversationId)
                ->where('sender_id', Auth::id())
                ->where('sender_type', 'admin')
                ->when($messageText, function($q) use ($messageText) { return $q->where('message', $messageText); })
                ->where('created_at', '>=', now()->subSeconds(5))
                ->orderByDesc('id')
                ->first();

            if ($recentExisting) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => true,
                        'message_id' => $recentExisting->id,
                        'message' => 'Tin nhắn đã tồn tại (gửi gần đây)'
                    ]);
                }
                return redirect()->route('admin.support.showConversation', $conversationId)->with('success', 'Đã gửi tin nhắn');
            }

            // Gửi tin nhắn từ admin
            try {
                $message = $this->supportService->sendMessage(
                    $conversationId,
                    Auth::id(),
                    'admin',
                    $messageText,
                    null,
                    $attachmentMeta
                );
            } catch (\Exception $e) {
                Log::error('Error sending admin support message: ' . $e->getMessage());
                if ($request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể gửi tin nhắn. Vui lòng thử lại!'
                    ], 500);
                }
                return redirect()->back()->withErrors(['message' => 'Không thể gửi tin nhắn. Vui lòng thử lại!']);
            }

            // Nếu request yêu cầu JSON response
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
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

            // Redirect như cũ
            return redirect()->route('admin.support.showConversation', $conversationId)->with('success', 'Đã gửi tin nhắn');

        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->errors()['message'][0] ?? 'Dữ liệu không hợp lệ'
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            Log::error('Unexpected error in admin sendMessage: ' . $e->getMessage());
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

            // Kiểm tra conversation tồn tại
            $firstMessage = SupportMessage::where('conversation_id', $conversationId)->first();
            if (!$firstMessage) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy cuộc trò chuyện'
                ], 404);
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
                        'user' => $msg->user ? [
                            'name' => $msg->user->name,
                            'email' => $msg->user->email
                        ] : null
                    ];
                })
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting new messages: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy tin nhắn mới'
            ], 500);
        }
    }

    public function getUpdates(Request $request)
    {
        try {
            $lastUpdate = $request->input('last_update', 0);
            $lastUpdateTime = date('Y-m-d H:i:s', $lastUpdate / 1000);

            // Lấy thống kê hiện tại
            $stats = [
                'unread_count' => SupportMessage::where('sender_type', 'user')->where('is_read', false)->count(),
                'total_count' => SupportMessage::select('conversation_id')->distinct()->count()
            ];

            // Lấy các conversation đã cập nhật
            $updatedConversations = SupportMessage::select('conversation_id')
                ->where('updated_at', '>', $lastUpdateTime)
                ->distinct()
                ->get()
                ->pluck('conversation_id');

            $updates = [];
            foreach ($updatedConversations as $conversationId) {
                $lastMessage = SupportMessage::where('conversation_id', $conversationId)
                    ->with('user')
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($lastMessage) {
                    $unreadCount = SupportMessage::where('conversation_id', $conversationId)
                        ->where('sender_type', 'user')
                        ->where('is_read', false)
                        ->count();

                    $updates[] = [
                        'conversation_id' => $conversationId,
                        'unread_count' => $unreadCount,
                        'last_message' => $lastMessage->message,
                        'updated_at' => $lastMessage->updated_at,
                        'user' => $lastMessage->user ? [
                            'name' => $lastMessage->user->name,
                            'email' => $lastMessage->user->email
                        ] : null
                    ];
                }
            }

            // Lấy các conversation mới
            $newConversations = SupportMessage::select('conversation_id')
                ->where('created_at', '>', $lastUpdateTime)
                ->distinct()
                ->get()
                ->pluck('conversation_id');

            $newConversationsData = [];
            foreach ($newConversations as $conversationId) {
                $firstMessage = SupportMessage::where('conversation_id', $conversationId)
                    ->with('user')
                    ->orderBy('created_at', 'asc')
                    ->first();

                if ($firstMessage) {
                    $unreadCount = SupportMessage::where('conversation_id', $conversationId)
                        ->where('sender_type', 'user')
                        ->where('is_read', false)
                        ->count();

                    $newConversationsData[] = [
                        'conversation_id' => $conversationId,
                        'subject' => $firstMessage->subject,
                        'unread_count' => $unreadCount,
                        'last_message' => $firstMessage->message,
                        'created_at' => $firstMessage->created_at,
                        'user' => $firstMessage->user ? [
                            'name' => $firstMessage->user->name,
                            'email' => $firstMessage->user->email
                        ] : null
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'updates' => $updates,
                'new_conversations' => $newConversationsData
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting updates: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy cập nhật'
            ], 500);
        }
    }
}
