<?php

use App\Http\Controllers\Admin\AdminSupportController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Admin\AdminRoomController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\RoomTypeReviewController;
use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminRoomTypeReviewController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\Admin\AdminUserController;

use Illuminate\Support\Facades\Auth;

// Route::get('/', function () {
//     return view('client.index');
// });

Route::get('/', [HotelController::class, 'index'])->name('index');

Route::get('/rooms', [HotelController::class, 'rooms'])->name('rooms');

Route::get('/restaurant', [HotelController::class, 'restaurant'])->name('restaurant');

Route::get('/blog', [HotelController::class, 'blog'])->name('blog');

Route::get('/about', [HotelController::class, 'about'])->name('about');

Route::get('/contact', [HotelController::class, 'contact'])->name('contact');
Route::post('/contact', [HotelController::class, 'sendContact'])->name('contact.send');

Route::get('/rooms-single/{id}', [HotelController::class, 'roomsSingle'])->name('rooms-single');

Route::get('/blog-single', [HotelController::class, 'blogSingle'])->name('blog-single');

// Đăng nhập & Đăng ký
Route::middleware('guest')->group(function () {
    // Đăng nhập
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);


    // Đăng ký
    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);
});

// Đăng xuất
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// Xác minh email
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])
        ->name('verification.notice');


    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');


    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

// Routes yêu cầu xác thực email
Route::middleware(['auth'])->group(function () {
    // Booking - Đặt phòng
    Route::get('/booking', [BookingController::class, 'booking'])->name('booking');
    Route::post('/booking', [BookingController::class, 'storeBooking']);
    Route::post('/booking/cancel/{id}', [BookingController::class, 'cancelBooking'])->name('booking.cancel');

    // Support
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support/ticket', [SupportController::class, 'createTicket'])->name('support.createTicket');
    Route::get('/support/ticket/{id}', [SupportController::class, 'showTicket'])->name('support.showTicket');
    Route::post('/support/ticket/{id}/message', [SupportController::class, 'sendMessage'])->name('support.sendMessage');
    Route::get('/booking/{id}/detail', [BookingController::class, 'showDetail'])->name('booking.detail');

    // AJAX Room Type Reviews - Đặt trước để tránh xung đột
    Route::post('/room-type-reviews/store-ajax', [RoomTypeReviewController::class, 'storeAjax'])->name('room-type-reviews.store-ajax');

    // Room Type Reviews - Chỉ trong profile
    Route::post('/room-type-reviews/{roomTypeId}', [UserProfileController::class, 'createReview'])->name('room-type-reviews.store');
    Route::put('/room-type-reviews/{id}', [UserProfileController::class, 'updateReview'])->name('room-type-reviews.update');
    Route::delete('/room-type-reviews/{id}', [UserProfileController::class, 'deleteReview'])->name('room-type-reviews.destroy');

    // Form đánh giá
    Route::get('/room-type-reviews/{roomTypeId}/form', [RoomTypeReviewController::class, 'reviewForm'])->name('room-type-reviews.form');
});

// Routes công khai cho room type reviews (chỉ hiển thị)
Route::get('/rooms/{id}/reviews-ajax', [HotelController::class, 'getRoomReviewsAjax'])->name('rooms.reviews-ajax');

// Admin Routes
Route::prefix('/admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard.page');

    // Quản lý đặt phòng
    Route::get('bookings/report', [AdminBookingController::class, 'report'])->name('bookings.report');
    Route::patch('bookings/{id}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::resource('bookings', AdminBookingController::class);

    // Quản lý phòng
    Route::resource('rooms', AdminRoomController::class);

    Route::get('/support', [AdminSupportController::class, 'index'])->name('support.index');
    Route::get('/support/ticket/{id}', [AdminSupportController::class, 'showTicket'])->name('support.showTicket');
    Route::post('/support/ticket/{id}/message', [AdminSupportController::class, 'sendMessage'])->name('support.sendMessage');


    // Routes cho quản lý ảnh phòng
    Route::delete('rooms/{room}/images/{image}', [AdminRoomController::class, 'deleteImage'])->name('rooms.images.delete');
    Route::post('rooms/{room}/images/{image}/primary', [AdminRoomController::class, 'setPrimaryImage'])->name('rooms.images.primary');

    // Quản lý đánh giá loại phòng
    Route::get('room-type-reviews/statistics', [AdminRoomTypeReviewController::class, 'statistics'])->name('room-type-reviews.statistics');
    Route::patch('room-type-reviews/{id}/approve', [AdminRoomTypeReviewController::class, 'approve'])->name('room-type-reviews.approve');
    Route::patch('room-type-reviews/{id}/reject', [AdminRoomTypeReviewController::class, 'reject'])->name('room-type-reviews.reject');
    Route::get('room-type-reviews', [AdminRoomTypeReviewController::class, 'index'])->name('room-type-reviews.index');
    Route::get('room-type-reviews/create', [AdminRoomTypeReviewController::class, 'create'])->name('room-type-reviews.create');
    Route::post('room-type-reviews', [AdminRoomTypeReviewController::class, 'store'])->name('room-type-reviews.store');
    Route::get('room-type-reviews/{review}', [AdminRoomTypeReviewController::class, 'show'])->name('room-type-reviews.show');
    Route::delete('room-type-reviews/{review}', [AdminRoomTypeReviewController::class, 'destroy'])->name('room-type-reviews.destroy');

    // Quản lý người dùng
    Route::resource('users', AdminUserController::class)->except(['create', 'store']);
});

// Route công khai cho room type reviews (chỉ hiển thị) - đặt sau admin routes để tránh xung đột
Route::get('/room-type-reviews/{roomTypeId}/ajax', [RoomTypeReviewController::class, 'getReviewsAjax'])->name('room-type-reviews.ajax');

// Password reset routes
Route::get('/forgot-password', [ForgotPasswordController::class, 'showForgotForm'])
    ->middleware('guest')
    ->name('password.request');

// Gửi link đặt lại mật khẩu
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink'])
    ->middleware('guest')
    ->name('password.email');

// Hiển thị form đặt lại mật khẩu
Route::get('/reset-password/{token}', [ForgotPasswordController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset');

// Đặt lại mật khẩu
Route::post('/reset-password', [ForgotPasswordController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');

// Search Rooms
Route::get('/search-rooms', [RoomController::class, 'search'])->name('rooms.search');

// Payment
Route::get('/confirm-info-payment/{booking}', [PaymentController::class, 'confirmInfo'])->name('confirm-info-payment');
Route::get('/payment-method/{booking}', [PaymentController::class, 'paymentMethod'])->name('payment-method');

// User Profile Routes (chỉ cần đăng nhập, không cần xác minh email)
Route::middleware('auth')->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'showUserProfile'])->name('user.profile');
    Route::post('/user/profile/update', [UserProfileController::class, 'updateUserProfile'])->name('user.profile.update');
    Route::post('/user/profile/change-password', [UserProfileController::class, 'changeUserPassword'])->name('user.profile.change_password');
    Route::get('/user/bookings', [UserProfileController::class, 'showUserBookings'])->name('user.bookings');
    Route::get('/user/reviews', [UserProfileController::class, 'showUserReviews'])->name('user.reviews');

    // API routes cho AJAX loading
    Route::get('/user/bookings/partial', [UserProfileController::class, 'getBookingsPartial'])->name('user.bookings.partial');
    Route::get('/user/reviews/partial', [UserProfileController::class, 'getReviewsPartial'])->name('user.reviews.partial');

    // API routes cho chi tiết booking và review
    Route::get('/user/bookings/{id}/detail', [UserProfileController::class, 'getBookingDetail'])->name('user.bookings.detail');
    Route::get('/user/reviews/{id}/detail', [UserProfileController::class, 'getReviewDetail'])->name('user.reviews.detail');
    Route::get('/user/reviews/{id}/data', [UserProfileController::class, 'getReviewData'])->name('user.reviews.data');
});
