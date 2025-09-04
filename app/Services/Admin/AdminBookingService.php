<?php

namespace App\Services\Admin;

use App\Interfaces\Repositories\Admin\AdminBookingRepositoryInterface;
use App\Interfaces\Repositories\RoomRepositoryInterface;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Interfaces\Services\Admin\AdminBookingServiceInterface;
use App\Models\Booking;
use App\Models\Room;
use App\Models\AdminNotification;
use App\Mail\BookingConfirmationMail;
use App\Mail\PaymentConfirmationMail;
use App\Models\BookingService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;
use App\Repositories\Admin\AdminBookingRepository;

class AdminBookingService implements AdminBookingServiceInterface
{
    protected $adminBookingRepository;
    protected $roomRepository;
    protected $userRepository;

    public function __construct(
        AdminBookingRepositoryInterface $adminBookingRepository,
        RoomRepositoryInterface $roomRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->adminBookingRepository = $adminBookingRepository;
        $this->roomRepository = $roomRepository;
        $this->userRepository = $userRepository;
    }

    // ==================== BOOKING METHODS ====================

    /**
     * Lấy danh sách đặt phòng có phân trang
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getBookingsWithPagination(Request $request): LengthAwarePaginator
    {
        $filters = [
            'status' => $request->get('status'),
            'payment_status' => $request->get('payment_status')
        ];

        return $this->adminBookingRepository->getAllWithPagination($filters);
    }

    /**
     * Lấy chi tiết đặt phòng
     *
     * @param string $id
     * @return Booking
     */
    public function getBookingDetails($id): Booking
    {
        $booking = $this->adminBookingRepository->findById($id);

        if (!$booking) {
            throw new \Exception('Không tìm thấy đặt phòng');
        }

        return $booking;
    }

    /**
     * Tạo đặt phòng mới
     *
     * @param array $data
     * @return Booking
     */
    public function createBooking(array $data): Booking
    {
        // Xác thực dữ liệu
        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'required|in:pending,confirmed',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Tính tổng tiền
        $room = $this->roomRepository->findById($data['room_id']);
        $checkIn = new \DateTime($data['check_in_date']);
        $checkOut = new \DateTime($data['check_out_date']);
        $nights = $checkIn->diff($checkOut)->days;
        $totalPrice = $room->price * $nights;

        // Tạo booking ID duy nhất
        $bookingId = 'BK' . date('ymd') . strtoupper(Str::random(5));

        // Chuẩn bị dữ liệu
        $bookingData = [
            'user_id' => $data['user_id'],
            'room_id' => $data['room_id'],
            'booking_id' => $bookingId,
            'check_in_date' => $data['check_in_date'],
            'check_out_date' => $data['check_out_date'],
            'price' => $totalPrice,
            'status' => $data['status']
        ];

        // Tạo đặt phòng
        return $this->adminBookingRepository->create($bookingData);
    }

    /**
     * Cập nhật đặt phòng
     *
     * @param int $id
     * @param array $data
     * @return Booking
     */
    public function updateBooking(int $id, array $data): Booking
    {
        // Xác thực dữ liệu cơ bản
        $validator = Validator::make($data, [
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

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $booking = $this->adminBookingRepository->findById($id);

        if (!$booking) {
            throw new \Exception('Không tìm thấy đặt phòng');
        }

        // Tính lại tổng tiền nếu thay đổi phòng hoặc ngày
        if (
            $booking->room_id != $data['room_id'] ||
            $booking->check_in_date != $data['check_in_date'] ||
            $booking->check_out_date != $data['check_out_date']
        ) {

            $room = $this->roomRepository->findById($data['room_id']);
            $checkIn = new \DateTime($data['check_in_date']);
            $checkOut = new \DateTime($data['check_out_date']);
            $nights = $checkIn->diff($checkOut)->days;
            $totalPrice = $room->price * $nights;

            $data['price'] = $totalPrice;
        }
        
        // Lưu trạng thái cũ để so sánh
        $oldStatus = $booking->status;
        $oldIdentityInfo = $booking->hasCompleteIdentityInfo();
        
        // Cập nhật đặt phòng
        $this->adminBookingRepository->update($booking, $data);
        
        // Lấy booking đã cập nhật
        $updatedBooking = $this->adminBookingRepository->findById($id);
        
        // Kiểm tra thay đổi trạng thái
        if ($oldStatus !== $updatedBooking->status) {
            $this->handleStatusChange($updatedBooking, $oldStatus, $updatedBooking->status);
        }
        
        // Kiểm tra thay đổi thông tin căn cước
        $newIdentityInfo = $updatedBooking->hasCompleteIdentityInfo();
        if (!$oldIdentityInfo && $newIdentityInfo) {
            // Tạo ghi chú hệ thống khi thông tin căn cước được hoàn thiện
            $this->createSystemNote($id, 'Thông tin căn cước của khách đã được cập nhật đầy đủ', 'system');
        }
        
        return $updatedBooking;

        return $this->adminBookingRepository->findById($id);
    }

    /**
     * Xóa đặt phòng
     *
     * @param int $id
     * @return bool
     */
    public function deleteBooking(int $id): bool
    {
        return $this->adminBookingRepository->delete($id);
    }

    /**
     * Cập nhật trạng thái đặt phòng
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateBookingStatus(int $id, string $status): bool
    {
        $booking = $this->adminBookingRepository->findById($id);
        if (!$booking) {
            throw new \Exception('Không tìm thấy đặt phòng');
        }

        $currentStatus = $booking->status;

        // Logic đặc biệt cho trạng thái pending_payment
        if ($currentStatus === 'pending_payment') {
            // Kiểm tra xem đã thanh toán chưa
            if ($booking->hasSuccessfulPayment()) {
                // Nếu đã thanh toán và chuyển sang confirmed
                if ($status === 'confirmed') {
                    $success = $this->adminBookingRepository->updateStatus($id, $status);
                    if ($success) {
                        $this->handleStatusChange($booking, $currentStatus, $status);
                    }
                    return $success;
                }
            } else {
                // Nếu chưa thanh toán, chỉ cho phép hủy
                if (in_array($status, ['cancelled', 'no_show'])) {
                    $success = $this->adminBookingRepository->updateStatus($id, $status);
                    if ($success) {
                        $this->handleStatusChange($booking, $currentStatus, $status);
                    }
                    return $success;
                } else {
                    throw new \Exception('Không thể chuyển trạng thái khi chưa thanh toán. Chỉ có thể hủy đặt phòng.');
                }
            }
        }

        // Logic cho các trạng thái khác - chỉ cho phép chuyển từng bước một
        $statusOrder = [
            'pending',
            'pending_payment',
            'confirmed',
            'checked_in',
            'checked_out',
            'completed',
            'cancelled',
            'no_show',
        ];

        $currentIndex = array_search($currentStatus, $statusOrder);
        $newIndex = array_search($status, $statusOrder);

        // Kiểm tra trạng thái hợp lệ
        if ($newIndex === false || $currentIndex === false) {
            throw new \Exception('Trạng thái không hợp lệ.');
        }

        // Cho phép chuyển sang cancelled/no_show ở bất kỳ trạng thái nào (trừ completed)
        if (in_array($status, ['cancelled', 'no_show'])) {
            if ($currentStatus !== 'completed') {
                $success = $this->adminBookingRepository->updateStatus($id, $status);
                if ($success) {
                    $this->handleStatusChange($booking, $currentStatus, $status);
                }
                return $success;
            } else {
                throw new \Exception('Không thể chuyển trạng thái khi đã hoàn thành.');
            }
        }

        // Chỉ cho phép chuyển sang trạng thái ngay tiếp theo (không nhảy cóc)
        $nextIndex = $currentIndex + 1;
        if ($nextIndex < count($statusOrder) && $newIndex === $nextIndex) {
            $success = $this->adminBookingRepository->updateStatus($id, $status);
            if ($success) {
                $this->handleStatusChange($booking, $currentStatus, $status);
            }
            return $success;
        } else {
            throw new \Exception('Chỉ được phép chuyển trạng thái theo thứ tự từng bước một.');
        }
    }

    /**
     * Lấy dữ liệu cho dashboard
     *
     * @return array
     */
    public function getDashboardData(): array
    {
        // Đếm số đặt phòng hôm nay
        $todayBookings = $this->adminBookingRepository->countToday();

        // Tính doanh thu tháng hiện tại
        $monthlyRevenue = $this->adminBookingRepository->calculateMonthlyRevenue();

        // Đếm số đặt phòng đang chờ xác nhận (bao gồm cả pending và pending_payment)
        $pendingBookings = $this->adminBookingRepository->countByStatus('pending') + $this->adminBookingRepository->countByStatus('pending_payment');

        // Lấy danh sách đặt phòng gần đây
        $recentBookings = $this->adminBookingRepository->getRecent(5);

        // Thống kê theo trạng thái
        $statusCounts = [
            'pending' => $this->adminBookingRepository->countByStatus('pending') + $this->adminBookingRepository->countByStatus('pending_payment'),
            'confirmed' => $this->adminBookingRepository->countByStatus('confirmed'),
            'cancelled' => $this->adminBookingRepository->countByStatus('cancelled'),
            'completed' => $this->adminBookingRepository->countByStatus('completed'),
        ];

        return [
            'todayBookings' => $todayBookings,
            'monthlyRevenue' => $monthlyRevenue,
            'pendingBookings' => $pendingBookings,
            'recentBookings' => $recentBookings,
            'statusCounts' => $statusCounts
        ];
    }

    /**
     * Lấy dữ liệu cho báo cáo
     *
     * @param Request $request
     * @return array
     */
    public function getReportData(Request $request): array
    {
        // Kiểm tra quyền truy cập
        if (Auth::user()->role?->name == 'staff') {
            throw new \Exception('Bạn không có quyền truy cập trang báo cáo.');
        }

        $filters = [
            'from_date' => $request->get('from_date'),
            'to_date' => $request->get('to_date'),
            'status' => $request->get('status')
        ];

        $bookings = $this->adminBookingRepository->getBookingsForReport($filters);

        // Tính toán doanh thu
        // Doanh thu dịch vụ bổ sung khách chọn + dịch vụ admin thêm
        $bookingIds = $bookings->pluck('id')->filter()->values();
        $customerServiceRevenue = (float) $bookings->sum(function ($b) {
            return (float) ($b->extra_services_total ?? 0);
        });
        // Phụ phí (surcharge) được tính là doanh thu dịch vụ theo yêu cầu
        $surchargeRevenue = (float) $bookings->sum(function ($b) {
            return (float) ($b->surcharge ?? 0);
        });

        // Lấy tổng dịch vụ admin thêm theo từng booking (group by) để có thể trừ ra khỏi doanh thu phòng
        $adminAddedByBooking = collect();
        if ($bookingIds->isNotEmpty()) {
            $adminAddedByBooking = BookingService::query()
                ->whereIn('booking_id', $bookingIds)
                ->selectRaw('booking_id, SUM(total_price) as total')
                ->groupBy('booking_id')
                ->pluck('total', 'booking_id');
        }
        $adminAddedServiceRevenue = (float) ($adminAddedByBooking->isEmpty() ? 0 : $adminAddedByBooking->sum());

        // Doanh thu phòng (KHÔNG bao gồm dịch vụ): price - extra_services_total - admin_added_services (mỗi booking)
        $roomRevenue = (float) $bookings->sum(function ($b) use ($adminAddedByBooking) {
            $extra = (float) ($b->extra_services_total ?? 0);
            $surcharge = (float) ($b->surcharge ?? 0);
            $adminExtra = (float) ($adminAddedByBooking[$b->id] ?? 0);
            $base = (float) ($b->price ?? 0) - $extra - $surcharge - $adminExtra;
            return $base > 0 ? $base : 0; // tránh âm trong trường hợp dữ liệu lệch
        });

        // Gán lại biến theo ý nghĩa hiển thị
        $totalRevenue = $roomRevenue; // Doanh thu đặt phòng chỉ tính tiền phòng
        $serviceRevenue = $customerServiceRevenue + $surchargeRevenue + $adminAddedServiceRevenue; // Doanh thu dịch vụ (khách chọn + phụ phí + admin thêm)
        // Tổng hợp
        $grandRevenue = $totalRevenue + $serviceRevenue;

        // Thống kê theo trạng thái
        $statusStats = $bookings->groupBy('status')
            ->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'revenue' => $items->sum('price')
                ];
            });

        return [
            'bookings' => $bookings,
            'totalRevenue' => $totalRevenue,
            'serviceRevenue' => $serviceRevenue,
            'grandRevenue' => $grandRevenue,
            'statusStats' => $statusStats,
            'filters' => $filters
        ];
    }

    /**
     * Lấy dữ liệu cho form tạo đặt phòng
     *
     * @return array
     */
    public function getCreateFormData(): array
    {
        $rooms = $this->roomRepository->getAll();
        $users = $this->userRepository->getAll();

        return [
            'rooms' => $rooms,
            'users' => $users
        ];
    }

    /**
     * Lấy dữ liệu cho form chỉnh sửa đặt phòng
     *
     * @param int $id
     * @return array
     */
    public function getEditFormData(int $id): array
    {
        $booking = $this->adminBookingRepository->findById($id);

        if (!$booking) {
            throw new \Exception('Không tìm thấy đặt phòng');
        }

        $rooms = $this->roomRepository->getAll();
        $users = $this->userRepository->getAll();

        return [
            'booking' => $booking,
            'rooms' => $rooms,
            'users' => $users
        ];
    }

    /**
     * Lấy danh sách trạng thái hợp lệ tiếp theo cho booking
     *
     * @param int $id
     * @return array
     */
    public function getValidNextStatuses(int $id): array
    {
        $booking = $this->adminBookingRepository->findById($id);
        if (!$booking) {
            return [];
        }

        $currentStatus = $booking->status;
        $validStatuses = [];

        // Logic đặc biệt cho trạng thái pending_payment
        if ($currentStatus === 'pending_payment') {
            // Kiểm tra xem đã thanh toán chưa
            if ($booking->hasSuccessfulPayment()) {
                // Nếu đã thanh toán, cho phép chuyển sang confirmed
                $validStatuses['confirmed'] = 'Đã xác nhận';
            } else {
                // Nếu chưa thanh toán, chỉ cho phép hủy
                $validStatuses['cancelled'] = 'Đã hủy';
                $validStatuses['no_show'] = 'Khách không đến';
            }
            return $validStatuses;
        }

        // Logic cho các trạng thái khác - chỉ cho phép chuyển từng bước một
        $statusOrder = [
            'pending',
            'pending_payment',
            'confirmed',
            'checked_in',
            'checked_out',
            'completed',
        ];

        $currentIndex = array_search($currentStatus, $statusOrder);
        if ($currentIndex === false) {
            return [];
        }

        // Nếu trạng thái hiện tại KHÔNG phải là completed thì luôn cho phép chuyển sang cancelled và no_show
        if ($currentStatus !== 'completed') {
            $validStatuses['cancelled'] = 'Đã hủy';
            // Ẩn 'Khách không đến' nếu đã nhận phòng hoặc đã trả phòng
            if ($currentStatus !== 'checked_in' && $currentStatus !== 'checked_out') {
                $validStatuses['no_show'] = 'Khách không đến';
            }
        }

        // Chỉ cho phép chuyển sang trạng thái ngay tiếp theo (không nhảy cóc)
        $nextIndex = $currentIndex + 1;
        if ($nextIndex < count($statusOrder)) {
            $nextStatus = $statusOrder[$nextIndex];
            $validStatuses[$nextStatus] = match ($nextStatus) {
                'pending' => 'Chờ xác nhận',
                'pending_payment' => 'Chờ thanh toán',
                'confirmed' => 'Đã xác nhận',
                'checked_in' => 'Đã nhận phòng',
                'checked_out' => 'Đã trả phòng',
                'completed' => 'Hoàn thành',
                default => 'Không xác định'
            };
        }

        return $validStatuses;
    }

    /**
     * Lấy danh sách trạng thái hợp lệ tiếp theo cho booking theo mã code
     *
     * @param string $bookingCode
     * @return array
     */
    public function getValidNextStatusesByCode(string $bookingCode): array
    {
        $booking = $this->adminBookingRepository->findByBookingCode($bookingCode);
        if (!$booking) {
            return [];
        }
        $statusOrder = [
            'pending',
            'confirmed',
            'checked_in',
            'checked_out',
            'cancelled',
        ];
        $currentStatus = $booking->status;
        $currentIndex = array_search($currentStatus, $statusOrder);
        if ($currentIndex === false) {
            return [];
        }
        return array_slice($statusOrder, $currentIndex + 1);
    }

    // ==================== NOTIFICATION METHODS ====================
    public function getUnreadNotificationCount(): int
    {
        return $this->adminBookingRepository->getUnreadNotificationCount();
    }

    public function getUnreadNotifications(int $limit = 10): Collection
    {
        return $this->adminBookingRepository->getUnreadNotifications($limit);
    }

    public function getAllNotifications(int $perPage = 20): LengthAwarePaginator
    {
        return $this->adminBookingRepository->getAllNotifications($perPage);
    }

    public function markNotificationAsRead(int $id): bool
    {
        return $this->adminBookingRepository->markNotificationAsRead($id);
    }

    public function markAllNotificationsAsRead(): int
    {
        return $this->adminBookingRepository->markAllNotificationsAsRead();
    }

    /**
     * Đánh dấu thông báo đã đọc
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = AdminNotification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
            return true;
        }
        return false;
    }

    /**
     * Đánh dấu tất cả thông báo đã đọc
     */
    public function markAllAsRead(): int
    {
        return AdminNotification::unread()->update(['is_read' => true]);
    }

    /**
     * Xóa thông báo cũ (quá 30 ngày)
     */
    public function deleteOldNotifications(): int
    {
        $cutoffDate = Carbon::now()->subDays(30);
        return AdminNotification::where('created_at', '<', $cutoffDate)->delete();
    }

    /**
     * Xóa hàng loạt thông báo
     *
     * @param array $ids
     * @return int
     */
    public function deleteNotifications(array $ids): int
    {
        return \App\Models\AdminNotification::whereIn('id', $ids)->delete();
    }

    /**
     * Đánh dấu đã đọc hàng loạt thông báo
     *
     * @param array $ids
     * @return int
     */
    public function markNotificationsAsRead(array $ids): int
    {
        return \App\Models\AdminNotification::whereIn('id', $ids)->where('is_read', false)->update(['is_read' => true]);
    }

    /**
     * Tạo thông báo mới
     */
    public function createNotification(
        string $type,
        string $title,
        string $message,
        array $data = [],
        string $priority = 'normal',
        string $icon = null,
        string $color = null
    ): AdminNotification {
        return AdminNotification::create([
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'priority' => $priority,
            'icon' => $icon,
            'color' => $color,
            'is_read' => false,
        ]);
    }

    /**
     * Tạo thông báo cho ghi chú mới
     */
    public function createNoteNotification(array $noteData): AdminNotification
    {
        $booking = \App\Models\Booking::find($noteData['booking_id']);
        $user = \App\Models\User::find($noteData['user_id']);

        $title = 'Ghi chú mới';
        $message = "Ghi chú mới từ {$user->name} cho đặt phòng #{$booking->booking_id}";

        return $this->createNotification(
            'booking_note_created',
            $title,
            $message,
            $noteData,
            'normal',
            'fas fa-sticky-note',
            'info'
        );
    }

    /**
     * Tạo thông báo cho đánh giá mới
     */
    public function createReviewNotification(array $reviewData): AdminNotification
    {
        $user = \App\Models\User::find($reviewData['user_id']);
        $roomType = \App\Models\RoomType::find($reviewData['room_type_id']);

        $title = 'Đánh giá phòng mới';
        $message = "Đánh giá {$reviewData['rating']}/5 sao cho {$roomType->name} từ {$user->name}";

        return $this->createNotification(
            'room_type_review_created',
            $title,
            $message,
            $reviewData,
            'normal',
            'fas fa-star',
            'info'
        );
    }

    /**
     * Tạo thông báo cho đặt phòng mới
     */
    public function createBookingNotification(array $bookingData): AdminNotification
    {
        $user = \App\Models\User::find($bookingData['user_id']);
        $room = \App\Models\Room::find($bookingData['room_id']);

        $title = 'Đặt phòng mới';
        $message = "Đặt phòng mới từ {$user->name} cho {$room->roomType->name}";

        return $this->createNotification(
            'booking_created',
            $title,
            $message,
            $bookingData,
            'normal',
            'fas fa-calendar-check',
            'success'
        );
    }

    /**
     * Tạo thông báo cho thay đổi trạng thái đặt phòng
     */
    public function createStatusChangeNotification(array $bookingData, string $oldStatus, string $newStatus): AdminNotification
    {
        $user = \App\Models\User::find($bookingData['user_id']);
        $room = \App\Models\Room::find($bookingData['room_id']);

        $statusText = match ($newStatus) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã nhận phòng',
            'checked_out' => 'Đã trả phòng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'no_show' => 'Khách không đến',
            default => 'Không xác định'
        };

        $title = 'Thay đổi trạng thái đặt phòng';
        $message = "Đặt phòng #{$bookingData['booking_id']} đã chuyển sang trạng thái: {$statusText}";

        return $this->createNotification(
            'booking_status_changed',
            $title,
            $message,
            array_merge($bookingData, ['old_status' => $oldStatus, 'new_status' => $newStatus]),
            'normal',
            'fas fa-exchange-alt',
            'warning'
        );
    }

    // ==================== EVENT HANDLER METHODS ====================

    /**
     * Xử lý sự kiện booking được tạo
     */
    public function onBookingCreated(Booking $booking): void
    {
        // Tạo ghi chú hệ thống
        $this->createSystemNote($booking->id, 'Đặt phòng mới được tạo', 'system');

        // Tạo thông báo admin
        $this->createBookingNotification($booking->toArray());
    }

    /**
     * Xử lý sự kiện booking được cập nhật
     */
    public function onBookingUpdated(Booking $booking, array $changes): void
    {
        $changeText = [];
        foreach ($changes as $field => $value) {
            $changeText[] = ucfirst($field) . ': ' . $value;
        }

        $this->createSystemNote($booking->id, 'Cập nhật thông tin: ' . implode(', ', $changeText), 'system');
    }

    /**
     * Xử lý sự kiện booking bị hủy
     */
    public function onBookingCancelled(Booking $booking, string $reason = 'Đã hủy'): void
    {
        $this->createSystemNote($booking->id, $reason, 'system');
    }

    /**
     * Xử lý sự kiện booking được xác nhận
     */
    public function onBookingConfirmed(Booking $booking): void
    {
        $this->createSystemNote($booking->id, 'Đặt phòng đã được xác nhận', 'system');

        // Gửi email xác nhận đặt phòng
        try {
            $latestPayment = $booking->payments->where('status', 'completed')->first();
            Mail::to($booking->user->email)->send(new BookingConfirmationMail($booking, $latestPayment));
        } catch (\Exception $e) {
            Log::error('Failed to send booking confirmation email: ' . $e->getMessage());
        }
    }

    /**
     * Xử lý sự kiện booking check-in
     */
    public function onBookingCheckedIn(Booking $booking): void
    {
        $this->createSystemNote($booking->id, 'Khách đã nhận phòng', 'system');
    }

    /**
     * Xử lý sự kiện booking check-out
     */
    public function onBookingCheckedOut(Booking $booking): void
    {
        $this->createSystemNote($booking->id, 'Khách đã trả phòng', 'system');
    }

    /**
     * Xử lý sự kiện booking hoàn thành
     */
    public function onBookingCompleted(Booking $booking): void
    {
        $this->createSystemNote($booking->id, 'Đặt phòng đã hoàn thành', 'system');
    }

    /**
     * Xử lý sự kiện booking no-show
     */
    public function onBookingNoShow(Booking $booking): void
    {
        $this->createSystemNote($booking->id, 'Khách không đến (No-show)', 'system');
    }

    /**
     * Tạo ghi chú hệ thống
     */
    private function createSystemNote(int $bookingId, string $content, string $type = 'system'): void
    {
        \App\Models\BookingNote::create([
            'booking_id' => $bookingId,
            'user_id' => 1, // Admin user ID 1
            'content' => $content,
            'type' => $type,
            'visibility' => 'internal',
            'is_internal' => true,
        ]);
    }

    /**
     * Tạo thông báo admin cho booking được tạo
     */
    public function notifyBookingCreated(Booking $booking): void
    {
        $this->createBookingNotification($booking->toArray());
    }

    /**
     * Tạo thông báo admin cho thay đổi trạng thái booking
     */
    public function notifyBookingStatusChanged(Booking $booking, string $oldStatus, string $newStatus): void
    {
        $this->createStatusChangeNotification($booking->toArray(), $oldStatus, $newStatus);
    }

    /**
     * Tạo thông báo admin cho booking bị hủy
     */
    public function notifyBookingCancelled(Booking $booking, string $reason): void
    {
        $this->createNotification(
            'booking_cancelled',
            'Đặt phòng bị hủy',
            "Đặt phòng #{$booking->booking_id} đã bị hủy: {$reason}",
            $booking->toArray(),
            'high',
            'fas fa-times-circle',
            'danger'
        );
    }

    /**
     * Xử lý thay đổi trạng thái booking
     */
    private function handleStatusChange(Booking $booking, string $oldStatus, string $newStatus): void
    {
        // Tạo ghi chú hệ thống
        $statusText = match ($newStatus) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã nhận phòng',
            'checked_out' => 'Đã trả phòng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'no_show' => 'Khách không đến',
            default => 'Không xác định'
        };

        $oldStatusText = match ($oldStatus) {
            'pending' => 'Chờ xác nhận',
            'confirmed' => 'Đã xác nhận',
            'checked_in' => 'Đã nhận phòng',
            'checked_out' => 'Đã trả phòng',
            'completed' => 'Hoàn thành',
            'cancelled' => 'Đã hủy',
            'no_show' => 'Khách không đến',
            default => 'Không xác định'
        };

        $noteContent = "Thay đổi trạng thái từ '{$oldStatusText}' sang '{$statusText}'";
        $this->createSystemNote($booking->id, $noteContent, 'system');

        // Ngừng tạo thông báo admin cho thay đổi trạng thái booking theo yêu cầu
        // $this->createStatusChangeNotification($booking->toArray(), $oldStatus, $newStatus);

        // Tự động xử lý các giao dịch pending quá hạn
        $this->cleanExpiredPendingPayments($booking);

        // Gọi các event handler tương ứng
        switch ($newStatus) {
            case 'confirmed':
                $this->onBookingConfirmed($booking);
                break;
            case 'checked_in':
                $this->onBookingCheckedIn($booking);
                break;
            case 'checked_out':
                $this->onBookingCheckedOut($booking);
                break;
            case 'completed':
                $this->onBookingCompleted($booking);
                break;
            case 'cancelled':
                $this->onBookingCancelled($booking, 'Đã hủy bởi admin');
                break;
            case 'no_show':
                $this->onBookingNoShow($booking);
                break;
        }
    }

    /**
     * Xóa các giao dịch pending quá 30 phút cho regular booking
     */
    private function cleanExpiredPendingPayments(Booking $booking): void
    {
        $thirtyMinutesAgo = now()->subMinutes(30);
        
        $expiredPayments = \App\Models\Payment::where('booking_id', $booking->id)
            ->where('status', 'pending')
            ->where('created_at', '<', $thirtyMinutesAgo)
            ->get();

        foreach ($expiredPayments as $expiredPayment) {
            $expiredPayment->delete();
        }

        if ($expiredPayments->count() > 0) {
            Log::info('Cleaned expired pending payments for regular booking', [
                'booking_id' => $booking->id,
                'count' => $expiredPayments->count()
            ]);
        }
    }

    /**
     * Tự động xử lý các giao dịch chờ xác nhận khi đã có payment completed
     */
    public function processPendingPayments(Booking $booking): void
    {
        // Kiểm tra xem có payment completed nào không
        $hasCompletedPayment = $booking->payments->where('status', 'completed')->count() > 0;
        
        if ($hasCompletedPayment) {
            // Tự động xử lý các giao dịch chuyển khoản chờ xác nhận
            $pendingBankTransfers = \App\Models\Payment::where('booking_id', $booking->id)
                ->where('method', 'bank_transfer')
                ->where('status', 'pending')
                ->get();

            foreach ($pendingBankTransfers as $pendingPayment) {
                $pendingPayment->update([
                    'status' => 'completed',
                    'notes' => 'Tự động xác nhận khi admin thu tiền bổ sung',
                    'admin_id' => Auth::id(),
                    'completed_at' => now(),
                ]);
            }

            // Xóa các giao dịch pending quá 30 phút
            $this->cleanExpiredPendingPayments($booking);
        }
    }
}
