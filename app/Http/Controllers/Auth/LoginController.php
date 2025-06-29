<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Hiển thị form đăng nhập
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8'],
            'remember' => ['nullable', 'in:on,1,true'],
        ]);

        try {
            $this->authService->login(
                $validated,
                $request->input('remember') === 'on'
            );

            $request->session()->regenerate();

            // Kiểm tra role của user để chuyển hướng
            $user = Auth::user();
            
            if ($user && $user->role) {
                // Nếu user có role admin, super_admin hoặc staff thì chuyển đến admin dashboard
                if (in_array($user->role->name, ['admin', 'super_admin', 'staff'])) {
                    return redirect()->route('admin.dashboard')->with('success', 'Chào mừng bạn đến với trang quản trị!');
                }
            }

            // Nếu không phải admin thì chuyển đến trang chủ
            return redirect()->route('index')->with('success', 'Đăng nhập thành công!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    /**
     * Xử lý đăng xuất
     */
    public function logout(Request $request)
    {
        $this->authService->logout();
        
        return redirect()->route('index');
    }
}
