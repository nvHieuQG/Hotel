<?php

namespace App\Repositories;

use App\Models\Room;
use App\Interfaces\Repositories\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class RoomRepository implements RoomRepositoryInterface
{
    protected $model;

    public function __construct(Room $model)
    {
        $this->model = $model;
    }

    /**
     * Lấy tất cả phòng
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Tìm phòng theo ID
     *
     * @param int $id
     * @return Room|null
     */
    public function findById(int $id): ?Room
    {
        return $this->model->find($id);
    }
} 