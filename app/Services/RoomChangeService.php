<?php

namespace App\Services;

use App\Interfaces\Repositories\RoomChangeRepositoryInterface;
use App\Interfaces\Services\RoomChangeServiceInterface;
use App\Models\Booking;
use App\Models\RoomChange;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomChangeService implements RoomChangeServiceInterface
{
    public function __construct(
        private RoomChangeRepositoryInterface $roomChangeRepository
    ) {}

    /**
     * Tạo yêu cầu đổi phòng từ khách hàng
     */
    public function createRoomChangeRequest(Booking $booking, array $data): RoomChange
    {
        // Kiểm tra booking có thể đổi phòng không
        if (!$this->canChangeRoom($booking)) {
            throw new \Exception('Booking không thể đổi phòng');
        }

        // Kiểm tra đã có yêu cầu đang chờ duyệt chưa
        if ($this->roomChangeRepository->hasPendingRequest($booking->id)) {
            throw new \Exception('Đã có yêu cầu đổi phòng đang chờ duyệt');
        }

        // Tự động chọn phòng cụ thể từ loại phòng
        $newRoomTypeId = $data['new_room_type_id'];
        $newRoom = $this->roomChangeRepository->getAvailableRoomByType($booking, $newRoomTypeId);
        
        if (!$newRoom) {
            throw new \Exception('Không có phòng trống của loại phòng này');
        }

        // Tính chênh lệch giá
        $priceDifference = $this->calculatePriceDifferenceByRoomType($booking, $newRoomTypeId);

        // Tạo yêu cầu đổi phòng
        $roomChangeData = [
            'booking_id' => $booking->id,
            'old_room_id' => $booking->room_id,
            'new_room_id' => $newRoom->id,
            'reason' => $data['reason'] ?? null,
            'customer_note' => $data['customer_note'] ?? null,
            'price_difference' => $priceDifference,
            'requested_by' => Auth::id(),
            'status' => 'pending',
        ];

        return $this->roomChangeRepository->create($roomChangeData);
    }

    /**
     * Duyệt yêu cầu đổi phòng (admin)
     */
    public function approveRoomChange(int $roomChangeId, array $data = []): bool
    {
        Log::info('RoomChangeService: Starting approval process', [
            'room_change_id' => $roomChangeId,
            'data' => $data
        ]);

        $roomChange = $this->roomChangeRepository->findById($roomChangeId);
        
        Log::info('RoomChangeService: Found room change', [
            'room_change_id' => $roomChangeId,
            'room_change_exists' => $roomChange ? true : false,
            'current_status' => $roomChange ? $roomChange->status : 'not_found'
        ]);

        if (!$roomChange || $roomChange->status !== 'pending') {
            Log::warning('RoomChangeService: Cannot approve room change', [
                'room_change_id' => $roomChangeId,
                'room_change_exists' => $roomChange ? true : false,
                'current_status' => $roomChange ? $roomChange->status : 'not_found'
            ]);
            return false;
        }

        $updateData = [
            'admin_note' => $data['admin_note'] ?? null,
        ];

        // Set payment status based on price difference
        if ($roomChange->price_difference > 0) {
            $updateData['payment_status'] = 'pending';
        } else {
            $updateData['payment_status'] = 'not_required';
        }

        // Apply surcharge (if any) at APPROVE time, then set status to approved.
        return DB::transaction(function () use ($roomChangeId, $roomChange, $updateData) {
            if ($roomChange->price_difference > 0) {
                $booking = $roomChange->booking;
                // Tăng tổng price để tổng tiền tăng đúng với chênh lệch
                $booking->price = ($booking->price ?? 0) + $roomChange->price_difference;
                $booking->surcharge = ($booking->surcharge ?? 0) + $roomChange->price_difference;
                $booking->save();
            }

            $result = $this->roomChangeRepository->updateStatus($roomChangeId, 'approved', $updateData);

            Log::info('RoomChangeService: Update status result', [
                'room_change_id' => $roomChangeId,
                'result' => $result
            ]);

            return $result;
        });
    }

    /**
     * Từ chối yêu cầu đổi phòng (admin)
     */
    public function rejectRoomChange(int $roomChangeId, array $data = []): bool
    {
        $roomChange = $this->roomChangeRepository->findById($roomChangeId);
        if (!$roomChange || $roomChange->status !== 'pending') {
            return false;
        }

        $updateData = [
            'admin_note' => $data['admin_note'] ?? null,
        ];

        $result = $this->roomChangeRepository->updateStatus($roomChangeId, 'rejected', $updateData);

        // No surcharge rollback here; surcharge is only applied upon payment confirmation at reception.

        return $result;
    }

    /**
     * Hoàn thành đổi phòng (admin)
     */
    public function completeRoomChange(int $roomChangeId): bool
    {
        $roomChange = $this->roomChangeRepository->findById($roomChangeId);
        if (!$roomChange || $roomChange->status !== 'approved') {
            return false;
        }

        // Only update booking to new room. Do NOT change any amounts here.
        $booking = $roomChange->booking;
        $booking->update([
            'room_id' => $roomChange->new_room_id,
        ]);

        // Cập nhật trạng thái phòng cũ và mới
        $roomChange->oldRoom->update(['status' => 'available']);
        $roomChange->newRoom->update(['status' => 'booked']);

        return $this->roomChangeRepository->updateStatus($roomChangeId, 'completed');
    }

    /**
     * Lấy danh sách yêu cầu đổi phòng cho admin
     */
    public function getRoomChangesForAdmin(array $filters = []): Collection
    {
        return $this->roomChangeRepository->getForAdmin($filters);
    }

    /**
     * Lấy lịch sử đổi phòng của booking
     */
    public function getRoomChangeHistory(int $bookingId): Collection
    {
        return $this->roomChangeRepository->getHistoryByBooking($bookingId);
    }

    /**
     * Kiểm tra xem booking có thể đổi phòng không
     */
    public function canChangeRoom(Booking $booking): bool
    {
        // Kiểm tra trạng thái booking
        if (in_array($booking->status, ['cancelled', 'completed'])) {
            return false;
        }

        // Kiểm tra thời gian (chỉ cho phép đổi phòng trước ngày check-out)
        if ($booking->check_out_date <= now()) {
            return false;
        }

        return true;
    }

    /**
     * Lấy danh sách loại phòng có thể đổi cho booking
     */
    public function getAvailableRoomTypesForChange(Booking $booking): Collection
    {
        return $this->roomChangeRepository->getAvailableRoomTypesForChange($booking);
    }

    /**
     * Tính toán chênh lệch giá khi đổi loại phòng
     */
    public function calculatePriceDifferenceByRoomType(Booking $booking, int $newRoomTypeId): float
    {
        return $this->roomChangeRepository->calculatePriceDifferenceByRoomType($booking, $newRoomTypeId);
    }
} 