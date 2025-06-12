<?php

namespace App\Interfaces\Services\Admin;

interface AdminRoomServiceInterface
{
    public function getAllRooms($status = null);
    public function getRoom($id);
    public function createRoom($data);
    public function updateRoom($id, $data);
    public function deleteRoom($id);
}