<?php 
namespace App\Interfaces\Services\Admin;

interface AdminRoomTypeServiceInterface
{
    public function getAllRoomTypes();
    public function getRoomType($id);
    public function createRoomType($data);
    public function updateRoomType($id, $data);
    public function deleteRoomType($id);
}