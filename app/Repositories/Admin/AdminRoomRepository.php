<?php

namespace App\Repositories\Admin;

use App\Models\Room;
use App\Models\RoomType;
use App\Interfaces\Repositories\Admin\AdminRoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class AdminRoomRepository implements AdminRoomRepositoryInterface
{
    public function getAll($status = null)
    {
        $query = Room::with(['roomType', 'bookings']);
    
        if ($status) {
            if ($status === 'booked') {
                // Lọc phòng có booking đang pending hoặc confirmed
                $query->whereHas('bookings', function($q) {
                    $q->whereIn('status', ['pending', 'confirmed']);
                });
            } else {
                // Lọc theo trạng thái phòng
                $query->where('status', $status);
            }
        }
    
        return $query->paginate(10);
    }

    public function find($id)
    {
        return Room::with(['roomType'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Room::create($data);
    }

    public function update($id, array $data)
    {
        $room = Room::findOrFail($id);
        $room->update($data);
        return $room;
    }

    public function delete($id)
    {
        
        $room = Room::with('bookings')->findOrFail($id);

        // Kiểm tra xem phòng có booking đang pending hoặc confirmed không
            if ($room->bookings()->whereIn('status', ['pending', 'confirmed'])->count() > 0) {
                return [
                    'success' => false,
                    'message' => 'Không thể xóa phòng vì đang có đơn đặt phòng đang xử lý!'
                ];
            }

        $room->delete();

        return [
            'success' => true,
            'message' => 'Xóa phòng thành công!'
        ];
    }

    /**
     * Lấy tất cả loại phòng
     *
     * @return Collection
     */
    public function getAllRoomTypes(): Collection
    {
        return RoomType::all();
    }
}