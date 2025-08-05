# Hệ Thống Realtime Chat

## Tổng quan
Hệ thống realtime chat cho phép khách hàng và admin giao tiếp trực tuyến thông qua widget chat được tích hợp vào trang web.

## Luồng hoạt động

### 1. Khởi tạo Chat Widget
- Khi người dùng đã đăng nhập, chat widget sẽ hiển thị ở góc phải dưới của trang
- Widget hiển thị lịch sử chat cũ (nếu có) hoặc thông báo "Chưa có cuộc trò chuyện nào"

### 2. Gửi tin nhắn đầu tiên
- Khi người dùng gửi tin nhắn đầu tiên:
  - **Luồng mới**: Tạo ticket và tin nhắn đầu tiên cùng lúc trong một transaction
  - **Luồng cũ**: Tạo ticket trước → rồi mới gửi tin nhắn (đã loại bỏ)
- Tin nhắn được gửi qua AJAX đến endpoint `/support/ticket` (không cần ticket ID)
- Server tự động tạo ticket với subject "Hỗ trợ nhanh" và lưu tin nhắn đầu tiên
- Response trả về JSON với thông tin ticket_id và message_id
- Tin nhắn được thêm vào UI ngay lập tức

### 3. Realtime Chat (Polling)
- Sau khi gửi tin nhắn đầu tiên, hệ thống bắt đầu polling
- Mỗi 3 giây, client gửi request đến `/support/ticket/{id}/messages?last_id={last_message_id}`
- Server kiểm tra và trả về các tin nhắn mới (có ID lớn hơn last_id)
- Tin nhắn mới được thêm vào UI tự động

### 4. Luồng dữ liệu

#### Client Side (JavaScript)
```javascript
// 1. Gửi tin nhắn (với protection trùng lặp)
if(!isSending) {
  isSending = true;
  // Tin nhắn đầu tiên: POST /support/ticket (không cần ticket ID)
  // Tin nhắn tiếp theo: POST /support/ticket/{id}/message
  sendMessage(message)
  isSending = false;
}

// 2. Polling tin nhắn mới (chỉ khi có ticket)
if(ticketId && hasMessages) {
  setInterval(() => {
    checkNewMessages() -> GET /support/ticket/{id}/messages?last_id={id}
  }, 3000);
}
```

#### Server Side (Laravel)
```php
// 1. Xử lý gửi tin nhắn (User)
SupportController::sendMessage() -> SupportService::sendMessage()

// 2. Lấy tin nhắn mới (User)
SupportController::getNewMessages() -> SupportService::getNewMessages()

// 3. Xử lý gửi tin nhắn (Admin)
AdminSupportController::sendMessage() -> SupportService::sendMessage()

// 4. Lấy tin nhắn mới (Admin)
AdminSupportController::getNewMessages() -> SupportService::getNewMessages()
```

### 5. Cấu trúc Database

#### SupportTicket
- id, user_id, subject, status, created_at, updated_at

#### SupportMessage
- id, ticket_id, sender_id, sender_type, message, created_at

### 6. Bảo mật
- Chỉ user đã đăng nhập mới có thể sử dụng chat
- Kiểm tra quyền truy cập ticket (user chỉ có thể xem ticket của mình)
- CSRF protection cho tất cả requests

### 7. Performance & Optimization
- Polling interval: 3 giây (có thể điều chỉnh)
- Chỉ lấy tin nhắn mới (WHERE id > last_id)
- Sử dụng JSON response để giảm kích thước data
- Tránh gửi tin nhắn trùng lặp với flag `isSending`
- Kiểm tra tin nhắn đã tồn tại trước khi thêm vào UI
- Chỉ bắt đầu polling khi có ticket và tin nhắn
- Validation tin nhắn rỗng ở cả client và server

### 8. Cải tiến có thể thực hiện
- Sử dụng WebSocket thay vì polling để realtime hơn
- Thêm typing indicator
- Thêm notification sound
- Lưu trạng thái online/offline của user
- Thêm file upload trong chat

## Cách sử dụng

### Cho User
1. Đăng nhập vào hệ thống
2. Click vào icon chat ở góc phải dưới
3. Gửi tin nhắn đầu tiên để bắt đầu cuộc trò chuyện
4. Chat sẽ tự động cập nhật tin nhắn mới

### Cho Admin
1. Truy cập Admin Panel
2. Vào mục Support để xem danh sách ticket
3. Click vào ticket để xem chi tiết và trả lời
4. Chat sẽ tự động cập nhật tin nhắn mới từ user
5. Admin có thể gửi tin nhắn và nhận realtime từ user
6. Hiển thị trạng thái Online/Offline của realtime chat 
