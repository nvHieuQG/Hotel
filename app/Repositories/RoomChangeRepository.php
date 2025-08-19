<?php

namespace App\Repositories;

use App\Interfaces\Repositories\RoomChangeRepositoryInterface;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomChange;
use App\Models\RoomType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RoomChangeRepository implements RoomChangeRepositoryInterface
{
    /**
     * Tạo yêu cầu đổi phòng mới
     */
    public function create(array $data): RoomChange
    {
        return RoomChange::create($data);
    }

    /**
     * Lấy danh sách yêu cầu đổi phòng theo trạng thái
     */
    public function getByStatus(string $status): Collection
    {
        return RoomChange::where('status', $status)
            ->with(['booking', 'oldRoom', 'newRoom', 'requestedBy', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy danh sách yêu cầu đổi phòng cho admin
     */
    public function getForAdmin(array $filters = []): Collection
    {
        $query = RoomChange::with(['booking', 'oldRoom', 'newRoom', 'requestedBy', 'approvedBy']);

        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Lấy yêu cầu đổi phòng theo ID
     */
    public function findById(int $id): ?RoomChange
    {
        return RoomChange::with(['booking', 'oldRoom', 'newRoom', 'requestedBy', 'approvedBy'])->find($id);
    }

    /**
     * Cập nhật trạng thái yêu cầu đổi phòng
     */
    public function updateStatus(int $id, string $status, array $data = []): bool
    {
        Log::info('RoomChangeRepository: Starting updateStatus', [
            'id' => $id,
            'status' => $status,
            'data' => $data
        ]);

        $roomChange = RoomChange::find($id);
        if (!$roomChange) {
            Log::warning('RoomChangeRepository: Room change not found', ['id' => $id]);
            return false;
        }

        Log::info('RoomChangeRepository: Found room change', [
            'id' => $id,
            'current_status' => $roomChange->status,
            'new_status' => $status
        ]);

        $updateData = ['status' => $status];

        if ($status === 'approved') {
            $updateData['approved_at'] = now();
            $updateData['approved_by'] = Auth::id();
        } elseif ($status === 'completed') {
            $updateData['completed_at'] = now();
        }

        if (isset($data['admin_note'])) {
            $updateData['admin_note'] = $data['admin_note'];
        }

        // Persist payment_status if provided by service (e.g., set to 'pending' or 'not_required')
        if (isset($data['payment_status'])) {
            $updateData['payment_status'] = $data['payment_status'];
        }

        Log::info('RoomChangeRepository: Update data', [
            'id' => $id,
            'update_data' => $updateData
        ]);

        $result = $roomChange->update($updateData);
        
        Log::info('RoomChangeRepository: Update result', [
            'id' => $id,
            'result' => $result
        ]);

        return $result;
    }

    /**
     * Lấy yêu cầu đổi phòng đang chờ duyệt của booking
     */
    public function getPendingByBooking(int $bookingId): ?RoomChange
    {
        return RoomChange::where('booking_id', $bookingId)
            ->where('status', 'pending')
            ->with(['oldRoom', 'newRoom'])
            ->first();
    }

    /**
     * Lấy lịch sử đổi phòng của booking
     */
    public function getHistoryByBooking(int $bookingId): Collection
    {
        return RoomChange::where('booking_id', $bookingId)
            ->with(['oldRoom', 'newRoom', 'requestedBy', 'approvedBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Kiểm tra xem booking có yêu cầu đổi phòng đang chờ duyệt không
     */
    public function hasPendingRequest(int $bookingId): bool
    {
        return RoomChange::where('booking_id', $bookingId)
            ->where('status', 'pending')
            ->exists();
    }

    /**
     * Lấy danh sách phòng có thể đổi cho booking
     */
    public function getAvailableRoomsForChange(Booking $booking): Collection
    {
        $currentRoom = $booking->room;
        $checkInDate = $booking->check_in_date;
        $checkOutDate = $booking->check_out_date;

        // Lấy tất cả phòng khả dụng (không giới hạn loại phòng), trừ chính phòng hiện tại
        $availableRooms = Room::where('id', '!=', $currentRoom->id)
            ->where('status', 'available')
            ->whereDoesntHave('bookings', function ($query) use ($checkInDate, $checkOutDate) {
                $query->where(function ($q) use ($checkInDate, $checkOutDate) {
                    $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                        ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                        ->orWhere(function ($subQ) use ($checkInDate, $checkOutDate) {
                            $subQ->where('check_in_date', '<=', $checkInDate)
                                ->where('check_out_date', '>=', $checkOutDate);
                        });
                });
            })
            ->with(['roomType'])
            ->get();

        return $availableRooms;
    }

    /**
     * Tính toán chênh lệch giá khi đổi phòng
     */
    public function calculatePriceDifference(Booking $booking, int $newRoomId): float
    {
        $newRoom = Room::find($newRoomId);
        if (!$newRoom) {
            return 0;
        }

        // Tính số đêm (giống logic booking ban đầu)
        $checkInDate = $booking->check_in_date->format('Y-m-d');
        $checkOutDate = $booking->check_out_date->format('Y-m-d');
        $nights = (new \DateTime($checkInDate))->diff(new \DateTime($checkOutDate))->days;
        if ($nights < 1) $nights = 1;

        // Lấy giá phòng mới theo đêm
        $newRoomPricePerNight = $newRoom->price ?? $newRoom->roomType->price ?? 0;
        $newRoomTotalPrice = $newRoomPricePerNight * $nights;

        // Lấy tổng tiền đã thanh toán cho booking (đã tính theo đêm)
        $oldRoomTotalPrice = $booking->price;

        // Tính chênh lệch: Tổng tiền phòng mới - Tổng tiền đã thanh toán
        return $newRoomTotalPrice - $oldRoomTotalPrice;
    }

    /**
     * Lấy phòng trống đầu tiên của loại phòng cụ thể
     */
    public function getAvailableRoomByType(Booking $booking, int $roomTypeId): ?Room
    {
        $checkInDate = $booking->check_in_date;
        $checkOutDate = $booking->check_out_date;

        return Room::where('room_type_id', $roomTypeId)
            ->where('status', 'available')
            ->whereDoesntHave('bookings', function ($query) use ($checkInDate, $checkOutDate) {
                $query->where(function ($q) use ($checkInDate, $checkOutDate) {
                    $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                        ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                        ->orWhere(function ($subQ) use ($checkInDate, $checkOutDate) {
                            $subQ->where('check_in_date', '<=', $checkInDate)
                                ->where('check_out_date', '>=', $checkOutDate);
                        });
                });
            })
            ->first();
    }

    /**
     * Lấy danh sách loại phòng có thể đổi cho booking
     */
    public function getAvailableRoomTypesForChange(Booking $booking): Collection
    {
        $currentRoomType = $booking->room->roomType;
        $checkInDate = $booking->check_in_date;
        $checkOutDate = $booking->check_out_date;

        // Lấy tất cả loại phòng có ít nhất một phòng khả dụng trong khoảng ngày (không giới hạn theo cấp độ loại phòng)
        $availableRoomTypes = RoomType::query()
            ->whereHas('rooms', function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('status', 'available')
                    ->whereDoesntHave('bookings', function ($subQuery) use ($checkInDate, $checkOutDate) {
                        $subQuery->where(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                                ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                                ->orWhere(function ($subQ) use ($checkInDate, $checkOutDate) {
                                    $subQ->where('check_in_date', '<=', $checkInDate)
                                        ->where('check_out_date', '>=', $checkOutDate);
                                });
                        });
                    });
            })
            ->with(['rooms' => function ($query) use ($checkInDate, $checkOutDate) {
                $query->where('status', 'available')
                    ->whereDoesntHave('bookings', function ($subQuery) use ($checkInDate, $checkOutDate) {
                        $subQuery->where(function ($q) use ($checkInDate, $checkOutDate) {
                            $q->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                                ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                                ->orWhere(function ($subQ) use ($checkInDate, $checkOutDate) {
                                    $subQ->where('check_in_date', '<=', $checkInDate)
                                        ->where('check_out_date', '>=', $checkOutDate);
                                });
                        });
                    });
            }])
            ->get();

        return $availableRoomTypes;
    }

    /**
     * Tính toán chênh lệch giá khi đổi loại phòng
     */
    public function calculatePriceDifferenceByRoomType(Booking $booking, int $newRoomTypeId): float
    {
        $newRoomType = RoomType::find($newRoomTypeId);
        if (!$newRoomType) {
            return 0;
        }

        // Tính số đêm (giống logic booking ban đầu)
        $checkInDate = $booking->check_in_date->format('Y-m-d');
        $checkOutDate = $booking->check_out_date->format('Y-m-d');
        $nights = (new \DateTime($checkInDate))->diff(new \DateTime($checkOutDate))->days;
        if ($nights < 1) $nights = 1;

        // Lấy giá loại phòng mới theo đêm
        $newRoomTypePricePerNight = $newRoomType->price ?? 0;
        $newRoomTypeTotalPrice = $newRoomTypePricePerNight * $nights;

        // Lấy tổng tiền đã thanh toán cho booking (đã tính theo đêm)
        $oldRoomTotalPrice = $booking->price;

        // Tính chênh lệch: Tổng tiền loại phòng mới - Tổng tiền đã thanh toán
        return $newRoomTypeTotalPrice - $oldRoomTotalPrice;
    }

} 