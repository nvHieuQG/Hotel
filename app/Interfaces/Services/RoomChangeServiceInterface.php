<?php

namespace App\Interfaces\Services;

use App\Models\Booking;
use App\Models\RoomChange;
use Illuminate\Database\Eloquent\Collection;

interface RoomChangeServiceInterface
{
    /**
     * Tạo yêu cầu đổi phòng từ khách hàng
     */
    public function createRoomChangeRequest(Booking $booking, array $data): RoomChange;

    /**
     * Duyệt yêu cầu đổi phòng (admin)
     */
    public function approveRoomChange(int $roomChangeId, array $data = []): bool;

    /**
     * Từ chối yêu cầu đổi phòng (admin)
     */
    public function rejectRoomChange(int $roomChangeId, array $data = []): bool;

    /**
     * Hoàn thành đổi phòng (admin)
     */
    public function completeRoomChange(int $roomChangeId): bool;

    /**
     * Lấy danh sách yêu cầu đổi phòng cho admin
     */
    public function getRoomChangesForAdmin(array $filters = []): Collection;

    /**
     * Lấy lịch sử đổi phòng của booking
     */
    public function getRoomChangeHistory(int $bookingId): Collection;

    /**
     * Kiểm tra xem booking có thể đổi phòng không
     */
    public function canChangeRoom(Booking $booking): bool;

    /**
     * Lấy danh sách loại phòng có thể đổi cho booking
     */
    public function getAvailableRoomTypesForChange(Booking $booking): Collection;

    /**
     * Tính toán chênh lệch giá khi đổi loại phòng
     */
    public function calculatePriceDifferenceByRoomType(Booking $booking, int $newRoomTypeId): float;
} 