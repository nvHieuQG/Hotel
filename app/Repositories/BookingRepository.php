<?php

namespace App\Repositories;

use App\Models\Booking;
use App\Interfaces\Repositories\BookingRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class BookingRepository implements BookingRepositoryInterface
{
    protected $model;

    public function __construct(Booking $model)
    {
        $this->model = $model;
    }

    /**
     * Lấy tất cả đặt phòng của người dùng
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->with('room.roomType')
            ->latest()
            ->get();
    }

    /**
     * Lấy các booking đã hoàn thành và chưa đánh giá của người dùng
     *
     * @param int $userId
     * @return Collection
     */
    public function getCompletedBookingsWithoutReview(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereDoesntHave('review')
            ->with(['room.roomType'])
            ->latest()
            ->get();
    }

    /**
     * Lấy các booking đã hoàn thành của người dùng (có thể đã đánh giá hoặc chưa)
     *
     * @param int $userId
     * @return Collection
     */
    public function getCompletedBookings(int $userId): Collection
    {
        return $this->model->where('user_id', $userId)
            ->where('status', 'completed')
            ->with(['room.roomType', 'review'])
            ->latest()
            ->get();
    }

    /**
     * Kiểm tra xem booking có thể đánh giá không
     *
     * @param int $bookingId
     * @param int $userId
     * @return bool
     */
    public function canBeReviewed(int $bookingId, int $userId): bool
    {
        $booking = $this->model->where('id', $bookingId)
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereDoesntHave('review')
            ->first();

        return $booking !== null;
    }

    /**
     * Tạo đặt phòng mới
     *
     * @param array $data
     * @return Booking
     */
    public function create(array $data): Booking
    {
        return $this->model->create($data);
    }

    /**
     * Tìm đặt phòng theo ID
     *
     * @param int $id
     * @param int $userId
     * @return Booking|null
     */
    public function findByIdAndUserId(int $id, int $userId): ?Booking
    {
        return $this->model->where('id', $id)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * Cập nhật đặt phòng
     *
     * @param Booking $booking
     * @param array $data
     * @return bool
     */
    public function update(Booking $booking, array $data): bool
    {
        return $booking->update($data);
    }

    public function getDetailById(int $id): ?Booking
    {
        return Booking::with('room.roomType')->findOrFail($id);
    }

    /**
     * Lấy tất cả đặt phòng của người dùng (có phân trang)
     *
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getByUserIdPaginate(int $userId, $perPage = 10)
    {
        return $this->model->where('user_id', $userId)
            ->with('room.roomType')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Kiểm tra xem user đã có booking hoàn thành cho loại phòng này chưa
     *
     * @param int $userId
     * @param int $roomTypeId
     * @return bool
     */
    public function hasUserCompletedBookingForRoomType(int $userId, int $roomTypeId): bool
    {
        return $this->model->where('user_id', $userId)
            ->where('status', 'completed')
            ->whereHas('room', function($query) use ($roomTypeId) {
                $query->where('room_type_id', $roomTypeId);
            })
            ->exists();
    }

    /**
     * Hàm kiểm tra phòng còn trống theo datetime
     */
    public function isRoomAvailable($roomId, $checkInDateTime, $checkOutDateTime)
    {
        return !$this->model->where('room_id', $roomId)
            ->where(function($query) use ($checkInDateTime, $checkOutDateTime) {
                $query->where('check_in_date', '<', $checkOutDateTime)
                      ->where('check_out_date', '>', $checkInDateTime);
            })
            ->exists();
    }
}