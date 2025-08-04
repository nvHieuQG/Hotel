<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\Admin\AdminBookingServiceInterface;
use App\Services\NotificationDataFormatterService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Interfaces\Services\RegistrationDocumentServiceInterface;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

class AdminBookingController extends Controller
{
    protected $bookingService;
    protected $dataFormatterService;
    protected $registrationDocumentService;

    /**
     * Khởi tạo controller
     */
    public function __construct(
        AdminBookingServiceInterface $bookingService,
        NotificationDataFormatterService $dataFormatterService,
        RegistrationDocumentServiceInterface $registrationDocumentService
    ) {
        $this->bookingService = $bookingService;
        $this->dataFormatterService = $dataFormatterService;
        $this->registrationDocumentService = $registrationDocumentService;
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
                'status' => 'required|in:pending,confirmed,checked_in,checked_out,completed,cancelled,no_show',
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
     * Tạo giấy đăng ký tạm chú tạm vắng Word
     */
    public function generateRegistrationWord($id)
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
            Log::error('Error generating registration Word: ' . $e->getMessage());
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
     * Xem file Word
     */
    public function viewWord($id)
    {
        try {
            $booking = $this->bookingService->getBookingDetails($id);
            
            if (!$booking) {
                abort(404, 'Không tìm thấy đặt phòng');
            }

            // Tìm file Word đã tạo
            $files = Storage::disk('public')->files('registrations');
            $filepath = null;
            foreach ($files as $file) {
                if (strpos($file, $booking->booking_id) !== false && strpos($file, '.html') !== false) {
                    $filepath = $file;
                    break;
                }
            }
            
            if (!$filepath) {
                // Tạo file Word nếu chưa có
                $filepath = $this->registrationDocumentService->generateRegistrationWord($booking);
                if (!$filepath) {
                    abort(500, 'Không thể tạo giấy đăng ký');
                }
            }

            // Kiểm tra file tồn tại bằng Storage
            if (!Storage::disk('public')->exists(str_replace('public/', '', $filepath))) {
                abort(404, 'File không tồn tại');
            }

            $fullPath = storage_path('app/public/' . str_replace('public/', '', $filepath));

            return response()->download($fullPath, 'registration_' . $booking->booking_id . '.docx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'Cache-Control' => 'no-cache, must-revalidate',
                'Pragma' => 'no-cache'
            ]);
        } catch (\Exception $e) {
            Log::error('Error viewing Word: ' . $e->getMessage());
            abort(500, 'Có lỗi xảy ra khi xem file');
        }
    }

    /**
     * Download file Word
     */
    public function downloadWord($id)
    {
        try {
            $booking = $this->bookingService->getBookingDetails($id);
            
            if (!$booking) {
                abort(404, 'Không tìm thấy đặt phòng');
            }

            // Tìm file Word đã tạo
            $files = Storage::disk('public')->files('registrations');
            $filepath = null;
            foreach ($files as $file) {
                if (strpos($file, $booking->booking_id) !== false && strpos($file, '.pdf') !== false) {
                    $filepath = $file;
                    break;
                }
            }
            
            if (!$filepath) {
                // Tạo file Word nếu chưa có
                $filepath = $this->registrationDocumentService->generateRegistrationWord($booking);
                if (!$filepath) {
                    abort(500, 'Không thể tạo giấy đăng ký');
                }
            }

            // Kiểm tra file tồn tại bằng Storage
            if (!Storage::disk('public')->exists(str_replace('public/', '', $filepath))) {
                abort(404, 'File không tồn tại');
            }

            $fullPath = storage_path('app/public/' . str_replace('public/', '', $filepath));
            $filename = 'registration_' . $booking->booking_id . '_' . date('Y-m-d_H-i-s') . '.docx';
            
            return response()->download($fullPath, $filename);
        } catch (\Exception $e) {
            Log::error('Error downloading Word: ' . $e->getMessage());
            abort(500, 'Có lỗi xảy ra khi tải xuống');
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