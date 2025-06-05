<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra người dùng đã đăng nhập và có vai trò admin, super_admin hoặc staff
        if (!Auth::check() || (Auth::user()->role?->name != 'admin' && Auth::user()->role?->name != 'super_admin' && Auth::user()->role?->name != 'staff')) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Không có quyền truy cập'], 403);
            }
            
            return redirect()->route('index')->with('error', 'Bạn không có quyền truy cập vào trang này.');
        }

        return $next($request);
    }
} 