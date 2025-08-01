# Hệ thống Thanh toán Thẻ tín dụng/Ghi nợ

## Tổng quan

Hệ thống thanh toán thẻ tín dụng/ghi nợ cho phép khách hàng thanh toán đặt phòng bằng thẻ Visa, Mastercard, American Express và các loại thẻ khác. Hệ thống hỗ trợ môi trường test với các thẻ mẫu để kiểm tra tính năng.

## Tính năng chính

### 1. Hỗ trợ các loại thẻ

-   **Visa**: Thẻ bắt đầu bằng số 4
-   **Mastercard**: Thẻ bắt đầu bằng số 51-55
-   **American Express**: Thẻ bắt đầu bằng số 34, 37
-   **Diners Club**: Thẻ bắt đầu bằng số 36, 38, hoặc 222100-272099
-   **Discover**: Thẻ bắt đầu bằng số 6011, 622126-622925, 64-65

### 2. Thẻ test mẫu

#### Thẻ thanh toán thành công:

-   **Visa**: 4111111111111111 (CVV: 123)
-   **Mastercard**: 5555555555554444 (CVV: 123)
-   **American Express**: 378282246310005 (CVV: 1234)

#### Thẻ thanh toán thất bại:

-   **Visa**: 4000000000000002 (CVV: 123)

### 3. Bảo mật thông tin

-   Thông tin thẻ được mã hóa khi truyền tải
-   Không lưu trữ thông tin thẻ thực tế trong database
-   Chỉ lưu 4 chữ số cuối và thông tin cơ bản
-   Tuân thủ các tiêu chuẩn bảo mật PCI DSS

## Cấu trúc hệ thống

### 1. Models

#### Payment Model

```php
class Payment extends Model
{
    protected $fillable = [
        'booking_id',
        'payment_method', // 'credit_card'
        'amount',
        'currency',
        'status',
        'transaction_id',
        'gateway_response', // Lưu thông tin thẻ (last4, brand, etc.)
        'gateway_code',
        'gateway_message',
        'paid_at',
        'gateway_name'
    ];
}
```

### 2. Services

#### PaymentService

-   `createCreditCardPayment()`: Tạo payment record cho credit card
-   `processCreditCardPayment()`: Xử lý thanh toán thẻ tín dụng
-   `getCreditCardTestInfo()`: Lấy thông tin thẻ test
-   `getCardBrand()`: Xác định loại thẻ từ số thẻ
-   `isTestCard()`: Kiểm tra có phải thẻ test không

### 3. Controllers

#### PaymentController

-   `processCreditCard()`: Hiển thị trang thanh toán thẻ
-   `confirmCreditCard()`: Xử lý xác nhận thanh toán thẻ

## Luồng hoạt động

### 1. Luồng thanh toán thẻ tín dụng

```
1. Khách hàng chọn phương thức "Thẻ tín dụng/ghi nợ"
2. Hệ thống tạo payment record với trạng thái pending
3. Khách hàng nhập thông tin thẻ (hoặc sử dụng thẻ test)
4. Hệ thống validate thông tin thẻ
5. Simulate xử lý thanh toán (delay 2 giây)
6. Kiểm tra có phải thẻ test không
7. Nếu là thẻ test hợp lệ → thanh toán thành công
8. Nếu không phải thẻ test → thanh toán thất bại
9. Cập nhật trạng thái payment và gửi email xác nhận
10. Chuyển hướng đến trang thành công/thất bại
```

### 2. Validation thông tin thẻ

-   **Số thẻ**: 16 chữ số
-   **Tháng hết hạn**: 1-12
-   **Năm hết hạn**: Từ năm hiện tại trở đi
-   **CVV**: 3-4 chữ số
-   **Tên chủ thẻ**: Bắt buộc

## Giao diện người dùng

### 1. Trang chọn phương thức thanh toán

-   Tab "Thẻ tín dụng/ghi nợ" được đặt đầu tiên
-   Hiển thị icon và mô tả phương thức thanh toán
-   Nút "Thanh toán bằng thẻ" để chuyển đến form

### 2. Trang thanh toán thẻ

#### Form thanh toán:

-   Tên chủ thẻ
-   Số thẻ (16 chữ số)
-   Tháng/Năm hết hạn
-   CVV (3-4 chữ số)
-   Ghi chú (tùy chọn)

#### Sidebar thông tin:

-   Danh sách thẻ test để click và điền tự động
-   Hướng dẫn sử dụng
-   Thông tin bảo mật

### 3. Trang thành công

-   Hiển thị thông tin giao dịch
-   Thông tin thẻ (4 số cuối, loại thẻ)
-   Thời gian thanh toán
-   Hướng dẫn check-in

## Bảo mật

### 1. Mã hóa dữ liệu

-   Thông tin thẻ được mã hóa khi truyền tải
-   Sử dụng HTTPS cho tất cả giao dịch
-   Không lưu trữ thông tin thẻ nhạy cảm

### 2. Validation

-   Kiểm tra định dạng số thẻ
-   Validate tháng/năm hết hạn
-   Kiểm tra CVV
-   Xác thực tên chủ thẻ

### 3. Logging

-   Ghi log tất cả giao dịch thanh toán
-   Lưu thông tin lỗi để debug
-   Không log thông tin thẻ nhạy cảm

## Tích hợp Production

### 1. Cổng thanh toán thực tế

Trong môi trường production, hệ thống có thể tích hợp với:

-   **VNPay**: Cổng thanh toán Việt Nam
-   **MoMo**: Ví điện tử
-   **Stripe**: Cổng thanh toán quốc tế
-   **PayPal**: Cổng thanh toán toàn cầu

### 2. Cấu hình

```php
// config/payment.php
return [
    'credit_card' => [
        'enabled' => true,
        'gateway' => env('CREDIT_CARD_GATEWAY', 'stripe'),
        'test_mode' => env('PAYMENT_TEST_MODE', true),
        'public_key' => env('PAYMENT_PUBLIC_KEY'),
        'secret_key' => env('PAYMENT_SECRET_KEY'),
    ]
];
```

### 3. Environment Variables

```env
CREDIT_CARD_GATEWAY=stripe
PAYMENT_TEST_MODE=true
PAYMENT_PUBLIC_KEY=pk_test_...
PAYMENT_SECRET_KEY=sk_test_...
```

## Testing

### 1. Thẻ test thành công

```javascript
// Visa
fillTestCard("4111111111111111", "123", "12/25");

// Mastercard
fillTestCard("5555555555554444", "123", "12/25");

// American Express
fillTestCard("378282246310005", "1234", "12/25");
```

### 2. Thẻ test thất bại

```javascript
// Visa thất bại
fillTestCard("4000000000000002", "123", "12/25");
```

### 3. Thẻ không hợp lệ

-   Số thẻ không đúng định dạng
-   Thẻ hết hạn
-   CVV sai
-   Tên chủ thẻ trống

## Monitoring

### 1. Logs

-   Ghi log tất cả giao dịch thanh toán
-   Lưu thông tin lỗi chi tiết
-   Tracking performance

### 2. Metrics

-   Tỷ lệ thanh toán thành công
-   Thời gian xử lý trung bình
-   Số lượng giao dịch theo loại thẻ
-   Error rate

### 3. Alerts

-   Giao dịch thất bại nhiều
-   Lỗi hệ thống thanh toán
-   Timeout xử lý

## Troubleshooting

### 1. Lỗi thường gặp

-   **Thẻ không hợp lệ**: Kiểm tra định dạng số thẻ
-   **Thanh toán thất bại**: Sử dụng thẻ test hợp lệ
-   **Timeout**: Kiểm tra kết nối mạng
-   **Validation error**: Kiểm tra thông tin form

### 2. Debug

-   Kiểm tra logs trong `storage/logs/laravel.log`
-   Xem thông tin payment trong database
-   Test với thẻ mẫu
-   Kiểm tra network tab trong browser

## Roadmap

### 1. Tính năng sắp tới

-   Tích hợp 3D Secure
-   Hỗ trợ thẻ nội địa (ATM, QR Code)
-   Lưu thẻ để thanh toán nhanh
-   Refund tự động
-   Webhook cho real-time updates

### 2. Cải tiến

-   UI/UX tốt hơn
-   Mobile responsive
-   Offline payment
-   Multi-currency support
-   Advanced fraud detection
