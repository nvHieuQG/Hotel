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

            //trả về view index
            return redirect()->route('index');
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
