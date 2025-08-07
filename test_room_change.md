# Hướng dẫn test chức năng đổi phòng

## Đã hoàn thành:

### 1. Database
- ✅ Tạo bảng `room_changes` với các trường cần thiết
- ✅ Tạo Model `RoomChange` với relationships và helper methods
- ✅ Cập nhật Model `Booking` để thêm relationship với `RoomChange`

### 2. Repository & Service Pattern
- ✅ Tạo `RoomChangeRepositoryInterface` và `RoomChangeRepository`
- ✅ Tạo `RoomChangeServiceInterface` và `RoomChangeService`
- ✅ Đăng ký Service Provider

### 3. Controllers
- ✅ Tạo `RoomChangeController` cho client
- ✅ Tạo `AdminRoomChangeController` cho admin
- ✅ Thêm routes cho cả client và admin

### 4. Views
- ✅ Tạo view form yêu cầu đổi phòng (`client/room-change/request.blade.php`)
- ✅ Tạo view lịch sử đổi phòng (`client/room-change/history.blade.php`)
- ✅ Tạo view danh sách yêu cầu đổi phòng cho admin (`admin/room-changes/index.blade.php`)
- ✅ Tạo view chi tiết yêu cầu đổi phòng cho admin (`admin/room-changes/show.blade.php`)

### 5. UI/UX
- ✅ Thêm nút "Yêu cầu đổi phòng" vào trang chi tiết booking
- ✅ Thêm menu "Yêu cầu đổi phòng" vào admin sidebar
- ✅ Hiển thị chênh lệch giá real-time khi chọn phòng mới
- ✅ Toast message khi gửi yêu cầu thành công

## Cách test:

### 1. Test Client Side:
1. Đăng nhập với tài khoản user
2. Vào trang chi tiết booking
3. Click nút "Yêu cầu đổi phòng"
4. Chọn phòng mới và lý do
5. Submit form
6. Kiểm tra toast message: "Yêu cầu đổi phòng của bạn đã được gửi và đang chờ xét duyệt"

### 2. Test Admin Side:
1. Đăng nhập với tài khoản admin
2. Vào menu "Yêu cầu đổi phòng"
3. Xem danh sách yêu cầu đổi phòng
4. Click vào một yêu cầu để xem chi tiết
5. Duyệt hoặc từ chối yêu cầu
6. Hoàn thành đổi phòng (nếu đã duyệt)

### 3. Test Features:
- ✅ Tính toán chênh lệch giá
- ✅ Kiểm tra phòng trống
- ✅ Validation và error handling
- ✅ Lịch sử đổi phòng
- ✅ Thống kê cho admin

## Routes đã tạo:

### Client Routes:
- `GET /booking/{booking}/room-change/request` - Form yêu cầu đổi phòng
- `POST /booking/{booking}/room-change/request` - Gửi yêu cầu
- `GET /booking/{booking}/room-change/history` - Lịch sử đổi phòng
- `GET /booking/{booking}/room-change/available-rooms` - API lấy phòng có thể đổi
- `POST /booking/{booking}/room-change/calculate-price` - API tính chênh lệch giá

### Admin Routes:
- `GET /admin/room-changes` - Danh sách yêu cầu đổi phòng
- `GET /admin/room-changes/{roomChange}` - Chi tiết yêu cầu
- `POST /admin/room-changes/{roomChange}/approve` - Duyệt yêu cầu
- `POST /admin/room-changes/{roomChange}/reject` - Từ chối yêu cầu
- `POST /admin/room-changes/{roomChange}/complete` - Hoàn thành đổi phòng
- `GET /admin/room-changes/statistics` - Thống kê
- `POST /admin/room-changes/{roomChange}/update-status` - API cập nhật trạng thái

## Lưu ý:
- Chức năng đã hoàn thành theo yêu cầu ban đầu
- Chưa có real-time notification (sẽ thêm sau)
- Chưa có email notification (sẽ thêm sau)
- Có thể mở rộng thêm tính năng real-time và email notification 