<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo từ chối thanh toán bằng thẻ</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 0 0 8px 8px;
        }
        .alert {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box {
            background: white;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚫 Thông báo từ chối thanh toán bằng thẻ</h1>
        <p>Tour Booking #{{ $tourBooking->booking_id }}</p>
    </div>

    <div class="content">
        <p>Xin chào <strong>{{ $tourBooking->user->name ?? 'Quý khách' }}</strong>,</p>

        <div class="alert">
            <strong>⚠️ Thông báo quan trọng:</strong><br>
            Giao dịch thanh toán bằng thẻ tín dụng của bạn đã bị từ chối bởi hệ thống.
        </div>

        <div class="info-box">
            <h3>📋 Thông tin giao dịch:</h3>
            <ul>
                <li><strong>Mã booking:</strong> {{ $tourBooking->booking_id }}</li>
                <li><strong>Tour:</strong> {{ $tourBooking->tour_name ?? 'N/A' }}</li>
                <li><strong>Số tiền:</strong> {{ number_format($payment->amount, 0, ',', '.') }} VNĐ</li>
                <li><strong>Phương thức:</strong> Thẻ tín dụng</li>
                <li><strong>Ngày giao dịch:</strong> {{ $payment->created_at->format('d/m/Y H:i') }}</li>
                <li><strong>Lý do từ chối:</strong> {{ $rejectionReason }}</li>
            </ul>
        </div>

        <div class="info-box">
            <h3>🔍 Lý do có thể:</h3>
            <ul>
                <li>Thông tin thẻ không chính xác</li>
                <li>Thẻ bị từ chối bởi ngân hàng</li>
                <li>Hạn mức thẻ không đủ</li>
                <li>Thẻ đã hết hạn</li>
                <li>Vấn đề bảo mật</li>
            </ul>
        </div>

        <div class="info-box">
            <h3>✅ Bước tiếp theo:</h3>
            <ol>
                <li>Kiểm tra lại thông tin thẻ</li>
                <li>Liên hệ ngân hàng để xác nhận</li>
                <li>Thử thanh toán lại với thẻ khác</li>
                <li>Hoặc chọn phương thức thanh toán khác</li>
            </ol>
        </div>

        <p>Nếu bạn cần hỗ trợ, vui lòng liên hệ với chúng tôi:</p>
        <ul>
            <li>📧 Email: support@example.com</li>
            <li>📞 Điện thoại: 1900-xxxx</li>
            <li>💬 Chat online: Trên website</li>
        </ul>

        <p>Chúng tôi xin lỗi vì sự bất tiện này và mong muốn được phục vụ bạn tốt hơn.</p>

        <p>Trân trọng,<br>
        <strong>Đội ngũ hỗ trợ khách hàng</strong></p>
    </div>

    <div class="footer">
        <p>Email này được gửi tự động từ hệ thống. Vui lòng không trả lời email này.</p>
        <p>&copy; {{ date('Y') }} Tên công ty. Tất cả quyền được bảo lưu.</p>
    </div>
</body>
</html>
