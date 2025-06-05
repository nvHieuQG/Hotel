<?php

namespace App\Services\Admin;

use App\Interfaces\Repositories\Admin\AdminBookingRepositoryInterface;
use App\Interfaces\Repositories\RoomRepositoryInterface;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Interfaces\Services\Admin\AdminBookingServiceInterface;
use App\Models\Booking;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

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

    /**
     * Lấy danh sách đặt phòng có phân trang
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getBookingsWithPagination(Request $request): LengthAwarePaginator
    {
        $filters = [
            'status' => $request->get('status')
        ];
        
        return $this->adminBookingRepository->getAllWithPagination($filters);
    }
    
    /**
     * Lấy chi tiết đặt phòng
     *
     * @param int $id
     * @return Booking
     */
    public function getBookingDetails(int $id): Booking
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
            'status' => 'required|in:pending,confirmed,completed,cancelled,no-show',
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
        // Xác thực dữ liệu
        $validator = Validator::make($data, [
            'user_id' => 'required|exists:users,id',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'required|in:pending,confirmed,completed,cancelled,no-show',
        ]);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        $booking = $this->adminBookingRepository->findById($id);
        
        if (!$booking) {
            throw new \Exception('Không tìm thấy đặt phòng');
        }
        
        // Tính lại tổng tiền nếu thay đổi phòng hoặc ngày
        if ($booking->room_id != $data['room_id'] || 
            $booking->check_in_date != $data['check_in_date'] || 
            $booking->check_out_date != $data['check_out_date']) {
            
            $room = $this->roomRepository->findById($data['room_id']);
            $checkIn = new \DateTime($data['check_in_date']);
            $checkOut = new \DateTime($data['check_out_date']);
            $nights = $checkIn->diff($checkOut)->days;
            $totalPrice = $room->price * $nights;
            
            $data['price'] = $totalPrice;
        }
        
        // Cập nhật đặt phòng
        $this->adminBookingRepository->update($booking, $data);
        
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
        return $this->adminBookingRepository->updateStatus($id, $status);
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
        
        // Đếm số đặt phòng đang chờ xác nhận
        $pendingBookings = $this->adminBookingRepository->countByStatus('pending');
        
        // Lấy danh sách đặt phòng gần đây
        $recentBookings = $this->adminBookingRepository->getRecent(5);
        
        // Thống kê theo trạng thái
        $statusCounts = [
            'pending' => $this->adminBookingRepository->countByStatus('pending'),
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
        
        // Tính toán tổng doanh thu
        $totalRevenue = $bookings->sum('price');
        
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
} 