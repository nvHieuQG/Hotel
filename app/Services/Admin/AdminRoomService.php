<?php

namespace App\Services\Admin;

use App\Repositories\Admin\AdminRoomRepository;
use App\Interfaces\Services\Admin\AdminRoomServiceInterface;
use App\Interfaces\Repositories\Admin\AdminRoomRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Collection;

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

    public function getRoomTypes(): Collection
    {
        return $this->roomRepository->getAllRoomTypes();
    }

    public function createRoom(array $data)
    {
        $this->validateRoomData($data);
        return $this->roomRepository->create($data);
    }

    public function updateRoom($id, array $data)
    {
        $this->validateRoomData($data);
        return $this->roomRepository->update($id, $data);
    }

    public function deleteRoom($id): array
    {
        return $this->roomRepository->delete($id);
    }

    /**
     * Validate dữ liệu phòng
     *
     * @param array $data
     * @throws ValidationException
     */
    protected function validateRoomData(array $data)
{
    $validator = Validator::make($data, [
        'room_type_id' => 'required|exists:room_types,id',
        'room_number' => 'required|string|max:20',
        'status' => 'required|in:available,booked,repair',
        
    ]);

    // Kiểm tra validate cơ bản
    if ($validator->fails()) {
        throw new ValidationException($validator);
    }

    
}
}