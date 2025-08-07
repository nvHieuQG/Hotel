<?php

namespace App\Interfaces\Repositories;

use App\Models\Booking;
use App\Models\RoomChange;
use Illuminate\Database\Eloquent\Collection;

interface RoomChangeRepositoryInterface
{
    /**
     * Tạo yêu cầu đổi phòng mới
     */
    public function create(array $data): RoomChange;

    /**
     * Lấy danh sách yêu cầu đổi phòng theo trạng thái
     */
    public function getByStatus(string $status): Collection;

    /**
     * Lấy danh sách yêu cầu đổi phòng cho admin
     */
    public function getForAdmin(array $filters = []): Collection;

    /**
     * Lấy yêu cầu đổi phòng theo ID
     */
    public function findById(int $id): ?RoomChange;

    /**
     * Cập nhật trạng thái yêu cầu đổi phòng
     */
    public function updateStatus(int $id, string $status, array $data = []): bool;

    /**
     * Lấy yêu cầu đổi phòng đang chờ duyệt của booking
     */
    public function getPendingByBooking(int $bookingId): ?RoomChange;

    /**
     * Lấy lịch sử đổi phòng của booking
     */
    public function getHistoryByBooking(int $bookingId): Collection;

    /**
     * Kiểm tra xem booking có yêu cầu đổi phòng đang chờ duyệt không
     */
    public function hasPendingRequest(int $bookingId): bool;

    /**
     * Lấy danh sách phòng có thể đổi cho booking
     */
    public function getAvailableRoomsForChange(Booking $booking): Collection;

    /**
     * Lấy danh sách loại phòng có thể đổi cho booking
     */
    public function getAvailableRoomTypesForChange(Booking $booking): Collection;

    /**
     * Tính toán chênh lệch giá khi đổi phòng
     */
    public function calculatePriceDifference(Booking $booking, int $newRoomId): float;

    /**
     * Tính toán chênh lệch giá khi đổi loại phòng
     */
    public function calculatePriceDifferenceByRoomType(Booking $booking, int $newRoomTypeId): float;

    /**
     * Lấy phòng trống đầu tiên của loại phòng cụ thể
     */
    public function getAvailableRoomByType(Booking $booking, int $roomTypeId): ?\App\Models\Room;
} 