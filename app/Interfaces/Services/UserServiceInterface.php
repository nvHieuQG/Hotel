<?php

namespace App\Interfaces\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserServiceInterface
{
    /**
     * Lấy tất cả người dùng
     *
     * @return Collection
     */
    public function getAll(): Collection;

    /**
     * Phân trang người dùng với bộ lọc
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Tìm người dùng theo ID
     *
     * @param int $id
     * @return mixed
     */
    public function findById(int $id);

    /**
     * Cập nhật thông tin người dùng
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser(int $id, array $data): bool;

    /**
     * Xóa người dùng
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool;

    /**
     * Tạo người dùng mới
     *
     * @param array $data
     * @return mixed
     */
    public function createUser(array $data);
}