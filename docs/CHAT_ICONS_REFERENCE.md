# Chat Icons Reference

## Client Chat Icons (master.blade.php)

### Chat Input Actions
| Icon | Class | Function | Description |
|------|-------|----------|-------------|
| 🛏️ | `fas fa-bed` | Gửi phòng cho khách | Cho phép gửi thông tin phòng trống cho khách hàng |
| 📅 | `fas fa-calendar-check` | Xem booking khách | Xem thông tin phòng khách đang đặt |
| 📎 | `fas fa-paperclip` | Đính kèm file | Đính kèm file, ảnh vào tin nhắn |
| ✈️ | `fas fa-paper-plane` | Gửi tin nhắn | Gửi tin nhắn chat |

### Review & Rating Icons
| Icon | Class | Function | Description |
|------|-------|----------|-------------|
| ⭐ | `fas fa-star` | Đánh giá sao | Icon sao cho đánh giá phòng |
| 📊 | `fas fa-chart-bar` | Đánh giá chi tiết | Biểu đồ cho đánh giá chi tiết |
| 🧹 | `fas fa-broom` | Vệ sinh | Đánh giá độ sạch sẽ |
| 🛋️ | `fas fa-couch` | Thoải mái | Đánh giá độ thoải mái |
| 📍 | `fas fa-map-marker-alt` | Vị trí | Đánh giá vị trí khách sạn |
| 📶 | `fas fa-wifi` | Tiện nghi | Đánh giá tiện nghi wifi |
| 💰 | `fas fa-dollar-sign` | Giá trị | Đánh giá tỷ lệ giá/chất lượng |
| 💬 | `fas fa-comment` | Bình luận | Icon cho phần bình luận |

### Chat Widget Icons
| Icon | Class | Function | Description |
|------|-------|----------|-------------|
| 😊 | `fas fa-smile` | Emoji | Chọn emoji cho tin nhắn |
| 📁 | `fas fa-file` | File preview | Hiển thị file đã chọn |
| ❌ | `fas fa-times` | Đóng/Xóa | Đóng modal hoặc xóa file |
| ⚠️ | `fas fa-exclamation-triangle` | Cảnh báo | Thông báo lỗi |
| ℹ️ | `fas fa-info-circle` | Thông tin | Thông báo thông tin |
| ✅ | `fas fa-check-circle` | Thành công | Thông báo thành công |

## Admin Chat Icons (show.blade.php)

### Chat Input Actions
| Icon | Class | Function | Description |
|------|-------|----------|-------------|
| 🛏️ | `fas fa-bed` | Gửi phòng cho khách | Admin gửi thông tin phòng cho khách |
| 📅 | `fas fa-calendar-check` | Xem booking khách | Admin xem booking hiện tại của khách |
| 😊 | `fas fa-smile` | Emoji picker | Chọn emoji cho tin nhắn |
| 📎 | `fas fa-paperclip` | Đính kèm file | Đính kèm ảnh/tệp |
| ✈️ | `fas fa-paper-plane` | Gửi tin nhắn | Gửi tin nhắn |

### Admin Actions
| Icon | Class | Function | Description |
|------|-------|----------|-------------|
| 👤 | `fas fa-user` | Xem hồ sơ | Xem hồ sơ chi tiết khách hàng |
| ❌ | `fas fa-times` | Đóng chat | Quay lại danh sách hỗ trợ |

### Emoji Categories
| Icon | Class | Category | Description |
|------|-------|----------|-------------|
| 😊 | `fas fa-smile` | Smileys | Biểu tượng cảm xúc |
| ✋ | `fas fa-hand-paper` | Gestures | Cử chỉ tay |
| ⭐ | `fas fa-star` | Objects | Đồ vật |
| ❤️ | `fas fa-heart` | Symbols | Ký hiệu |

### File & Media Icons
| Icon | Class | Function | Description |
|------|-------|----------|-------------|
| 📁 | `fas fa-file` | File preview | Hiển thị file đã chọn |
| ❌ | `fas fa-times` | Xóa file | Xóa file đã chọn |
| 🔄 | `fas fa-spinner fa-spin` | Loading | Đang gửi tin nhắn |

### Status & Notification Icons
| Icon | Class | Function | Description |
|------|-------|----------|-------------|
| ⚠️ | `fas fa-exclamation-triangle` | Lỗi | Thông báo lỗi |
| ℹ️ | `fas fa-info-circle` | Thông tin | Thông báo thông tin |
| ✅ | `fas fa-check-circle` | Thành công | Thông báo thành công |

## Icon Usage Guidelines

### Color Coding
- **Primary Actions**: `#1E88E5` (Blue)
- **Success**: `#4CAF50` (Green) 
- **Warning**: `#FF9800` (Orange)
- **Error**: `#F44336` (Red)
- **Info**: `#2196F3` (Light Blue)
- **Secondary**: `#9E9E9E` (Gray)

### Size Standards
- **Chat Input Icons**: 28px x 28px
- **Send Button**: 36px x 36px
- **Modal Icons**: 18px
- **Rating Stars**: 24px

### Hover Effects
- Scale: `1.05` - `1.2`
- Background: `rgba(0, 0, 0, 0.1)`
- Transition: `0.2s ease`

## Implementation Notes

### Client Chat (master.blade.php)
- Icons được đặt trong `.chat-attachments` container
- Sử dụng class `attachment-btn` cho styling
- Button IDs: `sendRoomBtn`, `viewBookingBtn`

### Admin Chat (show.blade.php)  
- Icons được đặt trong `.chat-attachments` container
- Sử dụng class `attachment-btn` và `emoji-btn`
- Button IDs: `sendRoomBtn`, `viewBookingBtn`, `emojiBtn`

### JavaScript Integration
Cần thêm event listeners cho các button mới:
```javascript
document.getElementById('sendRoomBtn').addEventListener('click', function() {
    // Logic gửi phòng
});

document.getElementById('viewBookingBtn').addEventListener('click', function() {
    // Logic xem booking
});
```
