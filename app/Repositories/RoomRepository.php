<?php

namespace App\Repositories;

use App\Models\Room;
use App\Interfaces\Repositories\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class RoomRepository implements RoomRepositoryInterface
{
    protected Room $model;

    public function __construct(Room $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Room
    {
        return $this->model->find($id);
    }

    public function newQuery(): Builder 
    {
        return $this->model->newQuery();
    }

    public function search(array $filters): Collection 
    {
        return $this->model->newQuery()->get(); 
    }
}
