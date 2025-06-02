<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Đăng ký Repository Bindings
        $this->app->bind(
            \App\Interfaces\Repositories\UserRepositoryInterface::class,
            \App\Repositories\UserRepository::class
        );
        
        $this->app->bind(
            \App\Interfaces\Repositories\BookingRepositoryInterface::class,
            \App\Repositories\BookingRepository::class
        );
        
        $this->app->bind(
            \App\Interfaces\Repositories\RoomRepositoryInterface::class,
            \App\Repositories\RoomRepository::class
        );
        
        // Đăng ký Service Bindings
        $this->app->bind(
            \App\Interfaces\Services\AuthServiceInterface::class,
            \App\Services\AuthService::class
        );
        
        $this->app->bind(
            \App\Interfaces\Services\BookingServiceInterface::class,
            \App\Services\BookingService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
