<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminOnlyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Chỉ cho phép admin hoặc super_admin
        if (!Auth::check() || !in_array(Auth::user()->role?->name, ['admin', 'super_admin'])) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Không có quyền truy cập'], 403);
            }
            return redirect()->route('index')->with('error', 'Bạn không có quyền truy cập vào trang này.');
        }

        return $next($request);
    }
}
