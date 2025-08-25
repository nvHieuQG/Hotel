<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\CheckBookingAccess;
use App\Http\Middleware\AdminOnlyMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Đăng ký middleware admin
        $middleware->alias([
            'admin' => AdminMiddleware::class,
            'check.booking.access' => CheckBookingAccess::class,
            'admin.only' => AdminOnlyMiddleware::class,
        ]);
    })
    ->withCommands([
        \App\Console\Commands\CreateBookingNoteReminders::class,
    ])
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
