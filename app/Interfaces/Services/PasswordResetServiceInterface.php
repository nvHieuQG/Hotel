<?php

namespace App\Interfaces\Services;

interface PasswordResetServiceInterface
{
    /**
     * Gửi link đặt lại mật khẩu qua email
     *
     * @param string $email
     * @return bool
     */
    public function sendResetLink(string $email): bool;

    /**
     * Đặt lại mật khẩu mới
     *
     * @param string $token
     * @param string $password
     * @return bool
     */
    public function resetPassword(string $token, string $password): bool;

    /**
     * Kiểm tra tính hợp lệ của token
     *
     * @param string $token
     * @return bool
     */
    public function validateToken(string $token): bool;
} 