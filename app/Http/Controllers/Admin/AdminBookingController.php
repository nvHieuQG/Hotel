<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\Admin\AdminBookingServiceInterface;
use App\Services\NotificationDataFormatterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class AdminBookingController extends Controller
{
    protected $bookingService;
    protected $dataFormatterService;

    /**
     * Khởi tạo controller
     */
    public function __construct(
        AdminBookingServiceInterface $bookingService,
        NotificationDataFormatterService $dataFormatterService
    ) {
        $this->bookingService = $bookingService;
        $this->dataFormatterService = $dataFormatterService;
    }

    // ==================== BOOKING METHODS ====================

    /**
     * Hiển thị danh sách đặt phòng
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $bookings = $this->bookingService->getBookingsWithPagination($request);
        
        return view('admin.bookings.index', compact('bookings', 'status'));
    }

    /**
     * Hiển thị chi tiết đặt phòng
     */
    public function show($id)
    {
        $booking = $this->bookingService->getBookingDetails($id);
        if (is_numeric($id)) {
            $validNextStatuses = $this->bookingService->getValidNextStatuses((int)$id);
        } else {
            $validNextStatuses = $this->bookingService->getValidNextStatusesByCode($id);
        }
        return view('admin.bookings.show', compact('booking', 'validNextStatuses'));
    }

    /**
     * Hiển thị form tạo đặt phòng mới
     */
    public function create()
    {
        $formData = $this->bookingService->getCreateFormData();
        
        return view('admin.bookings.create', [
            'rooms' => $formData['rooms'],
            'users' => $formData['users']
        ]);
    }

    /**
     * Lưu đặt phòng mới
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'required|in:pending,confirmed',
        ]);
        
        $this->bookingService->createBooking($validatedData);
        
        return redirect()->route('admin.bookings.index')
            ->with('success', 'Đã tạo đặt phòng thành công.');
    }

    /**
     * Hiển thị form chỉnh sửa đặt phòng
     */
    public function edit($id)
    {
        $formData = $this->bookingService->getEditFormData($id);
        $validNextStatuses = $this->bookingService->getValidNextStatuses($id);
        
        return view('admin.bookings.edit', [
            'booking' => $formData['booking'],
            'rooms' => $formData['rooms'],
            'users' => $formData['users'],
            'validNextStatuses' => $validNextStatuses
        ]);
    }

    /**
     * Cập nhật thông tin đặt phòng
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,completed,cancelled,no_show',
        ]);
        
        $booking = $this->bookingService->updateBooking($id, $validatedData);
        
        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Đã cập nhật đặt phòng thành công.');
    }

    /**
     * Cập nhật trạng thái đặt phòng
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,completed,cancelled,no_show'
        ]);
        
        try {
            $this->bookingService->updateBookingStatus($id, $request->status);
            
            // Lấy thông tin booking để hiển thị trong thông báo
            $booking = $this->bookingService->getBookingDetails($id);
            $statusText = match($request->status) {
                'pending' => 'Chờ xác nhận',
                'confirmed' => 'Đã xác nhận',
                'checked_in' => 'Đã nhận phòng',
                'checked_out' => 'Đã trả phòng',
                'completed' => 'Hoàn thành',
                'cancelled' => 'Đã hủy',
                'no_show' => 'Khách không đến',
                default => 'Không xác định'
            };
            
            return redirect()->back()->with('success', "Đã cập nhật trạng thái đặt phòng #{$booking->booking_id} thành '{$statusText}'. Ghi chú và thông báo đã được tạo tự động.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Xóa đặt phòng
     */
    public function destroy($id)
    {
        // Xử lý xóa notification
        $notification = \App\Models\AdminNotification::findOrFail($id);
        $notification->delete();
        return redirect()->route('admin.notifications.index')
            ->with('success', 'Đã xóa thông báo thành công.');
    }
    
    /**
     * Hiển thị báo cáo đặt phòng
     */
    public function report(Request $request)
    {
        try {
            $reportData = $this->bookingService->getReportData($request);
            
            return view('admin.bookings.report', [
                'bookings' => $reportData['bookings'],
                'fromDate' => $reportData['filters']['from_date'] ?? null,
                'toDate' => $reportData['filters']['to_date'] ?? null,
                'status' => $reportData['filters']['status'] ?? null,
                'totalRevenue' => $reportData['totalRevenue'],
                'statusStats' => $reportData['statusStats']
            ]);
        } catch (\Exception $e) {
            return redirect()->route('admin.bookings.index')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Trả về partial chi tiết booking cho AJAX/modal (admin)
     */
    public function detailPartial($id)
    {
        $booking = $this->bookingService->getBookingDetails($id);
        return view('admin.bookings.detail', compact('booking'));
    }

    // ==================== NOTIFICATION METHODS ====================

    /**
     * Hiển thị trang quản lý thông báo
     */
    public function notificationsIndex(Request $request)
    {
        $type = $request->get('type', null);
        $priority = $request->get('priority', null);
        $isRead = $request->get('is_read', null);
        $search = $request->get('search', null);

        $query = \App\Models\AdminNotification::query();

        // Tìm kiếm theo từ khóa
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%")
                  ->orWhere('type', 'like', "%{$search}%")
                  ->orWhere('priority', 'like', "%{$search}%")
                  ->orWhere('id', $search)
                  ->orWhere('is_read', $search)
                  ->orWhereDate('created_at', $search)
                  ->orWhereDate('updated_at', $search);
            });
        }

        // Lọc theo loại
        if ($type) {
            if ($type === 'unread') {
                $query->unread();
            } else {
                $query->ofType($type);
            }
        }

        // Lọc theo độ ưu tiên
        if ($priority) {
            $query->ofPriority($priority);
        }

        // Lọc theo trạng thái đọc
        if ($isRead !== null && $isRead !== '') {
            if ($isRead == '1') {
                $query->read();
            } else if ($isRead == '0') {
                $query->unread();
            }
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.notifications.index', compact('notifications', 'type', 'priority', 'isRead', 'search'));
    }

    /**
     * Hiển thị chi tiết thông báo
     */
    public function notificationShow($id)
    {
        // Đánh dấu đã đọc
        $this->bookingService->markNotificationAsRead($id);
        $notification = \App\Models\AdminNotification::findOrFail($id);
        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * API: Lấy số lượng thông báo chưa đọc (cho badge)
     */
    public function getUnreadNotificationCount(): \Illuminate\Http\JsonResponse
    {
        try {
            $count = $this->bookingService->getUnreadNotificationCount();
            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting unread notification count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'count' => 0
            ], 500);
        }
    }

    /**
     * API: Lấy danh sách thông báo chưa đọc (cho dropdown khi bấm chuông)
     */
    public function getUnreadNotifications(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $notifications = $this->bookingService->getUnreadNotifications($limit);
            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            \Log::error('Error getting unread notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'notifications' => []
            ], 500);
        }
    }

    /**
     * Xóa nhiều thông báo
     */
    public function deleteMulti(Request $request)
    {
        $ids = $request->input('notification_id', []);
        if (!empty($ids)) {
            \App\Models\AdminNotification::whereIn('id', $ids)->delete();
            return redirect()->route('admin.notifications.index')->with('success', 'Đã xóa các thông báo đã chọn!');
        }
        return redirect()->route('admin.notifications.index')->with('warning', 'Bạn chưa chọn thông báo nào để xóa!');
    }

    /**
     * Đánh dấu đã đọc nhiều thông báo
     */
    public function markReadMulti(Request $request)
    {
        $ids = $request->input('notification_id', []);
        if (!empty($ids)) {
            \App\Models\AdminNotification::whereIn('id', $ids)->update(['is_read' => true]);
            return redirect()->route('admin.notifications.index')->with('success', 'Đã đánh dấu đã đọc các thông báo đã chọn!');
        }
        return redirect()->route('admin.notifications.index')->with('warning', 'Bạn chưa chọn thông báo nào để đánh dấu đã đọc!');
    }
} 