<?php

namespace App\Interfaces\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * Tạo người dùng mới
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User;

    /**
     * Tìm người dùng theo email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User;

    /**
     * Tìm người dùng theo username
     *
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User;
    
    /**
     * Tìm người dùng theo ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User;
    
    /**
     * Cập nhật thông tin người dùng
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User;
    
    /**
     * Lấy tất cả người dùng
     *
     * @return Collection
     */
    public function getAll(): Collection;
} 