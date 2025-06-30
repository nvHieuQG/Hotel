<?php

namespace App\Interfaces\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface
{
    /**
     * Lấy tất cả đặt phòng của người dùng
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection;

    /**
     * Lấy các booking đã hoàn thành và chưa đánh giá của người dùng
     *
     * @param int $userId
     * @return Collection
     */
    public function getCompletedBookingsWithoutReview(int $userId): Collection;

    /**
     * Lấy các booking đã hoàn thành của người dùng (có thể đã đánh giá hoặc chưa)
     *
     * @param int $userId
     * @return Collection
     */
    public function getCompletedBookings(int $userId): Collection;

    /**
     * Kiểm tra xem booking có thể đánh giá không
     *
     * @param int $bookingId
     * @param int $userId
     * @return bool
     */
    public function canBeReviewed(int $bookingId, int $userId): bool;

    /**
     * Tạo đặt phòng mới
     *
     * @param array $data
     * @return Booking
     */
    public function create(array $data): Booking;

    /**
     * Tìm đặt phòng theo ID
     *
     * @param int $id
     * @param int $userId
     * @return Booking|null
     */
    public function findByIdAndUserId(int $id, int $userId): ?Booking;

    /**
     * Cập nhật đặt phòng
     *
     * @param Booking $booking
     * @param array $data
     * @return bool
     */
    public function update(Booking $booking, array $data): bool;

    /**
     * Lấy chi tiết đặt phòng theo ID
     *
     * @param int $id
     * @return Booking|null
     */
    public function getDetailById(int $id): ?Booking;
}