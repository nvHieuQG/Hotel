<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Hiển thị form đăng ký
     */
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    /**
     * Xử lý đăng ký
     */
    public function register(Request $request)
    {
        try {
            // Sử dụng service để xác thực và tạo tài khoản
            $user = $this->authService->register($request->all());
            
            // Đăng nhập sau khi đăng ký
            Auth::login($user);
            
            return redirect()->route('verification.notice');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        }
    }
}
