# Chat Error Handling System

## Tổng quan
Hệ thống xử lý lỗi cho chat được thiết kế để cung cấp trải nghiệm mượt mà cho khách hàng và admin, tránh các lỗi gây khó chịu khi gửi tin nhắn lần đầu.

## Các loại lỗi được xử lý

### 1. Validation Errors
- **Tin nhắn rỗng**: Kiểm tra tin nhắn không được để trống
- **Tin nhắn quá ngắn**: Phải có ít nhất 1 ký tự
- **Tin nhắn quá dài**: Không được quá 1000 ký tự

### 2. Database Errors
- **Lỗi tạo ticket**: Không thể tạo yêu cầu hỗ trợ mới
- **Lỗi lưu tin nhắn**: Không thể lưu tin nhắn vào database
- **Lỗi kết nối database**: Database không khả dụng

### 3. Network Errors
- **Request timeout**: Kết nối mạng chậm hoặc bị gián đoạn
- **Server error**: Lỗi 500 từ server
- **Connection refused**: Không thể kết nối đến server

### 4. Authentication Errors
- **CSRF token invalid**: Token bảo mật không hợp lệ
- **User not authenticated**: Người dùng chưa đăng nhập
- **Permission denied**: Không có quyền truy cập

## Error Handling Flow

### Frontend (JavaScript)
```javascript
// 1. Validation trước khi gửi
if (!message || message.trim() === '') return;

// 2. Kiểm tra HTTP status
.then(res => {
    if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
    }
    return res.json();
})

// 3. Xử lý response từ server
.then(data => {
    if(data.success) {
        // Thành công
    } else {
        // Hiển thị lỗi từ server
        showChatError(data.message);
    }
})

// 4. Xử lý lỗi network
.catch((error) => {
    console.error('Chat error:', error);
    showChatError('Kết nối mạng có vấn đề. Vui lòng thử lại sau!');
})
```

### Backend (Laravel)
```php
try {
    // 1. Validation
    $request->validate([
        'message' => 'required|string|min:1|max:1000',
    ], [
        'message.required' => 'Vui lòng nhập tin nhắn',
        'message.min' => 'Tin nhắn phải có ít nhất 1 ký tự',
        'message.max' => 'Tin nhắn không được quá 1000 ký tự'
    ]);

    // 2. Tạo ticket (nếu cần)
    try {
        $ticket = $this->supportService->createTicket($userId, 'Hỗ trợ nhanh');
    } catch (\Exception $e) {
        Log::error('Error creating support ticket: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Không thể tạo yêu cầu hỗ trợ. Vui lòng thử lại!'
        ], 500);
    }

    // 3. Gửi tin nhắn
    try {
        $message = $this->supportService->sendMessage($id, $userId, 'user', $messageText);
    } catch (\Exception $e) {
        Log::error('Error sending support message: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'Không thể gửi tin nhắn. Vui lòng thử lại!'
        ], 500);
    }

} catch (\Illuminate\Validation\ValidationException $e) {
    return response()->json([
        'success' => false,
        'message' => $e->errors()['message'][0] ?? 'Dữ liệu không hợp lệ'
    ], 422);
} catch (\Exception $e) {
    Log::error('Unexpected error in sendMessage: ' . $e->getMessage());
    return response()->json([
        'success' => false,
        'message' => 'Có lỗi xảy ra. Vui lòng thử lại sau!'
    ], 500);
}
```

## Error Messages

### User-Friendly Messages
- **"Vui lòng nhập tin nhắn"** - Khi tin nhắn rỗng
- **"Tin nhắn phải có ít nhất 1 ký tự"** - Khi tin nhắn quá ngắn
- **"Tin nhắn không được quá 1000 ký tự"** - Khi tin nhắn quá dài
- **"Không thể tạo yêu cầu hỗ trợ. Vui lòng thử lại!"** - Lỗi tạo ticket
- **"Không thể gửi tin nhắn. Vui lòng thử lại!"** - Lỗi gửi tin nhắn
- **"Kết nối mạng có vấn đề. Vui lòng thử lại sau!"** - Lỗi network
- **"Có lỗi xảy ra. Vui lòng thử lại sau!"** - Lỗi không xác định

### Error Display
- **Client**: Thông báo lỗi đẹp với màu đỏ, tự động ẩn sau 5 giây
- **Admin**: Alert Bootstrap với nút đóng, tự động ẩn sau 5 giây

## Logging

### Error Logs
```php
// Log lỗi tạo ticket
Log::error('Error creating support ticket: ' . $e->getMessage());

// Log lỗi gửi tin nhắn
Log::error('Error sending support message: ' . $e->getMessage());

// Log lỗi không xác định
Log::error('Unexpected error in sendMessage: ' . $e->getMessage());
```

### Console Logs (Frontend)
```javascript
// Log lỗi network
console.error('Chat error:', error);

// Log lỗi admin
console.error('Admin chat error:', error);
```

## Prevention Strategies

### 1. Pre-validation
- Kiểm tra tin nhắn rỗng trước khi gửi
- Validate độ dài tin nhắn ở client
- Kiểm tra kết nối mạng

### 2. Retry Mechanism
- Tự động thử lại khi gặp lỗi network
- Giới hạn số lần thử lại (3 lần)
- Tăng thời gian chờ giữa các lần thử

### 3. Fallback Options
- Lưu tin nhắn vào localStorage khi offline
- Gửi lại khi có kết nối
- Hiển thị thông báo "Đang gửi..." khi retry

### 4. User Feedback
- Loading state khi đang gửi
- Disable button khi đang xử lý
- Thông báo thành công khi gửi xong

## Testing Scenarios

### 1. Network Issues
- Ngắt kết nối internet
- Server không phản hồi
- Timeout requests

### 2. Database Issues
- Database connection failed
- Table not found
- Constraint violations

### 3. Validation Issues
- Tin nhắn rỗng
- Tin nhắn quá dài
- Ký tự đặc biệt

### 4. Authentication Issues
- CSRF token expired
- User session expired
- Permission denied

## Monitoring

### 1. Error Tracking
- Log tất cả lỗi với context
- Track error frequency
- Monitor error patterns

### 2. Performance Monitoring
- Response time tracking
- Success rate monitoring
- User experience metrics

### 3. Alert System
- Email alerts cho critical errors
- Dashboard monitoring
- Real-time error reporting

## Best Practices

### 1. User Experience
- Không hiển thị technical errors
- Cung cấp hướng dẫn khắc phục
- Giữ trạng thái form khi có lỗi

### 2. Security
- Không expose sensitive information
- Validate input ở cả client và server
- Log security-related errors

### 3. Performance
- Không retry quá nhiều lần
- Timeout requests hợp lý
- Cache responses khi có thể

### 4. Maintenance
- Regular error log review
- Update error messages
- Monitor error trends 
