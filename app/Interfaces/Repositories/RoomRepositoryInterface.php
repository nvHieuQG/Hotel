<?php

namespace App\Interfaces\Repositories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

interface RoomRepositoryInterface
{
    /**
     * Lấy tất cả phòng
     *
     * @return Collection
     */
    public function getAll(): Collection;
    
    /**
     * Tìm phòng theo ID
     *
     * @param int $id
     * @return Room|null
     */
    public function findById(int $id): ?Room;
} 