<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminNotificationService;
use App\Services\NotificationDataFormatterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminNotificationController extends Controller
{
    protected $notificationService;
    protected $dataFormatterService;

    public function __construct(
        AdminNotificationService $notificationService,
        NotificationDataFormatterService $dataFormatterService
    ) {
        $this->notificationService = $notificationService;
        $this->dataFormatterService = $dataFormatterService;
    }

    /**
     * Hiển thị trang quản lý thông báo
     */
    public function index(Request $request)
    {
        $type = $request->get('type');
        $priority = $request->get('priority');
        $isRead = $request->get('is_read');

        $query = \App\Models\AdminNotification::query();

        if ($type) {
            $query->ofType($type);
        }

        if ($priority) {
            $query->ofPriority($priority);
        }

        if ($isRead !== null) {
            if ($isRead) {
                $query->read();
            } else {
                $query->unread();
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        $stats = [
            'total' => \App\Models\AdminNotification::count(),
            'unread' => $this->notificationService->getUnreadCount(),
            'by_priority' => $this->notificationService->getUnreadCountByPriority(),
        ];

        return view('admin.notifications.index', compact('notifications', 'stats', 'type', 'priority', 'isRead'));
    }

    /**
     * API: Lấy danh sách thông báo chưa đọc (cho AJAX)
     */
    public function getUnreadNotifications(): JsonResponse
    {
        $notifications = $this->notificationService->getUnreadNotifications(10);
        $count = $this->notificationService->getUnreadCount();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'count' => $count,
            'by_priority' => $this->notificationService->getUnreadCountByPriority()
        ]);
    }

    /**
     * API: Lấy số lượng thông báo chưa đọc (cho badge)
     */
    public function getUnreadCount(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'count' => $this->notificationService->getUnreadCount(),
            'by_priority' => $this->notificationService->getUnreadCountByPriority()
        ]);
    }

    /**
     * API: Đánh dấu thông báo đã đọc
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id' => 'required|integer|exists:admin_notifications,id'
        ]);

        $success = $this->notificationService->markAsRead($request->notification_id);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu thông báo đã đọc',
                'count' => $this->notificationService->getUnreadCount()
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy thông báo'
        ], 404);
    }

    /**
     * API: Đánh dấu tất cả thông báo đã đọc
     */
    public function markAllAsRead(): JsonResponse
    {
        $count = $this->notificationService->markAllAsRead();

        return response()->json([
            'success' => true,
            'message' => "Đã đánh dấu {$count} thông báo đã đọc",
            'count' => 0
        ]);
    }

    /**
     * API: Xóa thông báo
     */
    public function delete(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id' => 'required|integer|exists:admin_notifications,id'
        ]);

        $notification = \App\Models\AdminNotification::find($request->notification_id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa thông báo',
            'count' => $this->notificationService->getUnreadCount()
        ]);
    }

    /**
     * API: Xóa tất cả thông báo đã đọc
     */
    public function deleteRead(): JsonResponse
    {
        $count = \App\Models\AdminNotification::read()->delete();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$count} thông báo đã đọc"
        ]);
    }

    /**
     * API: Xóa thông báo cũ (quá 30 ngày)
     */
    public function deleteOld(): JsonResponse
    {
        $count = $this->notificationService->deleteOldNotifications();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$count} thông báo cũ"
        ]);
    }

    /**
     * Hiển thị chi tiết thông báo
     */
    public function show($id)
    {
        $notification = \App\Models\AdminNotification::findOrFail($id);
        
        // Đánh dấu đã đọc khi xem chi tiết
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        // Format dữ liệu bổ sung
        $formattedData = $this->dataFormatterService->formatData($notification->data);

        return view('admin.notifications.show', compact('notification', 'formattedData'));
    }

    /**
     * Tạo thông báo test (cho mục đích demo)
     */
    public function createTestNotification(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string',
            'message' => 'required|string',
            'priority' => 'nullable|string|in:low,normal,high,urgent'
        ]);

        $notification = \App\Models\AdminNotification::createNotification(
            $request->type,
            $request->title,
            $request->message,
            $request->data ?? [],
            $request->priority ?? 'normal'
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo thông báo test',
            'notification' => $notification
        ]);
    }

    /**
     * Tạo thông báo test cho ghi chú
     */
    public function createTestNoteNotification(Request $request): JsonResponse
    {
        $notification = \App\Models\AdminNotification::createNotification(
            'booking_note_created',
            'Ghi chú mới (Test)',
            'Ghi chú mới từ Khách hàng cho đặt phòng #TEST001',
            [
                'note_id' => 1,
                'booking_id' => 1,
                'user_id' => 1,
                'type' => 'customer',
                'visibility' => 'public',
                'is_internal' => false,
                'booking_code' => 'TEST001'
            ],
            'normal',
            'fas fa-sticky-note',
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo thông báo test cho ghi chú',
            'notification' => $notification
        ]);
    }

    /**
     * Tạo thông báo test cho đánh giá
     */
    public function createTestReviewNotification(Request $request): JsonResponse
    {
        $notification = \App\Models\AdminNotification::createNotification(
            'room_type_review_created',
            'Đánh giá phòng mới (Test)',
            'Đánh giá 5/5 sao cho Phòng Deluxe từ Nguyễn Văn A',
            [
                'review_id' => 1,
                'user_id' => 1,
                'room_type_id' => 1,
                'rating' => 5,
                'room_type_name' => 'Phòng Deluxe'
            ],
            'normal',
            'fas fa-star',
            'info'
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã tạo thông báo test cho đánh giá',
            'notification' => $notification
        ]);
    }

    /**
     * API: Lấy danh sách thông báo với filter (cho AJAX)
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $type = $request->get('type');
        $priority = $request->get('priority');
        $isRead = $request->get('is_read');
        $page = $request->get('page', 1);

        $query = \App\Models\AdminNotification::query();

        if ($type && $type !== 'all') {
            if ($type === 'unread') {
                $query->unread();
            } else {
                $query->ofType($type);
            }
        }

        if ($priority) {
            $query->ofPriority($priority);
        }

        if ($isRead !== null && $isRead !== '') {
            if ($isRead) {
                $query->read();
            } else {
                $query->unread();
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20, ['*'], 'page', $page);

        // Format notifications for response
        $formattedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->title,
                'message' => $notification->message,
                'type' => $notification->type,
                'priority' => $notification->priority,
                'is_read' => $notification->is_read,
                'time_ago' => $notification->time_ago,
                'color' => $notification->color,
                'display_icon' => $notification->display_icon,
                'badge_color' => $notification->badge_color,
                'show_url' => route('admin.notifications.show', $notification->id),
                'mark_read_url' => route('admin.notifications.mark-read', $notification->id),
                'delete_url' => route('admin.notifications.delete', $notification->id),
            ];
        });

        return response()->json([
            'success' => true,
            'notifications' => $formattedNotifications,
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
            'filters' => [
                'type' => $type,
                'priority' => $priority,
                'is_read' => $isRead,
            ]
        ]);
    }
}
