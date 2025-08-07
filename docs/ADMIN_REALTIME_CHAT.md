# Admin Realtime Chat System

## Tổng quan
Hệ thống realtime chat cho admin cho phép admin trả lời và giao tiếp trực tuyến với khách hàng thông qua giao diện admin panel.

## Tính năng chính

### 1. Giao diện Admin Chat
- **Trạng thái realtime**: Hiển thị badge Online/Offline
- **Lịch sử chat**: Hiển thị tất cả tin nhắn với timestamp
- **Form gửi tin nhắn**: Textarea với validation và loading state
- **Auto-scroll**: Tự động scroll xuống tin nhắn mới nhất

### 2. Realtime Features
- **Polling tự động**: Kiểm tra tin nhắn mới mỗi 3 giây
- **Gửi tin nhắn realtime**: Không cần refresh trang
- **Nhận tin nhắn realtime**: Tự động cập nhật khi user gửi tin nhắn
- **Tránh duplicate**: Kiểm tra tin nhắn đã tồn tại trước khi thêm

### 3. UX Improvements
- **Loading state**: Hiển thị spinner khi đang gửi tin nhắn
- **Enter key support**: Gửi tin nhắn bằng Enter (Shift+Enter để xuống dòng)
- **Validation**: Kiểm tra tin nhắn rỗng ở cả client và server
- **Error handling**: Hiển thị thông báo lỗi khi gửi tin nhắn thất bại

## Luồng hoạt động

### 1. Khởi tạo Chat
```
Admin mở ticket → Khởi tạo lastMessageId → Bắt đầu polling → Hiển thị trạng thái Online
```

### 2. Gửi tin nhắn
```
Admin nhập tin nhắn → Validation → Gửi AJAX request → Cập nhật UI → Bắt đầu realtime (nếu cần)
```

### 3. Nhận tin nhắn
```
Polling mỗi 3s → Kiểm tra tin nhắn mới → Thêm vào UI → Cập nhật lastMessageId → Auto-scroll
```

### 4. Đóng chat
```
Admin rời trang → Dừng polling → Hiển thị trạng thái Offline → Giải phóng tài nguyên
```

## API Endpoints

### Gửi tin nhắn (Admin)
```
POST /admin/support/ticket/{id}/message
Content-Type: application/json

{
    "message": "Nội dung tin nhắn",
    "_token": "CSRF token"
}

Response:
{
    "success": true,
    "message_id": 123,
    "message": "Tin nhắn đã được gửi"
}
```

### Lấy tin nhắn mới (Admin)
```
GET /admin/support/ticket/{id}/messages?last_id=100
Accept: application/json

Response:
{
    "success": true,
    "messages": [
        {
            "id": 101,
            "message": "Tin nhắn mới",
            "sender_type": "user",
            "created_at": "2024-01-01 12:00:00"
        }
    ]
}
```

## Cấu trúc Database

### SupportTicket
- `id`: ID ticket
- `user_id`: ID khách hàng
- `subject`: Chủ đề ticket
- `status`: Trạng thái (open/closed)
- `created_at`, `updated_at`: Timestamp

### SupportMessage
- `id`: ID tin nhắn
- `ticket_id`: ID ticket
- `sender_id`: ID người gửi
- `sender_type`: Loại người gửi (user/admin)
- `message`: Nội dung tin nhắn
- `created_at`: Thời gian gửi

## Bảo mật

### 1. Authentication
- Chỉ admin đã đăng nhập mới có thể truy cập
- Middleware `auth` và `admin` được áp dụng

### 2. Authorization
- Admin có thể xem tất cả ticket
- Không cần kiểm tra quyền sở hữu ticket

### 3. CSRF Protection
- Tất cả requests đều có CSRF token
- Validation token ở server side

### 4. Input Validation
- Validation tin nhắn rỗng
- Sanitize input để tránh XSS
- Kiểm tra độ dài tin nhắn

## Performance

### 1. Polling Optimization
- Chỉ polling khi có ticket
- Interval 3 giây (có thể điều chỉnh)
- Dừng polling khi rời trang

### 2. Database Optimization
- Chỉ lấy tin nhắn mới (WHERE id > last_id)
- Sử dụng index trên ticket_id và created_at
- Limit số lượng tin nhắn trả về

### 3. UI Optimization
- Không re-render toàn bộ chat
- Chỉ thêm tin nhắn mới vào DOM
- Sử dụng data attributes để tracking

## Monitoring & Debugging

### 1. Console Logging
- Log lỗi khi polling thất bại
- Log thông tin tin nhắn mới
- Log trạng thái realtime

### 2. Network Monitoring
- Theo dõi số lượng requests
- Kiểm tra response time
- Phát hiện lỗi network

### 3. Error Handling
- Hiển thị thông báo lỗi user-friendly
- Fallback khi realtime không hoạt động
- Retry mechanism cho failed requests

## Cải tiến có thể thực hiện

### 1. WebSocket
- Thay thế polling bằng WebSocket
- Real-time hơn và ít overhead
- Hỗ trợ typing indicator

### 2. Push Notifications
- Thông báo khi có tin nhắn mới
- Desktop notifications
- Email notifications

### 3. File Upload
- Hỗ trợ gửi file trong chat
- Image preview
- File size validation

### 4. Chat History
- Pagination cho tin nhắn cũ
- Search tin nhắn
- Export chat history

### 5. Multi-ticket Support
- Chat với nhiều ticket cùng lúc
- Tab interface
- Quick switch giữa các ticket 
