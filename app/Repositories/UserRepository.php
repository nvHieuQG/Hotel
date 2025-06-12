<?php

namespace App\Repositories;

use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    protected $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /**
     * Tạo người dùng mới
     *
     * @param array $data
     * @return User
     */
    public function create(array $data): User
    {
        return $this->model->create([
            'name' => $data['name'],
            'username' => $data['username'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone'] ?? null,
            'role_id' => $data['role_id'] ?? 2, // Mặc định là khách hàng
        ]);
    }

    /**
     * Tìm người dùng theo email
     *
     * @param string $email
     * @return User|null
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Tìm người dùng theo username
     *
     * @param string $username
     * @return User|null
     */
    public function findByUsername(string $username): ?User
    {
        return $this->model->where('username', $username)->first();
    }
    
    /**
     * Tìm người dùng theo ID
     *
     * @param int $id
     * @return User|null
     */
    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }
    
    /**
     * Cập nhật thông tin người dùng
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        // Xử lý mật khẩu nếu có
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        $user->update($data);
        return $user->fresh();
    }
    
    /**
     * Lấy tất cả người dùng
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->model->all();
    }

    /**
     * Cập nhật mật khẩu mới
     *
     * @param int $userId
     * @param string $password
     * @return bool
     */
    public function updatePassword(int $userId, string $password): bool
    {
        $user = $this->model->find($userId);
        if ($user) {
            $user->password = Hash::make($password);
            $user->reset_token = null;
            $user->save();
            return true;
        }
        return false;
    }

    /**
     * Cập nhật reset token
     *
     * @param int $userId
     * @param string $token
     * @return bool
     */
    public function updateResetToken(int $userId, string $token): bool
    {
        $user = $this->model->find($userId);
        if ($user) {
            $user->reset_token = $token;
            $user->save();
            return true;
        }
        return false;
    }

    /**
     * Tìm user theo reset token
     *
     * @param string $token
     * @return User|null
     */
    public function findByResetToken(string $token): ?User
    {
        return $this->model->where('reset_token', $token)->first();
    }
} 