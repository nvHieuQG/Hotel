<?php

namespace App\Services;

use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Interfaces\Services\AuthServiceInterface;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerifyEmail;

class AuthService implements AuthServiceInterface
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Đăng ký người dùng mới (tạo tài khoản tạm thời chờ xác minh email)
     *
     * @param array $data
     * @return User
     */
    public function register(array $data): User
    {
        // Xác thực dữ liệu
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users', 'regex:/^[a-zA-Z0-9]+$/'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'regex:/^[a-zA-Z0-9@$!%*?&]+$/'],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);
        
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        
        
        // Thêm trường email_verified_at là null để đánh dấu tài khoản chưa xác minh
        $data['email_verified_at'] = null;
        
        // Tạo tài khoản tạm thời (chưa xác minh)
        $user = $this->userRepository->create($data);
        
        // Tạo token xác minh email và gửi email
        $verificationUrl = $this->createVerificationUrl($user);
        $this->sendVerificationEmail($user, $verificationUrl);
        
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
        // Kiểm tra xem đang đăng nhập bằng email hay username
        $fieldType = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        
        // Kiểm tra tài khoản có tồn tại không
        $userExists = false;
        
        if ($fieldType == 'email') {
            $user = $this->userRepository->findByEmail($credentials['login']);
            if (!$user) {
                throw ValidationException::withMessages([
                    'login' => ['Email không tồn tại trong hệ thống.'],
                ]);
            }
            $userExists = true;
        } else {
            $user = $this->userRepository->findByUsername($credentials['login']);
            if (!$user) {
                throw ValidationException::withMessages([
                    'login' => ['Tên đăng nhập không tồn tại trong hệ thống.'],
                ]);
            }
            $userExists = true;
        }
        
        // Nếu tài khoản tồn tại, kiểm tra mật khẩu
        if ($userExists) {
            // Kiểm tra mật khẩu
            if (!Hash::check($credentials['password'], $user->password)) {
                throw ValidationException::withMessages([
                    'password' => ['Mật khẩu không chính xác.'],
                ]);
            }
            
            // Đăng nhập người dùng
            Auth::login($user, $remember);
            return true;
        }
        
        // Không nên đến được đây, nhưng để đảm bảo
        throw ValidationException::withMessages([
            'login' => ['Thông tin đăng nhập không chính xác.'],
        ]);
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

    /**
     * Tạo URL xác minh email
     *
     * @param User $user
     * @return string
     */
    public function createVerificationUrl(User $user): string
    {
        // Tạo token xác minh
        $token = sha1($user->getEmailForVerification() . Str::random(40));
        
        // Lưu token vào database
        DB::table('verification_tokens')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'created_at' => now(),
            'expires_at' => now()->addHours(24), // Token hết hạn sau 24 giờ
        ]);
        
        // Tạo URL xác minh
        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addHours(24),
            [
                'id' => $user->id,
                'hash' => $token, // Sử dụng hash thay vì token để khớp với route mặc định của Laravel
            ]
        );
    }
    
    /**
     * Gửi email xác minh
     *
     * @param User $user
     * @param string $verificationUrl
     * @return void
     */
    public function sendVerificationEmail(User $user, string $verificationUrl): void
    {
        Mail::to($user->email)->send(new VerifyEmail($user, $verificationUrl));
    }
    
    /**
     * Xác minh email người dùng
     *
     * @param int $userId
     * @param string $hash
     * @return bool
     */
    public function verifyEmail(int $userId, string $hash): bool
    {
        // Kiểm tra token có hợp lệ không
        $verificationData = DB::table('verification_tokens')
            ->where('user_id', $userId)
            ->where('token', $hash)
            ->where('expires_at', '>', now())
            ->first();
            
        if (!$verificationData) {
            return false;
        }
        
        // Cập nhật trạng thái xác minh email
        $user = $this->userRepository->findById($userId);
        if ($user) {
            $user->email_verified_at = now();
            $user->save();
            
            // Xóa token đã sử dụng
            DB::table('verification_tokens')->where('user_id', $userId)->delete();
            
            return true;
        }
        
        return false;
    }
} 