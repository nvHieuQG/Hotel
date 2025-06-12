<?php 

namespace App\Repositories\Admin;
use App\Models\RoomType;
use App\Interfaces\Repositories\Admin\AdminRoomTypeRepositoryInterface;

class AdminRoomTypeRepository implements AdminRoomTypeRepositoryInterface {
    public function getAll(){
        return RoomType::paginate(10);
    }

    public function find($id){
        return RoomType::findOrFail($id);
    }

    public function create(array $data){
        return RoomType::create($data);
    }

    public function update($id, array $data){
        $roomType = RoomType::findOrFail($id);
        $roomType->update($data);
        return $roomType;
    }

    public function delete($id){
        return RoomType::destroy($id);
    }
}