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



        // Room Type Review Repository Binding
        $this->app->bind(
            \App\Interfaces\Repositories\RoomTypeReviewRepositoryInterface::class,
            \App\Repositories\RoomTypeReviewRepository::class
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



        // Room Type Review Service Binding
        $this->app->bind(
            \App\Interfaces\Services\RoomTypeReviewServiceInterface::class,
            \App\Services\RoomTypeReviewService::class
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

        // Profile Service Binding
        $this->app->bind(
            \App\Interfaces\Services\ProfileServiceInterface::class,
            function ($app) {
                return new \App\Services\ProfileService(
                    $app->make(\App\Interfaces\Repositories\UserRepositoryInterface::class)
                );
            }
        );

        $this->app->bind(PasswordResetServiceInterface::class, function ($app) {
            return new PasswordResetService(
                $app->make(UserRepositoryInterface::class)
            );
        });

        //Search Room Service Binding
        $this->app->bind(
            \App\Interfaces\Services\RoomServiceInterface::class,
            \App\Services\RoomService::class
        );

        $this->app->bind(
            \App\Interfaces\Services\UserServiceInterface::class,
            \App\Services\UserService::class
        );

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
        
        $this->app->bind(
            \App\Interfaces\Repositories\RoomTypeRepositoryInterface::class,
            \App\Repositories\RoomTypeRepository::class
        );
        $this->app->bind(
            \App\Interfaces\Services\RoomTypeServiceInterface::class,
            \App\Services\RoomTypeService::class
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
