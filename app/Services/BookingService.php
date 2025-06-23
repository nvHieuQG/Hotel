<?php

namespace App\Services;

use App\Interfaces\Repositories\BookingRepositoryInterface;
use App\Interfaces\Repositories\RoomRepositoryInterface;
use App\Interfaces\Services\BookingServiceInterface;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class BookingService implements BookingServiceInterface
{
    protected $bookingRepository;
    protected $roomRepository;

    public function __construct(
        BookingRepositoryInterface $bookingRepository,
        RoomRepositoryInterface $roomRepository
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->roomRepository = $roomRepository;
    }

    /**
     * Lấy thông tin trang đặt phòng
     *
     * @return array
     */
    public function getBookingPageData(): array
    {
        $user = Auth::user();

        // Kiểm tra người dùng đã xác thực email chưa
        if ($user->email_verified_at === null) {
            throw ValidationException::withMessages([
                'email' => ['Bạn cần xác thực email trước khi đặt phòng.'],
            ]);
        }

        // Lấy danh sách phòng
        $rooms = $this->roomRepository->getAll();

        return [
            'user' => $user,
            'rooms' => $rooms
        ];
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
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests' => 'required|integer|min:1',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Tính toán tổng tiền
        $room = $this->roomRepository->findById($data['room_id']);
        $checkIn = new \DateTime($data['check_in']);
        $checkOut = new \DateTime($data['check_out']);
        $nights = $checkIn->diff($checkOut)->days;
        $totalPrice = $room->price * $nights;

        // Tạo booking ID duy nhất
        $bookingId = 'BK' . date('ymd') . strtoupper(Str::random(5));

        // Chuẩn bị dữ liệu
        $bookingData = [
            'user_id' => Auth::id(),
            'booking_id' => $bookingId,
            'room_id' => $data['room_id'],
            'check_in_date' => $data['check_in'],
            'check_out_date' => $data['check_out'],
            'price' => $totalPrice,
            'status' => 'pending'
        ];

        // Tạo đặt phòng
        return $this->bookingRepository->create($bookingData);
    }

    /**
     * Lấy danh sách đặt phòng của người dùng hiện tại
     *
     * @return Collection
     */
    public function getCurrentUserBookings(): Collection
    {
        return $this->bookingRepository->getByUserId(Auth::id());
    }

    /**
     * Hủy đặt phòng
     *
     * @param int $bookingId
     * @return bool
     */
    public function cancelBooking(int $bookingId): bool
    {
        // Tìm đặt phòng
        $booking = $this->bookingRepository->findByIdAndUserId($bookingId, Auth::id());

        // Nếu không tìm thấy hoặc không phải của người dùng hiện tại
        if (!$booking) {
            throw ValidationException::withMessages([
                'booking' => ['Không tìm thấy đặt phòng.'],
            ]);
        }

        // Kiểm tra trạng thái
        if ($booking->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Không thể hủy đặt phòng này.'],
            ]);
        }

        // Cập nhật trạng thái
        return $this->bookingRepository->update($booking, ['status' => 'cancelled']);
    }

    public function getBookingDetail(int $id): Booking
    {
        return $this->bookingRepository->getDetailById($id);
    }
}