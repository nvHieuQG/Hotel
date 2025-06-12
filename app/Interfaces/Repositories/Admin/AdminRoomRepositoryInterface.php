<?php 


namespace App\Interfaces\Repositories\Admin;

interface AdminRoomRepositoryInterface
{
    public function getAll($status = null);
    public function find($id);
    public function create(array $data);
    public function update($id, array $data);
    public function delete($id);
}