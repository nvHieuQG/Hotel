<?php

namespace App\Services;

use App\Models\Booking;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use App\Interfaces\Services\BookingServiceInterface;
use App\Interfaces\Repositories\RoomRepositoryInterface;
use App\Interfaces\Repositories\BookingRepositoryInterface;
use App\Interfaces\Repositories\RoomTypeRepositoryInterface;
use Illuminate\Support\Facades\Log;

class BookingService implements BookingServiceInterface
{
    protected $bookingRepository;
    protected $roomRepository;
    protected $roomTypeRepository;

    public function __construct(
        BookingRepositoryInterface $bookingRepository,
        RoomRepositoryInterface $roomRepository,
        RoomTypeRepositoryInterface $roomTypeRepository
    ) {
        $this->bookingRepository = $bookingRepository;
        $this->roomRepository = $roomRepository;
        $this->roomTypeRepository = $roomTypeRepository;
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

        // Lấy danh sách loại phòng
        $roomTypes = $this->roomTypeRepository->getAllRoomTypes();

        return [
            'user' => $user,
            'roomTypes' => $roomTypes
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
        // 1. Validation
        $validator = Validator::make($data, [
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guests' => 'required|integer|min:1',
            'phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Ép kiểu về string
        $checkInDate = is_object($data['check_in_date']) ? $data['check_in_date']->format('Y-m-d') : $data['check_in_date'];
        $checkOutDate = is_object($data['check_out_date']) ? $data['check_out_date']->format('Y-m-d') : $data['check_out_date'];

        $checkInDateTime = $checkInDate . ' 14:00:00';
        $checkOutDateTime = $checkOutDate . ' 12:00:00';

        // 3. Kiểm tra phòng trống, tạo booking như cũ nhưng dùng $checkInDateTime, $checkOutDateTime
        $availableRoom = $this->roomRepository->findAvailableRoomByType(
            $data['room_type_id'],
            $checkInDateTime,
            $checkOutDateTime
        );

        if (!$availableRoom) {
            throw ValidationException::withMessages([
                'room' => ['Hiện không còn phòng trống cho loại phòng này trong thời gian đã chọn.'],
            ]);
        }

        // 4. Tính tiền
        $checkInDate = (new \DateTime($checkInDateTime))->format('Y-m-d');
        $checkOutDate = (new \DateTime($checkOutDateTime))->format('Y-m-d');
        $nights = (new \DateTime($checkInDate))->diff(new \DateTime($checkOutDate))->days;
        if ($nights < 1) $nights = 1;
        $totalPrice = $availableRoom->roomType->price * $nights;

        // 5. Tạo booking ID
        $bookingId = 'BK' . date('ymd') . strtoupper(Str::random(5));

        $bookingData = [
            'user_id' => Auth::id(),
            'booking_id' => $bookingId,
            'room_id' => $availableRoom->id,
            'check_in_date' => $checkInDateTime,  // Lưu vào DB dạng datetime
            'check_out_date' => $checkOutDateTime,
            'price' => $totalPrice,
            'status' => 'pending',
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        Log::info([
            'check_in' => $checkInDateTime,
            'check_out' => $checkOutDateTime,
            'nights' => $nights,
            'room_price' => $availableRoom->roomType->price,
            'total_price' => $totalPrice,
        ]);
        return $this->bookingRepository->create($bookingData);
    }

    /**
     * Lấy danh sách đặt phòng của người dùng hiện tại (có phân trang)
     *
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getCurrentUserBookings($perPage = 10)
    {
        return $this->bookingRepository->getByUserIdPaginate(Auth::id(), $perPage);
    }

    /**
     * Lấy các booking đã hoàn thành và chưa đánh giá của người dùng hiện tại
     *
     * @return Collection
     */
    public function getCompletedBookingsWithoutReview(): Collection
    {
        return $this->bookingRepository->getCompletedBookingsWithoutReview(Auth::id());
    }

    /**
     * Lấy các booking đã hoàn thành của người dùng hiện tại
     *
     * @return Collection
     */
    public function getCompletedBookings(): Collection
    {
        return $this->bookingRepository->getCompletedBookings(Auth::id());
    }

    /**
     * Kiểm tra xem booking có thể đánh giá không
     *
     * @param int $bookingId
     * @return bool
     */
    public function canBeReviewed(int $bookingId): bool
    {
        return $this->bookingRepository->canBeReviewed($bookingId, Auth::id());
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

    /**
     * Lấy danh sách đặt phòng theo userId
     *
     * @param int $userId
     * @return Collection
     */
    public function getBookingsByUser(int $userId): Collection
    {
        return $this->bookingRepository->getByUserId($userId);
    }
}
