<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo từ chối chuyển khoản</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc3545;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
        }
        .footer {
            background-color: #6c757d;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 0 0 5px 5px;
            font-size: 14px;
        }
        .alert {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .info-box {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            color: #0c5460;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚫 Chuyển khoản bị từ chối</h1>
        <p>Tour Booking #{{ $tourBooking->booking_id }}</p>
    </div>

    <div class="content">
        <p>Xin chào <strong>{{ $tourBooking->user->name }}</strong>,</p>

        <div class="alert">
            <strong>⚠️ Thông báo quan trọng:</strong><br>
            Giao dịch chuyển khoản của bạn đã bị từ chối bởi admin.
        </div>

        <h3>📋 Thông tin chi tiết:</h3>
        <ul>
            <li><strong>Mã Tour Booking:</strong> {{ $tourBooking->booking_id }}</li>
            <li><strong>Tên Tour:</strong> {{ $tourBooking->tour_name }}</li>
            <li><strong>Số tiền:</strong> {{ number_format($payment->amount, 0, ',', '.') }} VNĐ</li>
            <li><strong>Ngày tạo giao dịch:</strong> {{ $payment->created_at->format('d/m/Y H:i') }}</li>
            <li><strong>Lý do từ chối:</strong> {{ $rejectionReason }}</li>
        </ul>

        <div class="info-box">
            <h4>🔄 Cách khắc phục:</h4>
            <p>Để tiếp tục đặt tour, bạn có thể:</p>
            <ol>
                <li><strong>Chuyển khoản lại:</strong> Thực hiện chuyển khoản mới với thông tin chính xác</li>
                <li><strong>Liên hệ hỗ trợ:</strong> Gọi hotline hoặc gửi email để được hỗ trợ</li>
                <li><strong>Thanh toán tại khách sạn:</strong> Chọn phương thức thanh toán khác</li>
            </ol>
        </div>

        <h3>📞 Hỗ trợ:</h3>
        <p>Nếu bạn cần hỗ trợ hoặc có thắc mắc, vui lòng liên hệ:</p>
        <ul>
            <li><strong>Hotline:</strong> 1900-xxxx</li>
            <li><strong>Email:</strong> support@hotel.com</li>
            <li><strong>Thời gian:</strong> 8:00 - 22:00 (Thứ 2 - Chủ nhật)</li>
        </ul>

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ route('tour-booking.show', $tourBooking->id) }}" class="button">
                Xem chi tiết Tour Booking
            </a>
        </div>

        <p>Trân trọng,<br>
        <strong>Đội ngũ MARRON Hotel</strong></p>
    </div>

    <div class="footer">
        <p>Email này được gửi tự động từ hệ thống MARRON Hotel</p>
        <p>Vui lòng không trả lời email này</p>
    </div>
</body>
</html>
