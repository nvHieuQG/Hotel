<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Interfaces\Services\PasswordResetServiceInterface;
use App\Services\PasswordResetService;

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
        
        // Admin Repository Bindings
        $this->app->bind(
            \App\Interfaces\Repositories\Admin\AdminBookingRepositoryInterface::class,
            \App\Repositories\Admin\AdminBookingRepository::class
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
        
        // Admin Service Bindings
        $this->app->bind(
            \App\Interfaces\Services\Admin\AdminBookingServiceInterface::class,
            \App\Services\Admin\AdminBookingService::class
        );
        
        $this->app->bind(
            \App\Interfaces\Services\Admin\AdminDashboardServiceInterface::class,
            \App\Services\Admin\AdminDashboardService::class
        );

        // Password Reset Bindings
        $this->app->bind(UserRepositoryInterface::class, function ($app) {
            return new UserRepository($app->make('App\Models\User'));
        });

        $this->app->bind(PasswordResetServiceInterface::class, function ($app) {
            return new PasswordResetService(
                $app->make(UserRepositoryInterface::class)
            );
        });

        $this->app->bind(
            \App\Interfaces\Repositories\Admin\AdminRoomRepositoryInterface::class,
            \App\Repositories\Admin\AdminRoomRepository::class
        );
        
        $this->app->bind(
            \App\Interfaces\Services\Admin\AdminRoomServiceInterface::class,
            \App\Services\Admin\AdminRoomService::class
        );

        $this->app->bind(
            \App\Interfaces\Repositories\Admin\AdminRoomTypeRepositoryInterface::class,
            \App\Repositories\Admin\AdminRoomTypeRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Services\Admin\AdminRoomTypeServiceInterface::class,
            \App\Services\Admin\AdminRoomTypeService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
