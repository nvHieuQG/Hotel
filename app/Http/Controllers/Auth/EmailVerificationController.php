<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationController extends Controller
{
    /**
     * Hiển thị trang thông báo xác minh email
     */
    public function notice()
    {
        return view('auth.verify-email');
    }

    /**
     * Xác minh email với link đã được gửi
     */
    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
 
        return redirect()->route('index')->with('verified', true);
    }

    /**
     * Gửi lại email xác minh
     */
    public function resend(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->route('index');
        }
 
        $request->user()->sendEmailVerificationNotification();
 
        return back()->with('status', 'Email xác minh đã được gửi lại!');
    }
}
