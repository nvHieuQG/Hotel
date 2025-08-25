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

        // Không set payment_status ở đây, để completeRoomChange xử lý sau khi hoàn thành
        // Payment status sẽ được set dựa trên chênh lệch giá thực tế khi hoàn thành

        $result = DB::transaction(function () use ($roomChange, $updateData) {
            // Cập nhật trạng thái thành approved
            $roomChange->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                ...$updateData
            ]);

            return true;
        });

        Log::info('RoomChangeService: Update status result', [
            'room_change_id' => $roomChangeId,
            'result' => $result
        ]);

        return $result;
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

        // Update booking to new room and apply price difference to booking->price (not surcharge)
        $booking = $roomChange->booking;
        DB::transaction(function () use ($booking, $roomChange) {
            // Cộng (hoặc trừ) chênh lệch vào giá phòng để base_room_price phản ánh giá mới
            $booking->price = ($booking->price ?? 0) + ($roomChange->price_difference ?? 0);
            $booking->room_id = $roomChange->new_room_id;
            $booking->save();

            // Cập nhật trạng thái phòng cũ và mới
            $roomChange->oldRoom->update(['status' => 'available']);
            $roomChange->newRoom->update(['status' => 'booked']);

            // Cập nhật payment_status dựa trên chênh lệch giá
            if ($roomChange->price_difference > 0) {
                // Đổi lên phòng đắt hơn - cần thu tiền
                $roomChange->update(['payment_status' => 'pending']);
            } elseif ($roomChange->price_difference < 0) {
                // Đổi xuống phòng rẻ hơn - cần hoàn tiền
                $roomChange->update(['payment_status' => 'refund_pending']);
            } else {
                // Không có chênh lệch giá
                $roomChange->update(['payment_status' => 'not_required']);
            }
        });

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