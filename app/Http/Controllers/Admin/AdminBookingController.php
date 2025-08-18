<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\Admin\AdminBookingServiceInterface;
use App\Interfaces\Services\Admin\AdminBookingServiceServiceInterface;
use App\Services\NotificationDataFormatterService;
use App\Services\PaymentService;
use App\Mail\BookingConfirmationMail;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

use Illuminate\Validation\ValidationException;
use App\Interfaces\Services\RegistrationDocumentServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class AdminBookingController extends Controller
{
    protected $bookingService;
    protected $dataFormatterService;
    protected $bookingServiceService;
    protected $paymentService;
    protected $registrationDocumentService;

    /**
     * Khởi tạo controller
     */
    public function __construct(
        AdminBookingServiceInterface $bookingService,
        NotificationDataFormatterService $dataFormatterService,
        AdminBookingServiceServiceInterface $bookingServiceService,
        PaymentService $paymentService,
        RegistrationDocumentServiceInterface $registrationDocumentService
    ) {
        $this->bookingService = $bookingService;
        $this->dataFormatterService = $dataFormatterService;
        $this->bookingServiceService = $bookingServiceService;
        $this->paymentService = $paymentService;
        $this->registrationDocumentService = $registrationDocumentService;
    }

    // ==================== BOOKING METHODS ====================

    /**
     * Hiển thị danh sách đặt phòng
     */
    public function index(Request $request)
    {
        $status = $request->get('status');
        $paymentStatus = $request->get('payment_status');
        $bookings = $this->bookingService->getBookingsWithPagination($request);

        return view('admin.bookings.index', compact('bookings', 'status', 'paymentStatus'));
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

        // Lấy dữ liệu services cho phần quản lý dịch vụ
        $bookingServices = $this->bookingServiceService->getBookingServices($booking->id);
        $availableRoomTypeServices = $this->bookingServiceService->getAvailableRoomTypeServices($booking->id);
        $availableServices = $this->bookingServiceService->getAvailableServices($booking->id);

        return view('admin.bookings.show', compact('booking', 'validNextStatuses', 'bookingServices', 'availableRoomTypeServices', 'availableServices'));
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
            'status' => 'required|in:pending,pending_payment,confirmed,checked_in,checked_out,completed,cancelled,no_show',
            'admin_notes' => 'nullable|string|max:1000',
            
            // Thông tin căn cước của khách
            'guest_full_name' => 'nullable|string|max:255',
            'guest_id_number' => 'nullable|string|max:20',
            'guest_birth_date' => 'nullable|date|before_or_equal:today',
            'guest_gender' => 'nullable|in:male,female,other',
            'guest_nationality' => 'nullable|string|max:100',
            'guest_permanent_address' => 'nullable|string|max:500',
            'guest_current_address' => 'nullable|string|max:500',
            'guest_phone' => 'nullable|string|max:20',
            'guest_email' => 'nullable|email|max:255',
            'guest_purpose_of_stay' => 'nullable|in:business,tourism,family,medical,study,other',
            'guest_vehicle_number' => 'nullable|string|max:20',
            'guest_notes' => 'nullable|string|max:1000',
        ]);

        $booking = $this->bookingService->updateBooking($id, $validatedData);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Đã cập nhật đặt phòng thành công.');
    }

    /**
     * Cập nhật trạng thái đặt phòng
     */
    public function updateStatus(Request $request, $bookingId)
    {
        try {
            $bookingModel = $this->bookingService->getBookingDetails($bookingId);
            
            if (!$bookingModel) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Không tìm thấy đặt phòng'], 404);
                }
                return redirect()->back()->with('error', 'Không tìm thấy đặt phòng');
            }

            $validatedData = $request->validate([
                'status' => 'required|in:pending,pending_payment,confirmed,checked_in,checked_out,completed,cancelled,no_show',
            ]);

            $success = $this->bookingService->updateBookingStatus($bookingModel->id, $validatedData['status']);
            
            if ($success) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Đã cập nhật trạng thái đặt phòng thành công',
                        'booking' => $bookingModel
                    ]);
                }
                return redirect()->back()
                    ->with('success', 'Đã cập nhật trạng thái đặt phòng thành công');
            } else {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'Không thể cập nhật trạng thái'], 500);
                }
                return redirect()->back()->with('error', 'Không thể cập nhật trạng thái');
            }
        } catch (\Exception $e) {
            Log::error('Error updating booking status: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Có lỗi xảy ra khi cập nhật trạng thái'], 500);
            }
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi cập nhật trạng thái: ' . $e->getMessage());
        }
    }

    /**
     * Xem trước giấy đăng ký tạm chú tạm vắng
     */
    public function previewRegistration($id)
    {
        try {
            $booking = $this->bookingService->getBookingDetails($id);
            
            if (!$booking) {
                return redirect()->back()->with('error', 'Không tìm thấy đặt phòng');
            }

            if (!$booking->hasCompleteIdentityInfo()) {
                return redirect()->back()->with('error', 'Thông tin căn cước của khách chưa đầy đủ');
            }

            $hotelInfo = $booking->getHotelInfo();
            
            return view('admin.bookings.registration-template', [
                'booking' => $booking,
                'hotelInfo' => $hotelInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Error previewing registration: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xem trước giấy đăng ký');
        }
    }



    /**
     * Tạo giấy đăng ký tạm chú tạm vắng PDF
     */
    public function generateRegistrationPdf($id)
    {
        try {
            $booking = $this->bookingService->getBookingDetails($id);
            
            if (!$booking) {
                return redirect()->back()->with('error', 'Không tìm thấy đặt phòng');
            }

            if (!$booking->hasCompleteIdentityInfo()) {
                return redirect()->back()->with('error', 'Thông tin căn cước của khách chưa đầy đủ');
            }

            $filepath = $this->registrationDocumentService->generateRegistrationDocument($booking);
            
            if (!$filepath) {
                return redirect()->back()->with('error', 'Không thể tạo giấy đăng ký');
            }

            return redirect()->back()->with('success', 'Đã tạo giấy đăng ký PDF thành công');
        } catch (\Exception $e) {
            Log::error('Error generating registration PDF: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi tạo giấy đăng ký');
        }
    }

    /**
     * Gửi giấy đăng ký qua email
     */
    public function sendRegistrationEmail($id)
    {
        try {
            $booking = $this->bookingService->getBookingDetails($id);
            
            if (!$booking) {
                return redirect()->back()->with('error', 'Không tìm thấy đặt phòng');
            }

            if (!$booking->hasCompleteIdentityInfo()) {
                return redirect()->back()->with('error', 'Thông tin căn cước của khách chưa đầy đủ');
            }

            // Tạo PDF trước nếu chưa có
            if ($booking->registration_status === 'pending') {
                $filepath = $this->registrationDocumentService->generateRegistrationDocument($booking);
                if (!$filepath) {
                    return redirect()->back()->with('error', 'Không thể tạo giấy đăng ký');
                }
            } else {
                // Tìm file PDF đã tạo
                $files = Storage::disk('public')->files('registrations');
                $filepath = null;
                foreach ($files as $file) {
                    if (strpos($file, $booking->booking_id) !== false && strpos($file, '.pdf') !== false) {
                        $filepath = 'public/' . $file;
                        break;
                    }
                }
                
                if (!$filepath) {
                    $filepath = $this->registrationDocumentService->generateRegistrationDocument($booking);
                }
            }

            // Gửi email với file PDF
            $email = $booking->guest_email ?: $booking->user->email;
            
            if (!$email) {
                return redirect()->back()->with('error', 'Không có email để gửi giấy đăng ký');
            }

            Mail::send('emails.registration-document', [
                'booking' => $booking,
                'hotelInfo' => $booking->getHotelInfo(),
            ], function ($message) use ($email, $booking, $filepath) {
                $message->to($email)
                        ->subject('Giấy đăng ký tạm chú tạm vắng - Booking #' . $booking->booking_id)
                        ->attach(storage_path('app/' . $filepath));
            });

            // Cập nhật trạng thái đã gửi
            $booking->update([
                'registration_status' => 'sent',
                'registration_sent_at' => now(),
            ]);

            $success = true;
            
            if (!$success) {
                return redirect()->back()->with('error', 'Không thể gửi email');
            }

            return redirect()->back()->with('success', 'Đã gửi giấy đăng ký qua email thành công');
        } catch (\Exception $e) {
            Log::error('Error sending registration email: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi gửi email');
        }
    }

    /**
     * Tải xuống giấy đăng ký
     */
    public function downloadRegistration($id, Request $request): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $booking = $this->bookingService->getBookingDetails($id);
            
            if (!$booking) {
                abort(404, 'Không tìm thấy đặt phòng');
            }

            $format = $request->get('format', 'pdf');
            
            $filepath = $this->registrationDocumentService->generateRegistrationDocument($booking);
            
            if (!$filepath) {
                abort(500, 'Không thể tạo giấy đăng ký');
            }

            $fullPath = storage_path('app/public/' . $filepath);
            
            if (!file_exists($fullPath)) {
                abort(404, 'File không tồn tại');
            }

            $filename = 'registration_' . $booking->booking_id . '_' . date('Y-m-d_H-i-s') . '.pdf';
            
            return response()->download($fullPath, $filename);
        } catch (\Exception $e) {
            Log::error('Error downloading registration: ' . $e->getMessage());
            abort(500, 'Có lỗi xảy ra khi tải xuống');
        }
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
            $query->where(function ($q) use ($search) {
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
            Log::error('Error getting unread notification count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'count' => 0
            ], 500);
        }
    }

    /**
     * API: Lấy số lượng thông báo chưa đọc (alias cho route)
     */
    public function getUnreadCount(): \Illuminate\Http\JsonResponse
    {
        return $this->getUnreadNotificationCount();
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
            Log::error('Error getting unread notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'notifications' => []
            ], 500);
        }
    }

    /**
     * Xóa nhiều thông báo
     */
    public function deleteNotification(Request $request): JsonResponse
    {
        $request->validate([
            'notification_id' => 'required|integer|exists:admin_notifications,id'
        ]);

        $notification = \App\Models\AdminNotification::find($request->notification_id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa thông báo',
            'count' => $this->bookingService->getUnreadNotificationCount()
        ]);
    }

    /**
     * API: Xóa tất cả thông báo đã đọc
     */
    public function deleteReadNotifications(): JsonResponse
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
    public function deleteOldNotifications(): JsonResponse
    {
        $count = $this->bookingService->deleteOldNotifications();

        return response()->json([
            'success' => true,
            'message' => "Đã xóa {$count} thông báo cũ"
        ]);
    }

    /**
     * Hiển thị chi tiết thông báo
     */
    public function notificationShow($id)
    {
        $notification = \App\Models\AdminNotification::findOrFail($id);

        // Đánh dấu đã đọc khi xem chi tiết
        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Đánh dấu thông báo đã đọc
     */
    public function markAsRead($id)
    {
        try {
            $notification = \App\Models\AdminNotification::findOrFail($id);
            $notification->markAsRead();
            
            return response()->json([
                'success' => true,
                'message' => 'Đã đánh dấu thông báo đã đọc'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể đánh dấu thông báo đã đọc'
            ], 500);
        }
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     */
    public function markAllAsRead(): JsonResponse
    {
        try {
            $count = \App\Models\AdminNotification::unread()->update([
                'is_read' => true,
                'read_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => "Đã đánh dấu {$count} thông báo đã đọc",
                'count' => 0
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể đánh dấu thông báo đã đọc'
            ], 500);
        }
    }

    /**
     * Xóa thông báo
     */
    public function destroy($id)
    {
        try {
            $notification = \App\Models\AdminNotification::findOrFail($id);
            $notification->delete();
            
            return redirect()->route('admin.notifications.index')->with('success', 'Đã xóa thông báo thành công');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Không thể xóa thông báo');
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
            $count = \App\Models\AdminNotification::whereIn('id', $ids)->update([
                'is_read' => true,
                'read_at' => now()
            ]);
                    return redirect()->route('admin.notifications.index')->with('success', "Đã đánh dấu {$count} thông báo đã đọc!");
    }
    return redirect()->route('admin.notifications.index')->with('warning', 'Bạn chưa chọn thông báo nào để đánh dấu đã đọc!');
    }

    /**
     * API: Lấy danh sách thông báo với filter (cho AJAX)
     */
    public function getNotifications(Request $request): JsonResponse
    {
        $type = $request->get('type');
        $priority = $request->get('priority');
        $isRead = $request->get('is_read');
        $search = $request->get('search');
        $page = $request->get('page', 1);

        $query = \App\Models\AdminNotification::query();

        // Tìm kiếm theo từ khóa
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        if ($type && $type !== 'all') {
            if ($type === 'unread') {
                $query->unread();
            } else {
                $query->ofType($type);
            }
        }

        if ($priority && $priority !== 'all') {
            $query->ofPriority($priority);
        }

        if ($isRead !== null && $isRead !== '') {
            if ($isRead == '1') {
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
                'search' => $search,
            ]
        ]);
    }

    /**
     * API: Xóa hàng loạt thông báo (chuẩn Laravel, redirect về lại trang)
     */
    public function deleteNotifications(Request $request)
    {
        $ids = (array) $request->input('notification_id');
        $count = $this->bookingService->deleteNotifications($ids);
        return redirect()->route('admin.notifications.index')
            ->with('success', "Đã xóa $count thông báo");
    }

    /**
     * API: Đánh dấu đã đọc hàng loạt thông báo
     */
    public function markNotificationsAsRead(Request $request): \Illuminate\Http\JsonResponse
    {
        $ids = (array) $request->input('notification_id');
        $count = $this->bookingService->markNotificationsAsRead($ids);
        return response()->json([
            'success' => $count > 0,
            'message' => "Đã đánh dấu $count thông báo đã đọc",
            'count' => $this->bookingService->getUnreadNotificationCount()
        ]);
    }
    /**
     * Thêm dịch vụ vào booking
     */
    public function addServiceToBooking(Request $request, $id)
    {
        try {
            $booking = $this->bookingService->getBookingDetails($id);
            if (!$booking) {
                return redirect()->back()->with('error', 'Booking không tồn tại');
            }

            // Validate request
            $request->validate([
                'service_name' => 'required|string|max:255',
                'service_price' => 'required|numeric|min:0',
                'quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string|max:500'
            ]);

            // Add custom service to booking
            $bookingService = $this->bookingServiceService->addCustomServiceToBooking(
                $booking->id,
                $request->service_name,
                $request->service_price,
                $request->quantity,
                $request->notes
            );

            return redirect()->back()->with('success', 'Đã thêm dịch vụ "' . $request->service_name . '" thành công!');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error adding service to booking: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi thêm dịch vụ. Vui lòng thử lại.');
        }
    }

    /**
     * Xóa dịch vụ khỏi booking
     */
    public function destroyServiceFromBooking($bookingId, $bookingServiceId)
    {
        try {
            $this->bookingServiceService->destroyServiceFromBooking($bookingServiceId);
            return redirect()->back()->with('success', 'Đã xóa dịch vụ thành công!');
        } catch (\Exception $e) {
            Log::error('Error removing service from booking: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi xóa dịch vụ.');
        }
    }

    /**
     * Xác nhận thanh toán chuyển khoản
     */
    public function confirmPayment(Request $request, $id)
    {
        try {
            $booking = $this->bookingService->getBookingDetails($id);

            // Tìm payment đang processing
            $processingPayment = $booking->payments->where('status', 'processing')->first();

            if (!$processingPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy giao dịch thanh toán đang chờ xác nhận'
                ]);
            }

            // Cập nhật trạng thái payment thành completed
            $processingPayment->update([
                'status' => 'completed',
                'gateway_message' => 'Đã xác nhận bởi admin',
                'paid_at' => now()
            ]);

            // Tự động chuyển trạng thái booking nếu đang ở pending hoặc pending_payment
            if ($booking->status === 'pending' || $booking->status === 'pending_payment') {
                $this->paymentService->confirmBookingAfterPayment($booking);
            }

            // Tạo thông báo cho việc xác nhận thanh toán
            $this->bookingService->createNotification(
                'payment_confirmed',
                'Thanh toán đã được xác nhận',
                "Đặt phòng #{$booking->booking_id} đã được xác nhận thanh toán thành công.",
                [
                    'booking_id' => $booking->id,
                    'payment_id' => $processingPayment->id,
                    'amount' => $processingPayment->amount
                ],
                'high',
                'fas fa-check-circle',
                'success'
            );

            // Gửi email xác nhận thanh toán
            $this->paymentService->sendPaymentConfirmationEmail($processingPayment);

            return response()->json([
                'success' => true,
                'message' => 'Đã xác nhận thanh toán thành công'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Thu tiền phát sinh (COD) cho các dịch vụ thêm sau khi khách đã thanh toán trước đó.
     */
    public function collectAdditionalPayment(Request $request, $id)
    {
        try {
            $booking = $this->bookingService->getBookingDetails($id);
            if (!$booking) {
                return redirect()->back()->with('error', 'Booking không tồn tại');
            }

            $request->validate([
                'amount' => 'nullable|numeric|min:0.01',
                'note' => 'nullable|string|max:500'
            ]);

            // Tính công nợ còn thiếu
            $outstanding = max(0, (float)($booking->total_booking_price ?? 0) - (float)($booking->total_paid ?? 0));
            $amount = $request->filled('amount') ? (float)$request->input('amount') : $outstanding;
            // Không cho thu vượt công nợ
            $amount = min($amount, $outstanding);

            if ($amount <= 0) {
                return redirect()->back()->with('error', 'Không có công nợ cần thu.');
            }

            // Tạo giao dịch COD hoàn tất ngay
            $payment = $this->paymentService->createPayment($booking, [
                'payment_method' => 'cod',
                'amount' => $amount,
                'currency' => 'VND',
                'status' => 'completed',
                'transaction_id' => 'COD_' . $booking->booking_id . '_' . time(),
                'gateway_name' => 'Cash at Desk',
                'gateway_response' => [
                    'note' => $request->input('note')
                ]
            ]);

            // Gắn mốc thời gian thanh toán và ghi chú cổng (nếu có cột)
            $payment->update([
                'paid_at' => now(),
                'gateway_message' => 'Thanh toán tại quầy'
            ]);

            // Gửi email xác nhận
            $this->paymentService->sendPaymentConfirmationEmail($payment);

            return redirect()->back()->with('success', 'Đã thu ' . number_format($amount) . ' VND cho phần phát sinh.');
        } catch (\Throwable $e) {
            \Log::error('collectAdditionalPayment error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Có lỗi xảy ra khi thu tiền: ' . $e->getMessage());
        }
    }
}
