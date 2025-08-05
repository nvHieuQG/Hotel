# Test Chat Flow

## Test Case 1: Gửi tin nhắn đầu tiên
**URL**: `POST /support/ticket/0/message`
**Data**: 
```json
{
    "message": "Xin chào, tôi cần hỗ trợ",
    "_token": "csrf_token"
}
```

**Expected Result**:
- Tạo ticket mới với subject "Hỗ trợ nhanh"
- Tạo tin nhắn đầu tiên
- Trả về JSON:
```json
{
    "success": true,
    "ticket_id": 123,
    "message_id": 456,
    "message": "Tin nhắn đã được gửi"
}
```

## Test Case 2: Gửi tin nhắn tiếp theo
**URL**: `POST /support/ticket/123/message`
**Data**:
```json
{
    "message": "Cảm ơn bạn",
    "_token": "csrf_token"
}
```

**Expected Result**:
- Chỉ tạo tin nhắn mới (không tạo ticket)
- Trả về JSON:
```json
{
    "success": true,
    "message_id": 457,
    "message": "Tin nhắn đã được gửi"
}
```

## Test Case 3: Lỗi validation
**URL**: `POST /support/ticket/0/message`
**Data**:
```json
{
    "message": "",
    "_token": "csrf_token"
}
```

**Expected Result**:
- Không tạo gì cả
- Trả về JSON:
```json
{
    "success": false,
    "message": "Vui lòng nhập tin nhắn"
}
```

## Test Case 4: Lỗi database
**Scenario**: Database connection failed
**Expected Result**:
- Rollback transaction
- Trả về JSON:
```json
{
    "success": false,
    "message": "Không thể tạo yêu cầu hỗ trợ. Vui lòng thử lại!"
}
```

## Frontend Flow Test

### Step 1: Mở chat widget
- Click vào icon chat
- Hiển thị chat box
- Không có tin nhắn nào

### Step 2: Gửi tin nhắn đầu tiên
- Nhập tin nhắn: "Xin chào"
- Click "Gửi"
- **Expected**: Tin nhắn hiển thị ngay lập tức
- **Expected**: Bắt đầu realtime polling

### Step 3: Gửi tin nhắn tiếp theo
- Nhập tin nhắn: "Cảm ơn"
- Click "Gửi"
- **Expected**: Tin nhắn hiển thị ngay lập tức

### Step 4: Nhận tin nhắn từ admin
- Admin gửi tin nhắn
- **Expected**: Tin nhắn hiển thị tự động sau 3 giây

## Debug Steps

### 1. Kiểm tra Network Tab
- Xem request URL có đúng không
- Xem response có đúng format không
- Xem có lỗi HTTP status không

### 2. Kiểm tra Console
- Xem có JavaScript error không
- Xem log của chat functions

### 3. Kiểm tra Database
- Xem ticket có được tạo không
- Xem tin nhắn có được lưu không
- Xem transaction có rollback không

### 4. Kiểm tra Laravel Logs
```bash
tail -f storage/logs/laravel.log
```

## Common Issues

### Issue 1: Route not found
**Symptom**: 404 error
**Solution**: Kiểm tra route definition

### Issue 2: Method not found
**Symptom**: 500 error
**Solution**: Kiểm tra method exists trong controller

### Issue 3: Database error
**Symptom**: 500 error với database message
**Solution**: Kiểm tra migration và database connection

### Issue 4: CSRF token mismatch
**Symptom**: 419 error
**Solution**: Kiểm tra CSRF token trong form

### Issue 5: Validation error
**Symptom**: 422 error
**Solution**: Kiểm tra validation rules 
