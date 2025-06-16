<?php 

namespace App\Services\Admin;
use App\Interfaces\Services\Admin\AdminRoomTypeServiceInterface;
use App\Interfaces\Repositories\Admin\AdminRoomTypeRepositoryInterface;

class AdminRoomTypeService implements AdminRoomTypeServiceInterface{
    protected $roomTypeRepository;

    public function __construct(AdminRoomTypeRepositoryInterface $roomTypeRepository){
        $this->roomTypeRepository = $roomTypeRepository;
    }

    public function getAllRoomTypes(){
        return $this->roomTypeRepository->getAll();
    }

    public function getRoomType($id){
        return $this->roomTypeRepository->find($id);
    }

    public function createRoomType($data){
        return $this->roomTypeRepository->create($data);
    }

    public function updateRoomType($id, $data){
        return $this->roomTypeRepository->update($id, $data);
    }

    public function deleteRoomType($id){
        return $this->roomTypeRepository->delete($id);
    }
}