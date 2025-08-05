# Admin Support List Interface

## Tổng Quan
Đã tạo giao diện danh sách tickets hỗ trợ cho admin tại `/admin/support` với thiết kế hiện đại và tính năng đầy đủ.

## Tính Năng Chính

### 1. Header với Thống Kê
- **Số tickets đang mở**: Hiển thị số lượng tickets chưa được xử lý
- **Số tickets đã đóng**: Hiển thị số lượng tickets đã hoàn thành
- **Tổng số tickets**: Tổng cộng tất cả tickets

### 2. Bộ Lọc và Tìm Kiếm
- **Filter tabs**: Tất cả | Đang mở | Đã đóng | Chưa đọc
- **Search box**: Tìm kiếm theo tên khách hàng, chủ đề, nội dung tin nhắn
- **Real-time filtering**: Lọc ngay lập tức khi thay đổi filter hoặc search

### 3. Danh Sách Tickets
- **Avatar khách hàng**: Hiển thị chữ cái đầu của tên
- **Thông tin cơ bản**: Tên khách, thời gian tạo, chủ đề
- **Preview tin nhắn**: Hiển thị tin nhắn cuối cùng
- **Trạng thái**: Badge màu cho trạng thái đang mở/đã đóng
- **Số tin nhắn**: Hiển thị tổng số tin nhắn trong ticket
- **Unread badge**: Số tin nhắn chưa đọc từ khách hàng

### 4. Tính Năng Tương Tác
- **Click để xem chi tiết**: Chuyển đến trang chat chi tiết
- **Highlight unread**: Tickets chưa đọc được highlight màu đỏ
- **Hover effects**: Hiệu ứng hover cho từng ticket

## Cấu Trúc Dữ Liệu

### Database Schema
```sql
-- support_messages table
ALTER TABLE support_messages ADD COLUMN is_read BOOLEAN DEFAULT FALSE;
CREATE INDEX idx_support_messages_is_read ON support_messages(is_read);
```

### Models
```php
// SupportMessage model
protected $fillable = [
    'ticket_id', 'sender_id', 'sender_type', 'message', 'is_read'
];

// SupportTicket model
public function user() {
    return $this->belongsTo(User::class, 'user_id');
}

public function messages() {
    return $this->hasMany(SupportMessage::class, 'ticket_id');
}
```

### Repository
```php
public function getAll() {
    return SupportTicket::with(['user', 'messages' => function($q) {
        $q->orderBy('created_at');
    }])->orderByDesc('created_at')->get();
}
```

## Giao Diện

### Layout
- **Container**: Card với shadow và border radius
- **Header**: Gradient background với thống kê
- **Filters**: Tabs và search box
- **List**: Scrollable list với max-height

### Responsive Design
- **Desktop**: Layout đầy đủ
- **Tablet**: Điều chỉnh spacing
- **Mobile**: Stack layout, responsive text

### Color Scheme
- **Primary**: #1E88E5 (Blue)
- **Success**: #4CAF50 (Green)
- **Warning**: #F57C00 (Orange)
- **Danger**: #F44336 (Red)
- **Gray**: #9E9E9E

## JavaScript Functionality

### Filter Logic
```javascript
function filterTickets(statusFilter, searchTerm = '') {
    // Filter by status (all, open, closed, unread)
    // Filter by search term (customer name, subject, message)
    // Show/hide tickets based on filters
    // Show empty state if no results
}
```

### Search Logic
```javascript
// Real-time search as user types
// Search in: customer name, subject, message preview
// Case-insensitive search
```

### Unread Detection
```javascript
// Count unread messages from users
// Highlight tickets with unread messages
// Update unread badge count
```

## Test Data

### Seeder
Đã tạo `SupportTicketSeeder` với 5 tickets mẫu:
1. **Hỏi về đặt phòng** - Open, có tin nhắn chưa đọc
2. **Vấn đề về thanh toán** - Open, có 2 tin nhắn chưa đọc
3. **Hủy đặt phòng** - Closed, đã hoàn thành
4. **Hỏi về dịch vụ spa** - Open, có 1 tin nhắn chưa đọc
5. **Đổi ngày check-in** - Open, có 1 tin nhắn chưa đọc

### Chạy Seeder
```bash
php artisan db:seed --class=SupportTicketSeeder
```

## Routes

### Admin Support Routes
```php
Route::get('/admin/support', [AdminSupportController::class, 'index'])->name('admin.support.index');
Route::get('/admin/support/ticket/{id}', [AdminSupportController::class, 'showTicket'])->name('admin.support.showTicket');
Route::post('/admin/support/ticket/{id}/message', [AdminSupportController::class, 'sendMessage'])->name('admin.support.sendMessage');
Route::get('/admin/support/ticket/{id}/messages', [AdminSupportController::class, 'getNewMessages'])->name('admin.support.getNewMessages');
```

## Workflow

### 1. Admin truy cập `/admin/support`
- Hiển thị danh sách tất cả tickets
- Thống kê số lượng tickets theo trạng thái
- Filter mặc định: "Tất cả"

### 2. Admin lọc tickets
- Click vào filter tabs để lọc theo trạng thái
- Nhập từ khóa để tìm kiếm
- Kết quả được cập nhật real-time

### 3. Admin xem chi tiết
- Click vào ticket để chuyển đến trang chat
- URL: `/admin/support/ticket/{id}`
- Hiển thị giao diện chat 3 cột

### 4. Admin trả lời
- Gửi tin nhắn trong chat interface
- Tin nhắn được lưu với `is_read = false`
- Khách hàng sẽ thấy tin nhắn mới

## Tính Năng Nâng Cao (Future)

### 1. Real-time Updates
- WebSocket để cập nhật danh sách real-time
- Notification khi có ticket mới
- Auto-refresh unread count

### 2. Bulk Actions
- Chọn nhiều tickets
- Mark as read/unread
- Change status (open/closed)
- Delete tickets

### 3. Advanced Filtering
- Filter by date range
- Filter by customer
- Filter by message count
- Sort by different criteria

### 4. Analytics
- Response time statistics
- Ticket resolution time
- Customer satisfaction metrics
- Support agent performance

## Troubleshooting

### Common Issues
1. **Tickets không hiển thị**: Kiểm tra database có dữ liệu không
2. **Unread count sai**: Kiểm tra field `is_read` trong database
3. **Filter không hoạt động**: Kiểm tra JavaScript console
4. **Search không tìm thấy**: Kiểm tra encoding và case sensitivity

### Debug Steps
1. Kiểm tra browser console cho JavaScript errors
2. Kiểm tra Laravel logs: `storage/logs/laravel.log`
3. Kiểm tra database có dữ liệu không
4. Verify routes hoạt động: `php artisan route:list --name=admin.support`

## Kết Luận

Giao diện admin support list đã được tạo hoàn chỉnh với:
- ✅ **Thiết kế hiện đại** và responsive
- ✅ **Tính năng đầy đủ**: filter, search, unread detection
- ✅ **Database schema** được cập nhật với field `is_read`
- ✅ **Test data** sẵn sàng để demo
- ✅ **JavaScript functionality** mượt mà
- ✅ **Integration** với chat interface hiện có

Admin có thể dễ dàng quản lý và trả lời các yêu cầu hỗ trợ từ khách hàng thông qua giao diện này. 
