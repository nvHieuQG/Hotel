<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\PasswordResetServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    protected $passwordResetService;

    public function __construct(PasswordResetServiceInterface $passwordResetService)
    {
        $this->passwordResetService = $passwordResetService;
    }

    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $this->passwordResetService->sendResetLink($request->email);

        return back()->with('status', 'Link đặt lại mật khẩu đã được gửi đến email của bạn!');
    }

    public function showResetForm($token)
    {
        if (!$this->passwordResetService->validateToken($token)) {
            return redirect()->route('password.request')
                ->with('error', 'Mã thông báo đặt lại mật khẩu không hợp lệ.');
        }

        return view('auth.reset-password', ['token' => $token]);
    }

    public function reset(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        if (!$this->passwordResetService->validateToken($request->token)) {
            return back()->with('error', 'Mã thông báo đặt lại mật khẩu không hợp lệ.');
        }

        $this->passwordResetService->resetPassword($request->token, $request->password);

        return redirect()->route('login')
            ->with('status', 'Bạn đã đặt lại mật khẩu thành công!');
    }
} 