<?php

namespace App\Services;

use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Interfaces\Services\AuthServiceInterface;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService implements AuthServiceInterface
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Đăng ký người dùng mới
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        $user = $this->userRepository->create($data);
        
        // Gửi email xác nhận
        event(new Registered($user));
        
        return $user;
    }

    /**
     * Đăng nhập người dùng
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     * @throws ValidationException
     */
    public function login(array $credentials, bool $remember = false): bool
    {
        $fieldType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        $loginData = [
            $fieldType => $credentials['login'],
            'password' => $credentials['password']
        ];
        
        if (!Auth::attempt($loginData, $remember)) {
            throw ValidationException::withMessages([
                'login' => ['Thông tin đăng nhập không chính xác.'],
            ]);
        }
        
        return true;
    }

    /**
     * Đăng xuất người dùng
     *
     * @return void
     */
    public function logout(): void
    {
        Auth::logout();
        
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
    
    /**
     * Lấy thông tin người dùng hiện tại
     *
     * @return User|null
     */
    public function getCurrentUser(): ?User
    {
        return Auth::user();
    }
    
    /**
     * Kiểm tra người dùng có vai trò cụ thể hay không
     *
     * @param User $user
     * @param string $roleName
     * @return bool
     */
    public function hasRole(User $user, string $roleName): bool
    {
        return $user->hasRole($roleName);
    }
} 