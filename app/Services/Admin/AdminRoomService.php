<?php

namespace App\Services\Admin;

use App\Repositories\Admin\AdminRoomRepository;
use App\Interfaces\Services\Admin\AdminRoomServiceInterface;
use App\Interfaces\Repositories\Admin\AdminRoomRepositoryInterface;

class AdminRoomService implements AdminRoomServiceInterface
{
    protected $roomRepository;

    public function __construct(AdminRoomRepositoryInterface $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    public function getAllRooms($status = null)
    {
        return $this->roomRepository->getAll($status);
    }

    public function getRoom($id)
    {
        return $this->roomRepository->find($id);
    }

    public function createRoom($data)
    {
        return $this->roomRepository->create($data);
    }

    public function updateRoom($id, $data)
    {
        return $this->roomRepository->update($id, $data);
    }

    public function deleteRoom($id)
    {
        return $this->roomRepository->delete($id);
    }
}