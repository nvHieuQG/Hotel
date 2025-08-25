<?php

use App\Http\Controllers\Admin\AdminBookingController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\AdminRoomController;
use App\Http\Controllers\Admin\AdminTourBookingController;
use App\Http\Controllers\Admin\AdminRoomTypeReviewController;
use App\Http\Controllers\Admin\AdminRoomTypeServiceController;
use App\Http\Controllers\Admin\AdminServiceCategoryController;
use App\Http\Controllers\Admin\AdminServiceController;
use App\Http\Controllers\Admin\AdminSupportController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;

use App\Http\Controllers\RoomController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\BookingPromotionController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\RoomChangeController;
use App\Http\Controllers\TourBookingController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\RoomTypeReviewController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\ClientVatInvoiceController;
use App\Http\Controllers\ClientTourVatInvoiceController;
use App\Http\Controllers\ChatbotController;

use App\Http\Controllers\Admin\AdminRoomChangeController;
use App\Http\Controllers\Admin\AdminExtraServiceController;
use App\Http\Controllers\Admin\AdminTourVatInvoiceController;
use App\Http\Controllers\Admin\TourBookingServiceController;
use App\Http\Controllers\Admin\TourBookingNoteController;


// Route::get('/', function () {
//     return view('client.index');
// });

// Staff Routes
Route::prefix('/staff')->name('staff.')->middleware(['auth', 'admin'])->group(function () {
    // Smart redirect for /staff root -> bookings
    Route::get('/', function () {
        return redirect()->route('staff.bookings.index');
    })->name('home');

    // Dashboard (staff can view dashboard)
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Bookings
    // List + full resource (except destroy)
    Route::resource('bookings', AdminBookingController::class)->except(['destroy']);
    // Additional booking actions (no reports here)
    Route::patch('bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.update-status');
    // Registration documents
    Route::get('bookings/{id}/registration/preview', [AdminBookingController::class, 'previewRegistration'])->name('bookings.registration.preview');
    Route::post('bookings/{id}/generate-pdf', [AdminBookingController::class, 'generateRegistrationPdf'])->name('bookings.generate-pdf');
    Route::post('bookings/{id}/send-email', [AdminBookingController::class, 'sendRegistrationEmail'])->name('bookings.send-email');
    Route::get('bookings/{id}/download-registration', [AdminBookingController::class, 'downloadRegistration'])->name('bookings.download-registration');
    Route::get('bookings/{id}/download-word', [AdminBookingController::class, 'downloadRegistration'])->name('bookings.download-word');
    Route::get('bookings/{id}/view-word', [AdminBookingController::class, 'downloadRegistration'])->name('bookings.view-word');
    // VAT invoice
    Route::post('bookings/{id}/vat/generate', [AdminBookingController::class, 'generateVatInvoice'])->name('bookings.vat.generate');
    Route::post('bookings/{id}/vat/send', [AdminBookingController::class, 'sendVatInvoice'])->name('bookings.vat.send');
    Route::get('bookings/{id}/vat/preview', [AdminBookingController::class, 'previewVatInvoice'])->name('bookings.vat.preview');
    Route::get('bookings/{id}/vat/download', [AdminBookingController::class, 'downloadVatInvoice'])->name('bookings.vat.download');
    Route::post('bookings/{id}/vat/regenerate', [AdminBookingController::class, 'regenerateVatInvoice'])->name('bookings.vat.regenerate');
    // Booking services & payments
    Route::post('bookings/{id}/services/add', [AdminBookingController::class, 'addServiceToBooking'])->name('bookings.services.add');
    Route::delete('bookings/{id}/services/{bookingServiceId}', [AdminBookingController::class, 'destroyServiceFromBooking'])->name('bookings.services.destroy');
    Route::post('bookings/{id}/confirm-payment', [AdminBookingController::class, 'confirmPayment'])->name('bookings.confirm-payment');
    Route::post('bookings/{id}/payments/collect', [AdminBookingController::class, 'collectAdditionalPayment'])->name('bookings.payments.collect');

    // Room changes
    Route::get('room-changes', [AdminRoomChangeController::class, 'index'])->name('room-changes.index');
    Route::get('room-changes/{roomChange}', [AdminRoomChangeController::class, 'show'])->name('room-changes.show');
    Route::post('room-changes/{roomChange}/approve', [AdminRoomChangeController::class, 'approve'])->name('room-changes.approve');
    Route::post('room-changes/{roomChange}/reject', [AdminRoomChangeController::class, 'reject'])->name('room-changes.reject');
    Route::post('room-changes/{roomChange}/complete', [AdminRoomChangeController::class, 'complete'])->name('room-changes.complete');
    Route::post('room-changes/{roomChange}/mark-paid', [AdminRoomChangeController::class, 'markAsPaid'])->name('room-changes.mark-paid');
    Route::post('room-changes/{roomChange}/mark-refunded', [AdminRoomChangeController::class, 'markAsRefunded'])->name('room-changes.mark-refunded');
    Route::post('room-changes/{roomChange}/update-status', [AdminRoomChangeController::class, 'updateStatus'])->name('room-changes.update-status');

    // Tour bookings
    Route::resource('tour-bookings', AdminTourBookingController::class);
    Route::patch('tour-bookings/{id}/status', [AdminTourBookingController::class, 'updateStatus'])->name('tour-bookings.update-status');
    Route::patch('tour-bookings/{id}/payments/{paymentId}', [AdminTourBookingController::class, 'updatePaymentStatus'])->name('tour-bookings.payments.update-status');
    Route::post('tour-bookings/{id}/collect-payment', [AdminTourBookingController::class, 'collectPayment'])->name('tour-bookings.collect-payment');
    // Services & notes for tour bookings
    Route::post('tour-bookings/{id}/services', [TourBookingServiceController::class, 'store'])->name('tour-bookings.services.store');
    Route::delete('tour-bookings/{id}/services/{serviceId}', [TourBookingServiceController::class, 'destroy'])->name('tour-bookings.services.destroy');
    Route::post('tour-bookings/{id}/notes', [TourBookingNoteController::class, 'store'])->name('tour-bookings.notes.store');
    Route::delete('tour-bookings/{id}/notes/{noteId}', [TourBookingNoteController::class, 'destroy'])->name('tour-bookings.notes.destroy');

    // Support
    Route::get('/support', [AdminSupportController::class, 'index'])->name('support.index');
    Route::get('/support/conversation/{conversationId}', [AdminSupportController::class, 'showConversation'])->name('support.showConversation');
    Route::post('/support/conversation/{conversationId}/message', [AdminSupportController::class, 'sendMessage'])->name('support.sendMessage');
    Route::get('/support/conversation/{conversationId}/messages', [AdminSupportController::class, 'getNewMessages'])->name('support.getNewMessages');
    Route::post('/support/conversations/updates', [AdminSupportController::class, 'getUpdates'])->name('support.getUpdates');
});

Route::get('/', [HotelController::class, 'index'])->name('index');

Route::get('/rooms', [HotelController::class, 'rooms'])->name('rooms');

Route::get('/restaurant', [HotelController::class, 'restaurant'])->name('restaurant');

Route::get('/blog', [HotelController::class, 'blog'])->name('blog');

Route::get('/about', [HotelController::class, 'about'])->name('about');

Route::get('/contact', [HotelController::class, 'contact'])->name('contact');
Route::post('/contact', [HotelController::class, 'sendContact'])->name('contact.send');

// Chatbot routes - Yêu cầu đăng nhập
Route::middleware(['auth'])->group(function () {
    Route::get('/chatbot', [ChatbotController::class, 'index'])->name('chatbot.index');
    Route::post('/chatbot/send-message', [ChatbotController::class, 'sendMessage'])->name('chatbot.sendMessage');
    Route::get('/chatbot/history', [ChatbotController::class, 'getChatHistory'])->name('chatbot.history');
    Route::post('/chatbot/clear-history', [ChatbotController::class, 'clearChatHistory'])->name('chatbot.clearHistory');
    Route::post('/chatbot/save-to-session', [ChatbotController::class, 'saveToSession'])->name('chatbot.saveToSession');
    Route::get('/chatbot/user-info', [ChatbotController::class, 'getUserInfo'])->name('chatbot.userInfo');


});

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
    Route::get('/booking/confirm', [BookingController::class, 'confirm'])->name('booking.confirm');
    Route::post('/booking', [BookingController::class, 'storeBooking']);
    Route::post('/booking/cancel/{id}', [BookingController::class, 'cancelBooking'])->name('booking.cancel');

    // Support
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::get('/support/conversation/{conversationId}', [SupportController::class, 'showConversation'])->name('support.showConversation');
    Route::post('/support/conversation/{conversationId}/message', [SupportController::class, 'sendMessage'])->name('support.sendMessage');
    Route::post('/support/message', [SupportController::class, 'sendMessage'])->name('support.createMessage');
    Route::get('/support/conversation/{conversationId}/messages', [SupportController::class, 'getNewMessages'])->name('support.getNewMessages');
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

// Tour Booking Routes - Công khai (không cần auth)
Route::get('/tour-booking/search', [TourBookingController::class, 'searchForm'])->name('tour-booking.search');
Route::post('/tour-booking/search', [TourBookingController::class, 'search'])->name('tour-booking.search.post');
Route::get('/tour-booking/search-test', [TourBookingController::class, 'search'])->name('tour-booking.search.test');
Route::get('/tour-booking/select-rooms', [TourBookingController::class, 'selectRooms'])->name('tour-booking.select-rooms');
Route::post('/tour-booking/calculate-price', [TourBookingController::class, 'calculatePrice'])->name('tour-booking.calculate-price');
Route::post('/tour-booking/confirm', [TourBookingController::class, 'confirm'])->name('tour-booking.confirm');
Route::post('/tour-booking/store', [TourBookingController::class, 'store'])->name('tour-booking.store');
Route::get('/tour-booking/payment/{bookingId}', [TourBookingController::class, 'payment'])->name('tour-booking.payment');
Route::get('/tour-booking', [TourBookingController::class, 'index'])->name('tour-booking.index');
Route::get('/tour-booking/{id}', [TourBookingController::class, 'show'])->name('tour-booking.show');

// Tour Booking Payment Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/tour-booking/{tourBooking}/credit-card', [TourBookingController::class, 'processCreditCardPayment'])->name('tour-booking.credit-card');
    Route::get('/tour-booking/{tourBooking}/bank-transfer', [TourBookingController::class, 'processBankTransferPayment'])->name('tour-booking.bank-transfer');

    Route::post('/tour-booking/{tourBooking}/credit-card/confirm', [TourBookingController::class, 'confirmCreditCardPayment'])->name('tour-booking.credit-card.confirm');
    Route::post('/tour-booking/{tourBooking}/bank-transfer/confirm', [TourBookingController::class, 'confirmBankTransferPayment'])->name('tour-booking.bank-transfer.confirm');

    // Client Tour VAT invoice routes
    Route::get('/tour-booking/{id}/vat-invoice', [ClientTourVatInvoiceController::class, 'showVatForm'])->name('tour-booking.vat-invoice');
    Route::post('/tour-booking/{id}/vat-invoice', [ClientTourVatInvoiceController::class, 'requestVatInvoice'])->name('tour-booking.vat-invoice.request');
    Route::get('/tour-booking/{id}/vat-invoice/download', [ClientTourVatInvoiceController::class, 'downloadVatInvoice'])->name('tour-booking.vat-invoice.download');

    // Tour booking promotion routes
    Route::post('/tour-booking/{id}/apply-promotion', [TourBookingController::class, 'applyPromotion'])->name('tour-booking.apply-promotion');
    Route::post('/tour-booking/{id}/remove-promotion', [TourBookingController::class, 'removePromotion'])->name('tour-booking.remove-promotion');
});

// Booking notes: KHÔNG lồng group auth bên ngoài nữa
Route::middleware(['auth', 'check.booking.access'])->group(function () {
    Route::get('/booking-notes/{bookingId}', [BookingController::class, 'notesIndex'])->name('booking-notes.index');
    Route::get('/booking-notes/{bookingId}/search', [BookingController::class, 'notesSearch'])->name('booking-notes.search');
    Route::get('/booking-notes/{bookingId}/create', [BookingController::class, 'notesCreate'])->name('booking-notes.create');
    Route::post('/booking-notes/{bookingId}', [BookingController::class, 'notesStore'])->name('booking-notes.store');
    Route::get('/booking-notes/{bookingId}/{id}/edit', [BookingController::class, 'notesEdit'])->name('booking-notes.edit');
    Route::put('/booking-notes/{bookingId}/{id}', [BookingController::class, 'notesUpdate'])->name('booking-notes.update');
    Route::delete('/booking-notes/{bookingId}/{id}', [BookingController::class, 'notesDestroy'])->name('booking-notes.destroy');
    Route::get('/booking-notes/{bookingId}/{id}', [BookingController::class, 'notesShow'])->name('booking-notes.show');
    Route::post('/booking-notes/{bookingId}/request', [BookingController::class, 'notesStoreRequest'])->name('booking-notes.store-request');
    Route::post('/booking-notes/{bookingId}/response', [BookingController::class, 'notesStoreResponse'])->name('booking-notes.store-response');
});

// Client VAT invoice
Route::middleware(['auth'])->group(function () {
    Route::post('profile/bookings/{booking}/vat/request', [ClientVatInvoiceController::class, 'request'])->name('client.vat-invoice.request');
    Route::post('profile/bookings/{booking}/vat/regenerate', [ClientVatInvoiceController::class, 'regenerate'])->name('client.vat-invoice.regenerate');
});

// Routes công khai cho room type reviews (chỉ hiển thị)
Route::get('/rooms/{id}/reviews-ajax', [HotelController::class, 'getRoomReviewsAjax'])->name('rooms.reviews-ajax');

// Admin Routes
Route::prefix('/admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    // Smart redirect for /admin root
    Route::get('/', function () {
        $role = auth()->user()->role->name ?? null;
        return in_array($role, ['admin', 'super_admin'])
            ? redirect()->route('admin.dashboard')
            : redirect()->route('admin.bookings.index');
    })->name('home');

    // Dashboard (staff can view)
    // Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Quản lý đặt phòng
    Route::get('bookings/report', [AdminBookingController::class, 'report'])->name('bookings.report')->middleware('admin.only');
    Route::patch('bookings/{booking}/status', [AdminBookingController::class, 'updateStatus'])->name('bookings.update-status');
    Route::resource('bookings', AdminBookingController::class)->except(['destroy']);

    // Giấy đăng ký tạm chú tạm vắng
    Route::get('bookings/{booking}/registration/preview', [AdminBookingController::class, 'previewRegistration'])->name('bookings.registration.preview');

    // Quản lý giấy đăng ký tạm chú tạm vắng
    Route::post('bookings/{id}/generate-pdf', [AdminBookingController::class, 'generateRegistrationPdf'])->name('bookings.generate-pdf');
    Route::post('bookings/{id}/send-email', [AdminBookingController::class, 'sendRegistrationEmail'])->name('bookings.send-email');
    Route::get('bookings/{id}/download-registration', [AdminBookingController::class, 'downloadRegistration'])->name('bookings.download-registration');
    Route::get('bookings/{id}/download-word', [AdminBookingController::class, 'downloadRegistration'])->name('bookings.download-word');
    Route::get('bookings/{id}/view-word', [AdminBookingController::class, 'downloadRegistration'])->name('bookings.view-word');

    // VAT invoice
    Route::post('bookings/{id}/vat/generate', [AdminBookingController::class, 'generateVatInvoice'])->name('bookings.vat.generate');
    Route::post('bookings/{id}/vat/send', [AdminBookingController::class, 'sendVatInvoice'])->name('bookings.vat.send');
    Route::get('bookings/{id}/vat/preview', [AdminBookingController::class, 'previewVatInvoice'])->name('bookings.vat.preview');
    Route::get('bookings/{id}/vat/download', [AdminBookingController::class, 'downloadVatInvoice'])->name('bookings.vat.download');
    Route::post('bookings/{id}/vat/regenerate', [AdminBookingController::class, 'regenerateVatInvoice'])->name('bookings.vat.regenerate');



    // Quản lý Tour Booking
    Route::get('tour-bookings/report', [AdminTourBookingController::class, 'report'])->name('tour-bookings.report')->middleware('admin.only');
    Route::patch('tour-bookings/{id}/status', [AdminTourBookingController::class, 'updateStatus'])->name('tour-bookings.update-status');
    Route::patch('tour-bookings/{id}/payments/{paymentId}', [AdminTourBookingController::class, 'updatePaymentStatus'])->name('tour-bookings.payments.update-status');
    Route::post('tour-bookings/{id}/collect-payment', [AdminTourBookingController::class, 'collectPayment'])->name('tour-bookings.collect-payment');
    Route::resource('tour-bookings', AdminTourBookingController::class);

    // Quản lý VAT Invoice Tour Booking
    Route::get('tour-vat-invoices', [AdminTourVatInvoiceController::class, 'index'])->name('tour-vat-invoices.index');
    Route::get('tour-vat-invoices/{id}', [AdminTourVatInvoiceController::class, 'show'])->name('tour-vat-invoices.show');
    Route::post('tour-vat-invoices/{id}/generate', [AdminTourVatInvoiceController::class, 'generateVatInvoice'])->name('tour-vat-invoices.generate');
    Route::post('tour-vat-invoices/{id}/reject', [AdminTourVatInvoiceController::class, 'rejectVatRequest'])->name('tour-vat-invoices.reject');
    Route::get('tour-vat-invoices/{id}/download', [AdminTourVatInvoiceController::class, 'downloadVatInvoice'])->name('tour-vat-invoices.download');
    Route::get('tour-vat-invoices/{id}/preview', [AdminTourVatInvoiceController::class, 'previewVatInvoice'])->name('tour-vat-invoices.preview');
    Route::post('tour-vat-invoices/{id}/send', [AdminTourVatInvoiceController::class, 'sendVatInvoice'])->name('tour-vat-invoices.send');
    Route::post('tour-vat-invoices/{id}/regenerate', [AdminTourVatInvoiceController::class, 'regenerateVatInvoice'])->name('tour-vat-invoices.regenerate');
    Route::get('tour-vat-invoices/statistics', [AdminTourVatInvoiceController::class, 'statistics'])->name('tour-vat-invoices.statistics')->middleware('admin.only');
    Route::post('tour-vat-invoices/{id}/fix-data', [AdminTourVatInvoiceController::class, 'fixVatInvoiceData'])->name('tour-vat-invoices.fix-data');

    // Tour Booking Services routes
    Route::post('tour-bookings/{id}/services', [TourBookingServiceController::class, 'store'])->name('tour-bookings.services.store');
    Route::delete('tour-bookings/{id}/services/{serviceId}', [TourBookingServiceController::class, 'destroy'])->name('tour-bookings.services.destroy');

    // Tour Booking Notes routes
    Route::post('tour-bookings/{id}/notes', [TourBookingNoteController::class, 'store'])->name('tour-bookings.notes.store');
    Route::delete('tour-bookings/{id}/notes/{noteId}', [TourBookingNoteController::class, 'destroy'])->name('tour-bookings.notes.destroy');

    // Quản lý thông báo
    Route::get('notifications', [AdminBookingController::class, 'notificationsIndex'])->name('notifications.index');
    Route::get('notifications/{id}', [AdminBookingController::class, 'notificationShow'])->name('notifications.show');
    Route::patch('notifications/{id}/mark-read', [AdminBookingController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::delete('notifications/{id}', [AdminBookingController::class, 'destroy'])->name('notifications.destroy');

    // API thông báo
    Route::prefix('api/notifications')->name('notifications.')->group(function () {
        Route::get('unread', [AdminBookingController::class, 'getUnreadNotifications'])->name('unread');
        Route::get('count', [AdminBookingController::class, 'getUnreadCount'])->name('count');
        Route::get('list', [AdminBookingController::class, 'getNotifications'])->name('list');
        Route::post('mark-read', [AdminBookingController::class, 'markAsRead'])->name('mark-read');
        Route::post('mark-all-read', [AdminBookingController::class, 'markAllAsRead'])->name('mark-all-read');
        Route::delete('delete', [AdminBookingController::class, 'deleteNotification'])->name('delete');
        Route::delete('delete-read', [AdminBookingController::class, 'deleteReadNotifications'])->name('delete-read');
        Route::delete('delete-old', [AdminBookingController::class, 'deleteOldNotifications'])->name('delete-old');
        Route::post('test', [AdminBookingController::class, 'createTestNotification'])->name('test');
        Route::post('test-note', [AdminBookingController::class, 'createTestNoteNotification'])->name('test-note');
        Route::post('test-review', [AdminBookingController::class, 'createTestReviewNotification'])->name('test-review');
        Route::post('delete-multi', [AdminBookingController::class, 'deleteNotifications'])->name('delete-multi');
        Route::post('mark-read-multi', [AdminBookingController::class, 'markNotificationsAsRead'])->name('mark-read-multi');
    });

    // Quản lý dịch vụ booking
    Route::post('bookings/{id}/services/add', [AdminBookingController::class, 'addServiceToBooking'])->name('bookings.services.add');
    Route::delete('bookings/{id}/services/{bookingServiceId}', [AdminBookingController::class, 'destroyServiceFromBooking'])->name('bookings.services.destroy');
    Route::post('bookings/{id}/confirm-payment', [AdminBookingController::class, 'confirmPayment'])->name('bookings.confirm-payment');
    // Thu tiền phát sinh tại quầy (COD)
    Route::post('bookings/{id}/payments/collect', [AdminBookingController::class, 'collectAdditionalPayment'])->name('bookings.payments.collect');

    // Bulk action (POST)
    Route::post('notifications/delete-multi', [AdminBookingController::class, 'deleteMulti'])->name('notifications.delete-multi');
    Route::post('notifications/mark-read-multi', [AdminBookingController::class, 'markReadMulti'])->name('notifications.mark-read-multi');

    // Quản lý phòng
    // Staff có thể xem danh sách/chi tiết, nhưng chỉ admin/super_admin mới tạo/sửa/xóa
    Route::resource('rooms', AdminRoomController::class)->only(['index', 'show']);
    Route::middleware('admin.only')->group(function () {
        Route::resource('rooms', AdminRoomController::class)->only(['create', 'store', 'edit', 'update', 'destroy']);
    });

    Route::get('/support', [AdminSupportController::class, 'index'])->name('support.index');
    Route::get('/support/conversation/{conversationId}', [AdminSupportController::class, 'showConversation'])->name('support.showConversation');
    Route::post('/support/conversation/{conversationId}/message', [AdminSupportController::class, 'sendMessage'])->name('support.sendMessage');
    Route::get('/support/conversation/{conversationId}/messages', [AdminSupportController::class, 'getNewMessages'])->name('support.getNewMessages');
    Route::post('/support/conversations/updates', [AdminSupportController::class, 'getUpdates'])->name('support.getUpdates');

    // Routes cho quản lý ảnh phòng (chỉ admin)
    Route::delete('rooms/{room}/images/{image}', [AdminRoomController::class, 'deleteImage'])->name('rooms.images.delete')->middleware('admin.only');
    Route::post('rooms/{room}/images/{image}/primary', [AdminRoomController::class, 'setPrimaryImage'])->name('rooms.images.primary')->middleware('admin.only');

    // Quản lý đánh giá loại phòng
    Route::get('room-type-reviews/statistics', [AdminRoomTypeReviewController::class, 'statistics'])->name('room-type-reviews.statistics')->middleware('admin.only');
    Route::patch('room-type-reviews/{id}/approve', [AdminRoomTypeReviewController::class, 'approve'])->name('room-type-reviews.approve');
    Route::patch('room-type-reviews/{id}/reject', [AdminRoomTypeReviewController::class, 'reject'])->name('room-type-reviews.reject');
    Route::get('room-type-reviews', [AdminRoomTypeReviewController::class, 'index'])->name('room-type-reviews.index');
    Route::get('room-type-reviews/create', [AdminRoomTypeReviewController::class, 'create'])->name('room-type-reviews.create');
    Route::post('room-type-reviews', [AdminRoomTypeReviewController::class, 'store'])->name('room-type-reviews.store');
    Route::get('room-type-reviews/{review}', [AdminRoomTypeReviewController::class, 'show'])->name('room-type-reviews.show');
    Route::delete('room-type-reviews/{review}', [AdminRoomTypeReviewController::class, 'destroy'])->name('room-type-reviews.destroy');

    // Quản lý người dùng (chỉ admin)
    Route::middleware('admin.only')->group(function () {
        Route::resource('users', AdminUserController::class)->except(['create', 'store']);
    });

    // Quản lý khuyến mãi
    Route::resource('promotions', \App\Http\Controllers\Admin\AdminPromotionController::class);

    // Quản lý danh mục dịch vụ (Service Categories) - chỉ admin
    Route::middleware('admin.only')->group(function () {
        Route::get('service-categories', [AdminServiceCategoryController::class, 'index'])->name('service-categories.index');
        Route::get('service-categories/create', [AdminServiceCategoryController::class, 'create'])->name('service-categories.create');
        Route::post('service-categories', [AdminServiceCategoryController::class, 'store'])->name('service-categories.store');
        Route::get('service-categories/{service_category}/edit', [AdminServiceCategoryController::class, 'edit'])->name('service-categories.edit');
        Route::put('service-categories/{service_category}', [AdminServiceCategoryController::class, 'update'])->name('service-categories.update');
        Route::delete('service-categories/{service_category}', [AdminServiceCategoryController::class, 'destroy'])->name('service-categories.destroy');
    });

    // Quản lý dịch vụ (Services) - chỉ admin
    Route::middleware('admin.only')->group(function () {
        Route::get('services', [AdminServiceController::class, 'index'])->name('services.index');
        Route::get('services/create', [AdminServiceController::class, 'create'])->name('services.create');
        Route::post('services', [AdminServiceController::class, 'store'])->name('services.store');
        Route::get('services/{service}/edit', [AdminServiceController::class, 'edit'])->name('services.edit');
        Route::put('services/{service}', [AdminServiceController::class, 'update'])->name('services.update');
        Route::delete('services/{service}', [AdminServiceController::class, 'destroy'])->name('services.destroy');
    });

    // Gán dịch vụ cho loại phòng (Room Type Services) - chỉ admin
    Route::middleware('admin.only')->group(function () {
        Route::get('room-type-services', [AdminRoomTypeServiceController::class, 'index'])->name('room-type-services.index');
        Route::get('room-type-services/{room_type}/edit', [AdminRoomTypeServiceController::class, 'edit'])->name('room-type-services.edit');
        Route::put('room-type-services/{room_type}', [AdminRoomTypeServiceController::class, 'update'])->name('room-type-services.update');
    });

    // Quản lý yêu cầu đổi phòng
    Route::get('room-changes', [AdminRoomChangeController::class, 'index'])->name('room-changes.index');
    Route::get('room-changes/{roomChange}', [AdminRoomChangeController::class, 'show'])->name('room-changes.show');
    Route::post('room-changes/{roomChange}/approve', [AdminRoomChangeController::class, 'approve'])->name('room-changes.approve');
    Route::post('room-changes/{roomChange}/reject', [AdminRoomChangeController::class, 'reject'])->name('room-changes.reject');
    Route::post('room-changes/{roomChange}/complete', [AdminRoomChangeController::class, 'complete'])->name('room-changes.complete');
    Route::post('room-changes/{roomChange}/mark-paid', [AdminRoomChangeController::class, 'markAsPaid'])->name('room-changes.mark-paid');
    Route::post('room-changes/{roomChange}/mark-refunded', [AdminRoomChangeController::class, 'markAsRefunded'])->name('room-changes.mark-refunded');
    Route::get('room-changes/statistics', [AdminRoomChangeController::class, 'statistics'])->name('room-changes.statistics');
    Route::post('room-changes/{roomChange}/update-status', [AdminRoomChangeController::class, 'updateStatus'])->name('room-changes.update-status');

    // Quản lý dịch vụ bổ sung (Extra Services) - chỉ admin
    Route::middleware('admin.only')->group(function () {
        Route::get('extra-services', [AdminExtraServiceController::class, 'index'])->name('extra-services.index');
        Route::get('extra-services/create', [AdminExtraServiceController::class, 'create'])->name('extra-services.create');
        Route::post('extra-services', [AdminExtraServiceController::class, 'store'])->name('extra-services.store');
        Route::get('extra-services/{extra_service}/edit', [AdminExtraServiceController::class, 'edit'])->name('extra-services.edit');
        Route::put('extra-services/{extra_service}', [AdminExtraServiceController::class, 'update'])->name('extra-services.update');
        Route::delete('extra-services/{extra_service}', [AdminExtraServiceController::class, 'destroy'])->name('extra-services.destroy');
    });
});

// Route công khai cho room type reviews (chỉ hiển thị) - đặt sau admin routes để tránh xung đột
Route::get('/room-type-reviews/{roomTypeId}/ajax', [RoomTypeReviewController::class, 'getReviewsAjax'])->name('room-type-reviews.ajax');

// Promotions - client
Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
Route::get('/promotions/{promotion}', [PromotionController::class, 'show'])->name('promotions.show');
Route::post('/promotion/check', [PromotionController::class, 'checkPromotion'])->name('promotion.check');

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
Route::get('/payment-method/{booking?}', [PaymentController::class, 'paymentMethod'])->name('payment-method');
Route::post('/ajax-booking', [BookingController::class, 'ajaxStoreBooking'])->name('ajax-booking');
Route::get('/api/room-type/{id}/image', [BookingController::class, 'getRoomTypeImage'])->name('api.room-type.image');
// Payment promotion preview API
Route::get('/api/payment/promotion-preview/{booking}', [PaymentController::class, 'promotionPreview'])->name('api.payment.promotion-preview');

// Room type promotion preview API
Route::get('/api/room-type/promotion-preview', [HotelController::class, 'promotionPreviewForRoomType'])->name('api.room-type.promotion-preview');


Route::get('/payment/bank-transfer/{booking}', [PaymentController::class, 'processBankTransfer'])->name('payment.bank-transfer');
Route::post('/payment/bank-transfer/{booking}/confirm', [PaymentController::class, 'confirmBankTransfer'])->name('payment.bank-transfer.confirm');
Route::get('/payment/credit-card/{booking}', [PaymentController::class, 'processCreditCard'])->name('payment.credit-card');
Route::post('/payment/credit-card/{booking}/confirm', [PaymentController::class, 'confirmCreditCard'])->name('payment.credit-card.confirm');
Route::get('/payment/success/{booking}', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/failed', [PaymentController::class, 'failed'])->name('payment.failed');
Route::get('/payment/history/{booking}', [PaymentController::class, 'history'])->name('payment.history');

// User Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/user/profile', [UserProfileController::class, 'showUserProfile'])->name('user.profile');
    Route::post('/user/profile/update', [UserProfileController::class, 'updateUserProfile'])->name('user.profile.update');
    Route::post('/user/profile/change-password', [UserProfileController::class, 'changeUserPassword'])->name('user.profile.change_password');
    Route::get('/user/bookings', [UserProfileController::class, 'showUserBookings'])->name('user.bookings');
    Route::get('/user/bookings/partial', [BookingController::class, 'partial'])->name('user.bookings.partial');
    Route::get('/user/bookings/{id}/detail', [BookingController::class, 'detailPartial'])->name('user.bookings.detail.partial');
    Route::get('/user/notes/partial', [BookingController::class, 'notesPartial'])->name('user.notes.partial');
    Route::get('/user/reviews', [UserProfileController::class, 'showUserReviews'])->name('user.reviews');
    Route::get('/user/reviews/partial', [UserProfileController::class, 'partialReviews'])->name('user.reviews.partial');
    Route::get('/user/reviews/{id}/detail', [UserProfileController::class, 'reviewDetail'])->name('user.reviews.detail');
    Route::get('/user/reviews/{id}/data', [UserProfileController::class, 'getReviewData'])->name('user.reviews.data');
    Route::post('/user/reviews', [UserProfileController::class, 'storeReview'])->name('user.reviews.store');
    Route::put('/user/reviews/{id}', [UserProfileController::class, 'updateReview'])->name('user.reviews.update');
    Route::delete('/user/reviews/{id}', [UserProfileController::class, 'deleteReview'])->name('user.reviews.delete');
    Route::get('/user/promotions', [UserProfileController::class, 'promotions'])->name('user.promotions');

    // Booking Promotion Routes
    Route::prefix('bookings/{booking}/promotions')->name('bookings.promotions.')->group(function () {
        Route::post('/apply', [BookingPromotionController::class, 'applyPromotion'])->name('apply');
        Route::delete('/remove', [BookingPromotionController::class, 'removePromotion'])->name('remove');
        Route::get('/available', [BookingPromotionController::class, 'getAvailablePromotions'])->name('available');
        Route::get('/applied', [BookingPromotionController::class, 'getAppliedPromotion'])->name('applied');
    });

    // Room Change Routes
    Route::get('/booking/{booking}/room-change/request', [RoomChangeController::class, 'showRequestForm'])->name('room-change.request');
    Route::post('/booking/{booking}/room-change/request', [RoomChangeController::class, 'storeRequest'])->name('room-change.store');
    Route::get('/booking/{booking}/room-change/history', [RoomChangeController::class, 'showHistory'])->name('room-change.history');
    Route::get('/booking/{booking}/room-change/available-rooms', [RoomChangeController::class, 'getAvailableRooms'])->name('room-change.available-rooms');
    Route::post('/booking/{booking}/room-change/calculate-price', [RoomChangeController::class, 'calculatePriceDifference'])->name('room-change.calculate-price');
});
