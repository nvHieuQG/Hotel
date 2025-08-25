<?php

namespace App\Providers;


use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Interfaces\Repositories\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\Interfaces\Services\PasswordResetServiceInterface;
use App\Services\PasswordResetService;
use App\Models\Booking;
use App\Observers\BookingObserver;


use App\Models\BookingNote;
use App\Observers\BookingNoteObserver;
use App\Models\RoomTypeReview;
use App\Observers\RoomTypeReviewObserver;
use App\Interfaces\Services\RegistrationDocumentServiceInterface;
use App\Services\RegistrationDocumentService;
use App\Interfaces\Services\VatInvoiceServiceInterface;
use App\Services\VatInvoiceService;

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

    $this->app->bind(
        \App\Interfaces\Repositories\Admin\AdminRoomRepositoryInterface::class,
        \App\Repositories\Admin\AdminRoomRepository::class
    );

    $this->app->bind(
        \App\Interfaces\Repositories\Admin\AdminRoomTypeRepositoryInterface::class,
        \App\Repositories\Admin\AdminRoomTypeRepository::class
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
            \App\Interfaces\Services\Admin\AdminBookingServiceServiceInterface::class,
            \App\Services\Admin\AdminBookingServiceService::class
        );

    $this->app->bind(
        \App\Interfaces\Services\Admin\AdminDashboardServiceInterface::class,
        \App\Services\Admin\AdminDashboardService::class
    );

    $this->app->bind(
        \App\Interfaces\Services\Admin\AdminRoomServiceInterface::class,
        \App\Services\Admin\AdminRoomService::class
    );

    $this->app->bind(
        \App\Interfaces\Services\Admin\AdminRoomTypeServiceInterface::class,
        \App\Services\Admin\AdminRoomTypeService::class
    );

    // Password Reset Bindings

        // Tour Booking Repository and Service Bindings
        $this->app->bind(
            \App\Interfaces\Repositories\TourBookingRepositoryInterface::class,
            \App\Repositories\TourBookingRepository::class
        );

        $this->app->bind(
            \App\Interfaces\Services\TourBookingServiceInterface::class,
            \App\Services\TourBookingService::class
        );

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
        \App\Interfaces\Repositories\RoomTypeRepositoryInterface::class,
        \App\Repositories\RoomTypeRepository::class
    );
    $this->app->bind(
        \App\Interfaces\Services\RoomTypeServiceInterface::class,
        \App\Services\RoomTypeService::class
    );

    $this->app->bind(
        \App\Interfaces\Services\SupportServiceInterface::class,
        \App\Services\SupportService::class
    );
    $this->app->bind(
        \App\Interfaces\Repositories\SupportTicketRepositoryInterface::class,
        \App\Repositories\SupportTicketRepository::class
    );

        // Service Category Repository Binding
        $this->app->bind(
            \App\Interfaces\Repositories\ServiceCategoryRepositoryInterface::class,
            \App\Repositories\ServiceCategoryRepository::class
        );
        // Service Category Service Binding
        $this->app->bind(
            \App\Interfaces\Services\ServiceCategoryServiceInterface::class,
            \App\Services\ServiceCategoryService::class
        );
        // Service Repository Binding
        $this->app->bind(
            \App\Interfaces\Repositories\ServiceRepositoryInterface::class,
            \App\Repositories\ServiceRepository::class
        );
        // Service Service Binding
        $this->app->bind(
            \App\Interfaces\Services\ServiceServiceInterface::class,
            \App\Services\ServiceService::class
        );
        // Room Type Service Repository Binding
        $this->app->bind(
            \App\Interfaces\Repositories\RoomTypeServiceRepositoryInterface::class,
            \App\Repositories\RoomTypeServiceRepository::class
        );
        // Room Type Service Service Binding
        $this->app->bind(
            \App\Interfaces\Services\RoomTypeServiceServiceInterface::class,
            \App\Services\RoomTypeServiceService::class
        );

        // Promotion Repository Bindings
        $this->app->bind(
            \App\Interfaces\Repositories\PromotionRepositoryInterface::class,
            \App\Repositories\PromotionRepository::class
        );
        $this->app->bind(
            \App\Interfaces\Repositories\Admin\AdminPromotionRepositoryInterface::class,
            \App\Repositories\Admin\AdminPromotionRepository::class
        );

        // Promotion Service Bindings
        $this->app->bind(
            \App\Interfaces\Services\PromotionServiceInterface::class,
            \App\Services\PromotionService::class
        );
        $this->app->bind(
            \App\Interfaces\Services\Admin\AdminPromotionServiceInterface::class,
            \App\Services\Admin\AdminPromotionService::class
        );

        // Payment Service Binding
        $this->app->bind(
            \App\Interfaces\Services\PaymentServiceInterface::class,
            \App\Services\PaymentService::class
        );
    // Service Category Repository Binding
    $this->app->bind(
        \App\Interfaces\Repositories\ServiceCategoryRepositoryInterface::class,
        \App\Repositories\ServiceCategoryRepository::class
    );
    // Service Category Service Binding
    $this->app->bind(
        \App\Interfaces\Services\ServiceCategoryServiceInterface::class,
        \App\Services\ServiceCategoryService::class
    );
    // Service Repository Binding
    $this->app->bind(
        \App\Interfaces\Repositories\ServiceRepositoryInterface::class,
        \App\Repositories\ServiceRepository::class
    );
    // Service Service Binding
    $this->app->bind(
        \App\Interfaces\Services\ServiceServiceInterface::class,
        \App\Services\ServiceService::class
    );
    // Room Type Service Repository Binding
    $this->app->bind(
        \App\Interfaces\Repositories\RoomTypeServiceRepositoryInterface::class,
        \App\Repositories\RoomTypeServiceRepository::class
    );
    // Room Type Service Service Binding
    $this->app->bind(
        \App\Interfaces\Services\RoomTypeServiceServiceInterface::class,
        \App\Services\RoomTypeServiceService::class
    );

        // Room Change Repository Binding
        $this->app->bind(
            \App\Interfaces\Repositories\RoomChangeRepositoryInterface::class,
            \App\Repositories\RoomChangeRepository::class
        );

        // Room Change Service Binding
        $this->app->bind(
            \App\Interfaces\Services\RoomChangeServiceInterface::class,
            \App\Services\RoomChangeService::class
        );

        // Extra Service Binding
        $this->app->bind(
            \App\Interfaces\Repositories\ExtraServiceRepositoryInterface::class,
            \App\Repositories\ExtraServiceRepository::class
        );
        $this->app->bind(
            \App\Interfaces\Services\ExtraServiceServiceInterface::class,
            \App\Services\ExtraServiceService::class
        );

    // View Composer cho dropdown notification
    \Illuminate\Support\Facades\View::composer('admin.layouts.admin-master', function ($view) {
        $unreadNotifications = \App\Models\AdminNotification::unread()->orderBy('created_at', 'desc')->limit(5)->get();
        $unreadCount = \App\Models\AdminNotification::unread()->count();
        $view->with(compact('unreadNotifications', 'unreadCount'));
    });
        // Registration Document Service
        $this->app->bind(RegistrationDocumentServiceInterface::class, RegistrationDocumentService::class);

        // VAT Invoice Service
        $this->app->bind(VatInvoiceServiceInterface::class, VatInvoiceService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Đăng ký Observer
        Booking::observe(BookingObserver::class);
        BookingNote::observe(BookingNoteObserver::class);
        RoomTypeReview::observe(RoomTypeReviewObserver::class);

        // Blade directives for roles
        Blade::if('role', function (string $roleName) {
            return Auth::check() && optional(Auth::user()->role)->name === $roleName;
        });

        Blade::if('roles', function (array $roles) {
            return Auth::check() && in_array(optional(Auth::user()->role)->name, $roles, true);
        });

        Blade::if('staff', function () {
            return Auth::check() && optional(Auth::user()->role)->name === 'staff';
        });

        // Đăng ký scheduled task để xóa giao dịch quá hạn
        $this->app->make(\Illuminate\Console\Scheduling\Schedule::class)
            ->command('payments:clean-expired')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/clean-expired-payments.log'));

        // Share featured promotions to client master layout if exists
        try {
            \Illuminate\Support\Facades\View::composer('client.layouts.master', function ($view) {
                if (class_exists(\App\Models\Promotion::class)) {
                    $featuredPromotions = \App\Models\Promotion::active()->featured()->limit(5)->get();
                    $view->with('featuredPromotions', $featuredPromotions);
                }
            });
        } catch (\Throwable $e) {
            // Avoid breaking boot if promotions tables not ready on some environments
        }
    }
}
