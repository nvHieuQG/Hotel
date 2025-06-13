<?php 

namespace App\Interfaces\Repositories\Admin;

use Illuminate\Database\Eloquent\Collection;

interface AdminRoomRepositoryInterface
{
    public function getAll($status = null);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
    
    /**
     * Lấy tất cả loại phòng
     *
     * @return Collection
     */
    public function getAllRoomTypes(): Collection;
}