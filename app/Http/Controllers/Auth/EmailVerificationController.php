<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\AuthServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Verified;

class EmailVerificationController extends Controller
{
    protected $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Hiển thị trang thông báo xác minh email
     */
    public function notice()
    {
        return view('auth.verify-email');
    }

    /**
     * Xác minh email
     */
    public function verify(Request $request, $id, $hash)
    {
        if (!$request->hasValidSignature()) {
            return redirect()->route('verification.notice')
                ->with('error', 'Liên kết xác minh không hợp lệ hoặc đã hết hạn.');
        }

        $result = $this->authService->verifyEmail($id, $hash);

        if ($result) {
            // Kích hoạt sự kiện đã xác minh
            $user = $this->authService->getCurrentUser();
            if ($user && $user->id == $id) {
                event(new Verified($user));
            }

            return redirect()->route('index')
                ->with('success', 'Email của bạn đã được xác minh thành công!');
        }

        return redirect()->route('verification.notice')
            ->with('error', 'Không thể xác minh email của bạn. Vui lòng thử lại.');
    }

    /**
     * Gửi lại email xác minh
     */
    public function resend(Request $request)
    {
        $user = $this->authService->getCurrentUser();

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('index')
                ->with('info', 'Email của bạn đã được xác minh trước đó.');
        }

        // Tạo token mới và gửi lại email
        $verificationUrl = $this->authService->createVerificationUrl($user);
        $this->authService->sendVerificationEmail($user, $verificationUrl);

        return back()->with('status', 'Email xác minh đã được gửi lại!');
    }
}
