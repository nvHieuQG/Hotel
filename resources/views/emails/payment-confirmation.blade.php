<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận thanh toán thành công</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #28a745;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #28a745;
            margin-bottom: 10px;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin: 20px 0;
        }
        .booking-id {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        .info-section {
            margin: 20px 0;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 10px 0;
            padding: 5px 0;
            border-bottom: 1px solid #eee;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .payment-section {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            padding: 20px;
            margin: 20px 0;
        }
        .payment-amount {
            font-size: 28px;
            font-weight: bold;
            color: #155724;
            text-align: center;
            margin: 10px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #666;
        }
        .contact-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background-color: #218838;
        }
        .status-badge {
            background-color: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏨 Marron Hotel</div>
            <div class="success-icon">✅</div>
            <h1>Thanh toán thành công!</h1>
            <div class="booking-id">{{ $booking->booking_id }}</div>
        </div>

        <p>Xin chào <strong>{{ $user->name }}</strong>,</p>
        
        <p>Chúng tôi xác nhận rằng thanh toán cho đặt phòng của bạn đã được xử lý thành công. Dưới đây là thông tin chi tiết:</p>

        <div class="info-section">
            <h3>📋 Thông tin đặt phòng</h3>
            <div class="info-row">
                <span class="info-label">Mã đặt phòng:</span>
                <span class="info-value">{{ $booking->booking_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Phòng:</span>
                <span class="info-value">{{ $booking->room->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ngày check-in:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y H:i') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ngày check-out:</span>
                <span class="info-value">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <div class="payment-section">
            <h3>💳 Thông tin thanh toán</h3>
            <div class="payment-amount">{{ number_format($payment->amount) }} VNĐ</div>
            
            <div class="info-row">
                <span class="info-label">Phương thức thanh toán:</span>
                <span class="info-value">
                    @if($payment->payment_method == 'bank_transfer')
                        🏦 Chuyển khoản ngân hàng
                    @else
                        💰 {{ ucfirst($payment->payment_method) }}
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Trạng thái:</span>
                <span class="info-value">
                    <span class="status-badge">{{ $payment->status_text }}</span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Thời gian thanh toán:</span>
                <span class="info-value">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i:s') : $payment->created_at->format('d/m/Y H:i:s') }}</span>
            </div>
            @if($payment->transaction_id)
            <div class="info-row">
                <span class="info-label">Mã giao dịch:</span>
                <span class="info-value"><code>{{ $payment->transaction_id }}</code></span>
            </div>
            @endif
            @if($payment->gateway_message)
            <div class="info-row">
                <span class="info-label">Ghi chú:</span>
                <span class="info-value">{{ $payment->gateway_message }}</span>
            </div>
            @endif
        </div>

        <div class="info-section">
            <h3>📝 Hướng dẫn check-in</h3>
            <ul>
                <li>Vui lòng đến khách sạn đúng thời gian check-in: <strong>{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y H:i') }}</strong></li>
                <li>Mang theo giấy tờ tùy thân khi check-in</li>
                <li>Thời gian check-out: <strong>{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y H:i') }}</strong></li>
                <li>Nếu có thay đổi, vui lòng liên hệ khách sạn trước 24h</li>
            </ul>
        </div>

        <div class="contact-info">
            <h3>📞 Liên hệ hỗ trợ</h3>
            <p><strong>Địa chỉ:</strong> 123 Đường MARRON, Quận XYZ, TP.HCM</p>
            <p><strong>Điện thoại:</strong> 028-1234-5678</p>
            <p><strong>Email:</strong> info@marronhotel.com</p>
            <p><strong>Website:</strong> www.marronhotel.com</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('booking.detail', $booking->id) }}" class="btn">Xem chi tiết đặt phòng</a>
            <a href="{{ route('user.bookings') }}" class="btn">Danh sách đặt phòng</a>
        </div>

        <div class="footer">
            <p>Cảm ơn bạn đã chọn Marron Hotel!</p>
            <p>Chúc bạn có một kỳ nghỉ tuyệt vời!</p>
            <p><small>Email này được gửi tự động, vui lòng không trả lời email này.</small></p>
        </div>
    </div>
</body>
</html> 