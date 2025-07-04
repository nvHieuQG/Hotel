<?php

namespace App\Interfaces\Services;

use Illuminate\Database\Eloquent\Collection;

interface UserServiceInterface
{
    /**
     * Lấy tất cả người dùng
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Tìm người dùng theo ID
     *
     * @param int $id
     * @return mixed
     */
    public function findById(int $id);
} 