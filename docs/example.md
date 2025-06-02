### Ví dụ minh họa: Chức năng Quản lý Phòng

#### 1. Tạo Interface

```php
// app/Interfaces/Repositories/RoomRepositoryInterface.php
<?php

namespace App\Interfaces\Repositories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

interface RoomRepositoryInterface
{
    public function getAll(): Collection;
    public function getById(int $id): ?Room;
    public function getAvailableRooms(string $checkInDate, string $checkOutDate): Collection;
    public function create(array $data): Room;
    public function update(Room $room, array $data): Room;
    public function delete(int $id): bool;
}

// app/Interfaces/Services/RoomServiceInterface.php
<?php

namespace App\Interfaces\Services;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

interface RoomServiceInterface
{
    public function getAllRooms(): Collection;
    public function getRoomById(int $id): ?Room;
    public function getAvailableRooms(string $checkInDate, string $checkOutDate): Collection;
    public function createRoom(array $data): Room;
    public function updateRoom(int $id, array $data): Room;
    public function deleteRoom(int $id): bool;
    public function checkRoomAvailability(int $roomId, string $checkInDate, string $checkOutDate): bool;
}
```

#### 2. Triển khai Repository

```php
// app/Repositories/RoomRepository.php
<?php

namespace App\Repositories;

use App\Interfaces\Repositories\RoomRepositoryInterface;
use App\Models\Room;
use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

class RoomRepository implements RoomRepositoryInterface
{
    protected $model;

    public function __construct(Room $room)
    {
        $this->model = $room;
    }

    public function getAll(): Collection
    {
        return $this->model->with('roomType')->get();
    }

    public function getById(int $id): ?Room
    {
        return $this->model->with('roomType')->find($id);
    }

    public function getAvailableRooms(string $checkInDate, string $checkOutDate): Collection
    {
        // Lấy ID của các phòng đã đặt trong khoảng thời gian này
        $bookedRoomIds = Booking::where(function($query) use ($checkInDate, $checkOutDate) {
            $query->whereBetween('check_in_date', [$checkInDate, $checkOutDate])
                ->orWhereBetween('check_out_date', [$checkInDate, $checkOutDate])
                ->orWhere(function($q) use ($checkInDate, $checkOutDate) {
                    $q->where('check_in_date', '<=', $checkInDate)
                      ->where('check_out_date', '>=', $checkOutDate);
                });
        })
        ->where('status', '!=', 'cancelled')
        ->pluck('room_id')
        ->toArray();

        // Lấy tất cả phòng trừ các phòng đã đặt và phòng đang sửa chữa
        return $this->model->with('roomType')
            ->whereNotIn('id', $bookedRoomIds)
            ->where('status', 'available')
            ->get();
    }

    public function create(array $data): Room
    {
        return $this->model->create($data);
    }

    public function update(Room $room, array $data): Room
    {
        $room->update($data);
        return $room->fresh();
    }

    public function delete(int $id): bool
    {
        return $this->model->findOrFail($id)->delete();
    }
}
```

#### 3. Triển khai Service

```php
// app/Services/RoomService.php
<?php

namespace App\Services;

use App\Interfaces\Repositories\RoomRepositoryInterface;
use App\Interfaces\Services\RoomServiceInterface;
use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class RoomService implements RoomServiceInterface
{
    protected $roomRepository;

    public function __construct(RoomRepositoryInterface $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    public function getAllRooms(): Collection
    {
        return $this->roomRepository->getAll();
    }

    public function getRoomById(int $id): ?Room
    {
        return $this->roomRepository->getById($id);
    }

    public function getAvailableRooms(string $checkInDate, string $checkOutDate): Collection
    {
        return $this->roomRepository->getAvailableRooms($checkInDate, $checkOutDate);
    }

    public function createRoom(array $data): Room
    {
        // Xử lý upload hình ảnh nếu có
        if (isset($data['image']) && $data['image']) {
            $data['image_url'] = $this->uploadImage($data['image']);
        }

        return $this->roomRepository->create($data);
    }

    public function updateRoom(int $id, array $data): Room
    {
        $room = $this->roomRepository->getById($id);
        
        if (!$room) {
            throw new \Exception('Phòng không tồn tại');
        }

        // Xử lý upload hình ảnh nếu có
        if (isset($data['image']) && $data['image']) {
            // Xóa hình ảnh cũ nếu có
            if ($room->image_url) {
                Storage::delete('public/' . $room->image_url);
            }
            
            $data['image_url'] = $this->uploadImage($data['image']);
        }

        return $this->roomRepository->update($room, $data);
    }

    public function deleteRoom(int $id): bool
    {
        $room = $this->roomRepository->getById($id);
        
        if (!$room) {
            throw new \Exception('Phòng không tồn tại');
        }

        // Xóa hình ảnh nếu có
        if ($room->image_url) {
            Storage::delete('public/' . $room->image_url);
        }

        return $this->roomRepository->delete($id);
    }

    public function checkRoomAvailability(int $roomId, string $checkInDate, string $checkOutDate): bool
    {
        $availableRooms = $this->roomRepository->getAvailableRooms($checkInDate, $checkOutDate);
        return $availableRooms->contains('id', $roomId);
    }

    /**
     * Upload hình ảnh và trả về đường dẫn
     */
    private function uploadImage($image): string
    {
        $path = $image->store('rooms', 'public');
        return $path;
    }
}
```

#### 4. Tạo Controller

```php
// app/Http/Controllers/Admin/RoomController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\RoomServiceInterface;
use App\Interfaces\Services\RoomTypeServiceInterface;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected $roomService;
    protected $roomTypeService;

    public function __construct(
        RoomServiceInterface $roomService,
        RoomTypeServiceInterface $roomTypeService
    ) {
        $this->roomService = $roomService;
        $this->roomTypeService = $roomTypeService;
    }

    public function index()
    {
        $rooms = $this->roomService->getAllRooms();
        return view('admin.rooms.index', compact('rooms'));
    }

    public function create()
    {
        $roomTypes = $this->roomTypeService->getAllRoomTypes();
        return view('admin.rooms.create', compact('roomTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:20|unique:rooms',
            'status' => 'required|in:available,booked,repair',
            'image' => 'nullable|image|max:2048',
        ]);

        $this->roomService->createRoom($validated);
        
        return redirect()->route('admin.rooms.index')
            ->with('success', 'Phòng đã được tạo thành công');
    }

    public function edit($id)
    {
        $room = $this->roomService->getRoomById($id);
        $roomTypes = $this->roomTypeService->getAllRoomTypes();
        
        return view('admin.rooms.edit', compact('room', 'roomTypes'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_number' => 'required|string|max:20|unique:rooms,room_number,' . $id,
            'status' => 'required|in:available,booked,repair',
            'image' => 'nullable|image|max:2048',
        ]);

        $this->roomService->updateRoom($id, $validated);
        
        return redirect()->route('admin.rooms.index')
            ->with('success', 'Phòng đã được cập nhật thành công');
    }

    public function destroy($id)
    {
        $this->roomService->deleteRoom($id);
        
        return redirect()->route('admin.rooms.index')
            ->with('success', 'Phòng đã được xóa thành công');
    }
}

// app/Http/Controllers/Client/RoomController.php
<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\RoomServiceInterface;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    protected $roomService;

    public function __construct(RoomServiceInterface $roomService)
    {
        $this->roomService = $roomService;
    }

    public function index(Request $request)
    {
        $checkIn = $request->input('check_in');
        $checkOut = $request->input('check_out');
        
        if ($checkIn && $checkOut) {
            $rooms = $this->roomService->getAvailableRooms($checkIn, $checkOut);
        } else {
            $rooms = $this->roomService->getAllRooms();
        }
        
        return view('client.rooms', compact('rooms', 'checkIn', 'checkOut'));
    }

    public function show($id)
    {
        $room = $this->roomService->getRoomById($id);
        
        if (!$room) {
            return redirect()->route('client.rooms')
                ->with('error', 'Phòng không tồn tại');
        }
        
        return view('client.rooms-single', compact('room'));
    }

    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);
        
        $isAvailable = $this->roomService->checkRoomAvailability(
            $validated['room_id'],
            $validated['check_in'],
            $validated['check_out']
        );
        
        return response()->json(['available' => $isAvailable]);
    }
}
```

#### 5. Đăng ký Binding

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    // Đăng ký Repository Bindings
    $this->app->bind(
        \App\Interfaces\Repositories\UserRepositoryInterface::class,
        \App\Repositories\UserRepository::class
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
        \App\Interfaces\Services\RoomServiceInterface::class,
        \App\Services\RoomService::class
    );
}
```

#### 6. Tạo Routes

```php
// routes/web.php

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::resource('rooms', \App\Http\Controllers\Admin\RoomController::class);
});

// Client Routes
Route::get('/rooms', [\App\Http\Controllers\Client\RoomController::class, 'index'])->name('rooms');
Route::get('/rooms/{id}', [\App\Http\Controllers\Client\RoomController::class, 'show'])->name('rooms.show');
Route::post('/rooms/check-availability', [\App\Http\Controllers\Client\RoomController::class, 'checkAvailability'])
    ->name('rooms.check-availability');
```

## 4. Tương tự cho các chức năng khác

### Đặt phòng (Booking)

#### Interfaces
- `BookingRepositoryInterface`
- `BookingServiceInterface`

#### Implementations
- `BookingRepository`
- `BookingService`

#### Controllers
- `Admin\BookingController`
- `Client\BookingController`

### Loại phòng (RoomType)

#### Interfaces
- `RoomTypeRepositoryInterface`
- `RoomTypeServiceInterface`

#### Implementations
- `RoomTypeRepository`
- `RoomTypeService`

#### Controllers
- `Admin\RoomTypeController`

### Dịch vụ (Service)

#### Interfaces
- `ServiceRepositoryInterface`
- `ServiceServiceInterface`

#### Implementations
- `ServiceRepository`
- `ServiceService`

#### Controllers
- `Admin\ServiceController`
- `Client\ServiceController`

### Thanh toán (Payment)

#### Interfaces
- `PaymentRepositoryInterface`
- `PaymentServiceInterface`

#### Implementations
- `PaymentRepository`
- `PaymentService`

#### Controllers
- `Admin\PaymentController`
- `Client\PaymentController`


## 6. Luồng xử lý tổng quát

### Ví dụ: Đặt phòng

1. Client gửi request đặt phòng
2. `BookingController` nhận request và validate dữ liệu
3. `BookingController` gọi `BookingService->createBooking()`
4. `BookingService` kiểm tra tính khả dụng của phòng bằng cách gọi `RoomService->checkRoomAvailability()`
5. `BookingService` tạo đơn đặt phòng bằng cách gọi `BookingRepository->create()`
6. `BookingService` gửi email xác nhận đặt phòng
7. `BookingController` trả về response cho client

## 7. Testing

- **Unit Testing**: Test các thành phần riêng lẻ, sử dụng mock object cho dependencies
- **Integration Testing**: Test tương tác giữa các thành phần
- **Feature Testing**: Test các tính năng từ đầu đến cuối

## 8. Kết luận