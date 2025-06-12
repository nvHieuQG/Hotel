<?php

namespace App\Interfaces\Repositories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

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

    /**
     * Tạo một truy vấn mới
     *
     * @return Builder
     */
    public function newQuery(): Builder;

    /**
     * Tìm kiếm phòng theo các bộ lọc
     *
     * @param array $filters
     * @return Collection
     */
    public function search(array $filters);
}
