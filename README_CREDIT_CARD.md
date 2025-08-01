# Hướng dẫn sử dụng tính năng Thanh toán Thẻ tín dụng

## Tổng quan

Tính năng thanh toán thẻ tín dụng đã được tích hợp vào hệ thống đặt phòng khách sạn. Hệ thống hỗ trợ thanh toán bằng thẻ Visa, Mastercard, American Express và các loại thẻ khác với môi trường test để kiểm tra tính năng.

## Cách sử dụng

### 1. Truy cập tính năng

1. Đăng nhập vào hệ thống
2. Chọn phòng và đặt phòng
3. Trên trang chọn phương thức thanh toán, chọn tab "Thẻ tín dụng/ghi nợ"
4. Nhấn nút "Thanh toán bằng thẻ"

### 2. Thẻ test để kiểm tra

#### Thẻ thanh toán thành công:

-   **Visa**: `4111111111111111` (CVV: `123`)
-   **Mastercard**: `5555555555554444` (CVV: `123`)
-   **American Express**: `378282246310005` (CVV: `1234`)

#### Thẻ thanh toán thất bại:

-   **Visa**: `4000000000000002` (CVV: `123`)

### 3. Cách sử dụng thẻ test

1. Trên trang thanh toán thẻ, click vào một thẻ test trong sidebar
2. Thông tin thẻ sẽ được điền tự động
3. Nhấn nút "Thanh toán" để test

### 4. Thông tin cần nhập

-   **Tên chủ thẻ**: Nhập tên như trên thẻ
-   **Số thẻ**: 16 chữ số (không có khoảng trắng)
-   **Tháng hết hạn**: Chọn tháng từ dropdown
-   **Năm hết hạn**: Chọn năm từ dropdown
-   **CVV**: 3-4 chữ số ở mặt sau thẻ
-   **Ghi chú**: Tùy chọn

## Tính năng bảo mật

### 1. Mã hóa dữ liệu

-   Thông tin thẻ được mã hóa khi truyền tải
-   Sử dụng HTTPS cho tất cả giao dịch
-   Không lưu trữ thông tin thẻ nhạy cảm trong database

### 2. Validation

-   Kiểm tra định dạng số thẻ (16 chữ số)
-   Validate tháng/năm hết hạn
-   Kiểm tra CVV (3-4 chữ số)
-   Xác thực tên chủ thẻ

### 3. Logging

-   Ghi log tất cả giao dịch thanh toán
-   Lưu thông tin lỗi để debug
-   Không log thông tin thẻ nhạy cảm

## Cấu trúc hệ thống

### 1. Files đã tạo/cập nhật

#### Controllers

-   `app/Http/Controllers/PaymentController.php` - Thêm methods cho credit card

#### Services

-   `app/Services/PaymentService.php` - Thêm logic xử lý credit card
-   `app/Interfaces/Services/PaymentServiceInterface.php` - Thêm interface methods

#### Views

-   `resources/views/client/payment/credit-card.blade.php` - Trang thanh toán thẻ
-   `resources/views/client/booking/payment-method.blade.php` - Cập nhật để thêm tab credit card
-   `resources/views/client/payment/payment-method.blade.php` - Cập nhật để thêm option credit card
-   `resources/views/client/payment/success.blade.php` - Cập nhật để hiển thị thông tin thẻ

#### Routes

-   `routes/web.php` - Thêm routes cho credit card payment

#### Documentation

-   `docs/CREDIT_CARD_PAYMENT_SYSTEM.md` - Documentation chi tiết
-   `tests/Feature/CreditCardPaymentTest.php` - Test cases

### 2. Database

Không cần thay đổi database vì bảng `payments` đã có sẵn các trường cần thiết:

-   `payment_method`: 'credit_card'
-   `gateway_response`: JSON chứa thông tin thẻ (last4, brand, etc.)
-   `status`: 'pending', 'completed', 'failed'

## Testing

### 1. Chạy test cases

```bash
php artisan test tests/Feature/CreditCardPaymentTest.php
```

### 2. Test manual

1. Tạo booking mới
2. Chọn phương thức thanh toán "Thẻ tín dụng/ghi nợ"
3. Sử dụng thẻ test để kiểm tra:
    - Thẻ thành công: `4111111111111111`
    - Thẻ thất bại: `4000000000000002`

### 3. Kiểm tra logs

```bash
tail -f storage/logs/laravel.log
```

## Troubleshooting

### 1. Lỗi thường gặp

#### "Thẻ không hợp lệ"

-   Kiểm tra số thẻ có đúng 16 chữ số không
-   Sử dụng thẻ test hợp lệ

#### "Thanh toán thất bại"

-   Sử dụng thẻ test thành công: `4111111111111111`
-   Kiểm tra thông tin CVV và ngày hết hạn

#### "Validation error"

-   Kiểm tra tất cả trường bắt buộc đã được điền
-   Đảm bảo định dạng số thẻ và CVV đúng

### 2. Debug

#### Kiểm tra database

```sql
SELECT * FROM payments WHERE payment_method = 'credit_card' ORDER BY created_at DESC;
```

#### Kiểm tra logs

```bash
grep "Credit card payment" storage/logs/laravel.log
```

#### Kiểm tra routes

```bash
php artisan route:list | grep credit-card
```

## Production Deployment

### 1. Cấu hình môi trường

```env
CREDIT_CARD_GATEWAY=stripe
PAYMENT_TEST_MODE=false
PAYMENT_PUBLIC_KEY=pk_live_...
PAYMENT_SECRET_KEY=sk_live_...
```

### 2. Tích hợp cổng thanh toán thực tế

Trong môi trường production, cần tích hợp với:

-   **VNPay**: Cổng thanh toán Việt Nam
-   **MoMo**: Ví điện tử
-   **Stripe**: Cổng thanh toán quốc tế
-   **PayPal**: Cổng thanh toán toàn cầu

### 3. Bảo mật

-   Sử dụng HTTPS
-   Mã hóa thông tin thẻ
-   Tuân thủ PCI DSS
-   Monitoring và alerting

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

## Liên hệ hỗ trợ

Nếu gặp vấn đề với tính năng thanh toán thẻ tín dụng, vui lòng:

1. Kiểm tra logs trong `storage/logs/laravel.log`
2. Chạy test cases để xác định lỗi
3. Liên hệ team phát triển với thông tin lỗi chi tiết

---

**Lưu ý**: Đây là môi trường test. Trong môi trường production, hệ thống sẽ tích hợp với cổng thanh toán thực tế.
