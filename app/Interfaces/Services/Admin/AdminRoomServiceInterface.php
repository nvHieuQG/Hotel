<?php

namespace App\Interfaces\Services\Admin;

use Illuminate\Database\Eloquent\Collection;

interface AdminRoomServiceInterface
{
    public function getAllRooms($status = null);
    public function getRoom($id);
    
    /**
     * Lấy tất cả loại phòng
     *
     * @return Collection
     */
    public function getRoomTypes(): Collection;
    
    /**
     * Tạo phòng mới
     *
     * @param array $data
     * @return mixed
     */
    public function createRoom(array $data);
    
    /**
     * Cập nhật phòng
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateRoom($id, array $data);
    
    /**
     * Xóa phòng
     *
     * @param int $id
     * @return array
     */
    public function deleteRoom($id): array;
}