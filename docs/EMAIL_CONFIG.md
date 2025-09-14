# Hướng dẫn cấu hình Email cho Tour Booking VAT Invoice

## 1. Cấu hình trong file .env

Để gửi email VAT invoice cho tour booking, bạn cần cấu hình email trong file `.env`:

### Cấu hình SMTP Gmail:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="Hotel Booking System"
```

### Cấu hình SMTP Mailtrap (cho testing):
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-mailtrap-username
MAIL_PASSWORD=your-mailtrap-password
MAIL_ENCRYPTION=tls
```

### Cấu hình SMTP khác:
```env
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@domain.com"
MAIL_FROM_NAME="Hotel Booking System"
```

## 2. Lưu ý quan trọng

- **Gmail**: Cần bật "2-Step Verification" và tạo "App Password"
- **Mailtrap**: Chỉ dùng cho testing, không gửi email thật
- **Port**: 587 (TLS) hoặc 465 (SSL) thường được sử dụng
- **Encryption**: TLS hoặc SSL tùy thuộc vào nhà cung cấp

## 3. Kiểm tra cấu hình

Sau khi cấu hình, bạn có thể test bằng cách:
1. Tạo tour booking với thông tin VAT
2. Admin tạo hóa đơn VAT
3. Admin gửi email hóa đơn VAT
4. Kiểm tra log trong `storage/logs/laravel.log`

## 4. Troubleshooting

Nếu email không gửi được:
1. Kiểm tra cấu hình SMTP
2. Kiểm tra log lỗi
3. Đảm bảo thư mục `storage/app/public/vat_invoices` có quyền ghi
4. Kiểm tra firewall/antivirus có chặn kết nối SMTP không
