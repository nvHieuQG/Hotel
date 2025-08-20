# Hệ thống Đặt phòng Tour du lịch

## Tổng quan

Hệ thống đặt phòng tour du lịch cho phép khách hàng đặt phòng cho nhiều người (tour du lịch) với các tính năng:

-   Tìm kiếm phòng phù hợp cho số lượng khách lớn
-   Chọn nhiều loại phòng khác nhau
-   Tính toán giá tự động
-   Quy trình thanh toán hoàn chỉnh
-   Quản lý tour bookings

## Cấu trúc Database

### Bảng `tour_bookings`

-   `id`: Khóa chính
-   `user_id`: ID người dùng
-   `booking_id`: Mã đặt phòng (unique)
-   `tour_name`: Tên tour
-   `total_guests`: Tổng số khách
-   `total_rooms`: Tổng số phòng
-   `check_in_date`: Ngày check-in
-   `check_out_date`: Ngày check-out
-   `total_price`: Tổng tiền
-   `status`: Trạng thái (pending, confirmed, cancelled, completed)
-   `special_requests`: Yêu cầu đặc biệt
-   `tour_details`: Chi tiết tour (JSON)

### Bảng `tour_booking_rooms`

-   `id`: Khóa chính
-   `tour_booking_id`: ID tour booking
-   `room_type_id`: ID loại phòng
-   `quantity`: Số lượng phòng
-   `guests_per_room`: Số khách mỗi phòng
-   `price_per_room`: Giá mỗi phòng
-   `total_price`: Tổng tiền cho loại phòng này
-   `guest_details`: Chi tiết khách (JSON)

## Models

### TourBooking

-   Relationships với User, TourBookingRoom, Payment
-   Methods: generateBookingId(), getStatusTextAttribute(), getTotalNightsAttribute()
-   Accessors: check_in_date_only, check_out_date_only, total_paid

### TourBookingRoom

-   Relationships với TourBooking, RoomType
-   Accessors: total_guests, formatted_price_per_room, formatted_total_price

## Services

### TourBookingService

-   `createTourBooking()`: Tạo tour booking mới
-   `getUserTourBookings()`: Lấy danh sách tour bookings của user
-   `calculateTourBookingPrice()`: Tính toán giá tour booking
-   `checkRoomAvailabilityForTour()`: Kiểm tra tính khả dụng
-   `getAvailableRoomTypesForTour()`: Lấy loại phòng có sẵn

## Controllers

### TourBookingController

-   `searchForm()`: Form tìm kiếm tour
-   `search()`: Xử lý tìm kiếm
-   `selectRooms()`: Chọn phòng
-   `calculatePrice()`: Tính toán giá (AJAX)
-   `confirm()`: Xác nhận đặt phòng
-   `store()`: Lưu tour booking
-   `payment()`: Trang thanh toán
-   `index()`: Danh sách tour bookings
-   `show()`: Chi tiết tour booking

## Routes

```php
// Tour Booking Routes
Route::get('/tour-booking/search', [TourBookingController::class, 'searchForm'])->name('tour-booking.search');
Route::get('/tour-booking/select-rooms', [TourBookingController::class, 'selectRooms'])->name('tour-booking.select-rooms');
Route::post('/tour-booking/calculate-price', [TourBookingController::class, 'calculatePrice'])->name('tour-booking.calculate-price');
Route::post('/tour-booking/confirm', [TourBookingController::class, 'confirm'])->name('tour-booking.confirm');
Route::post('/tour-booking/store', [TourBookingController::class, 'store'])->name('tour-booking.store');
Route::get('/tour-booking/payment/{bookingId}', [TourBookingController::class, 'payment'])->name('tour-booking.payment');
Route::get('/tour-booking', [TourBookingController::class, 'index'])->name('tour-booking.index');
Route::get('/tour-booking/{id}', [TourBookingController::class, 'show'])->name('tour-booking.show');
```

## Views

### Trang tìm kiếm (`search.blade.php`)

-   Form nhập thông tin tour
-   Chọn số khách, ngày check-in/out
-   Hướng dẫn quy trình

### Trang chọn phòng (`select-rooms.blade.php`)

-   Hiển thị danh sách loại phòng có sẵn
-   Chọn số lượng và số khách mỗi phòng
-   Tính toán giá real-time
-   Tóm tắt đặt phòng

### Trang xác nhận (`confirm.blade.php`)

-   Hiển thị thông tin tour
-   Chi tiết phòng đã chọn
-   Form yêu cầu đặc biệt
-   Tóm tắt cuối cùng

### Trang thanh toán (`payment.blade.php`)

-   Thông tin tour booking
-   Chi tiết phòng
-   Phương thức thanh toán
-   Form thanh toán

### Danh sách tour bookings (`index.blade.php`)

-   Hiển thị tất cả tour bookings của user
-   Trạng thái và thông tin cơ bản
-   Link đến chi tiết và thanh toán

### Chi tiết tour booking (`show.blade.php`)

-   Thông tin đầy đủ tour booking
-   Chi tiết phòng đã đặt
-   Lịch sử thanh toán
-   Hành động (thanh toán, xem chi tiết)

## Quy trình sử dụng

1. **Tìm kiếm**: User nhập thông tin tour (tên, số khách, ngày)
2. **Chọn phòng**: Hệ thống hiển thị loại phòng phù hợp, user chọn số lượng
3. **Tính toán**: Hệ thống tính toán giá tự động
4. **Xác nhận**: User kiểm tra và xác nhận thông tin
5. **Thanh toán**: User chọn phương thức và thực hiện thanh toán
6. **Quản lý**: User có thể xem danh sách và chi tiết tour bookings

## Tính năng đặc biệt

-   **Tính toán giá tự động**: Dựa trên số đêm và loại phòng
-   **Kiểm tra tính khả dụng**: Đảm bảo phòng có sẵn cho ngày đặt
-   **Quản lý trạng thái**: Theo dõi trạng thái tour booking
-   **Thanh toán linh hoạt**: Hỗ trợ nhiều phương thức thanh toán
-   **Giao diện responsive**: Tương thích với mọi thiết bị
-   **Validation**: Kiểm tra dữ liệu đầu vào chặt chẽ

## Cấu hình

### Service Provider

Đăng ký các service và repository trong `AppServiceProvider`:

```php
$this->app->bind(
    \App\Interfaces\Repositories\TourBookingRepositoryInterface::class,
    \App\Repositories\TourBookingRepository::class
);

$this->app->bind(
    \App\Interfaces\Services\TourBookingServiceInterface::class,
    \App\Services\TourBookingService::class
);
```

### Navigation

Thêm link "Đặt Tour" vào navigation header và dropdown menu user.

## Seeder

Chạy seeder để tạo dữ liệu mẫu:

```bash
php artisan db:seed --class=TourBookingSeeder
```

## Migration

Chạy migration để tạo bảng:

```bash
php artisan migrate
```

## Lưu ý

-   Cần có user đăng nhập để sử dụng tính năng
-   Cần có room types trong database
-   Hệ thống tự động kiểm tra tính khả dụng của phòng
-   Mã booking được tạo tự động với format: TOUR + YYYYMMDD + 6 ký tự
