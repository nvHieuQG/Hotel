<?php

namespace App\Interfaces\Services;

use App\Models\User;
use Illuminate\Validation\ValidationException;

interface AuthServiceInterface
{
    /**
     * Đăng ký người dùng mới
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User;

    /**
     * Đăng nhập người dùng
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     * @throws ValidationException
     */
    public function login(array $credentials, bool $remember = false): bool;

    /**
     * Đăng xuất người dùng
     *
     * @return void
     */
    public function logout(): void;
    
    /**
     * Lấy thông tin người dùng hiện tại
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User;
    
    /**
     * Kiểm tra người dùng có vai trò cụ thể hay không
     *
     * @param User $user
     * @param string $roleName
     * @return bool
     */
    public function hasRole(User $user, string $roleName): bool;
} 