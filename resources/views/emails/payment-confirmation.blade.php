<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn mua bán - Xác nhận thanh toán</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; max-width: 680px; margin: 0 auto; padding: 24px; background-color: #f7f8fa; }
        .card { background: #fff; border-radius: 12px; padding: 28px; box-shadow: 0 6px 20px rgba(0,0,0,0.06); }
        .header { text-align: center; border-bottom: 3px solid #0d6efd; padding-bottom: 18px; margin-bottom: 26px; }
        .brand { font-size: 22px; font-weight: 700; color: #0d6efd; }
        .invoice-tag { display: inline-block; background-color: #0d6efd; color: #fff; padding: 8px 14px; border-radius: 6px; font-weight: 600; margin-top: 8px; }
        .section { margin: 18px 0; padding: 16px; background: #f8f9fb; border-radius: 8px; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eef0f3; }
        .row:last-child { border-bottom: none; }
        .label { font-weight: 600; color: #555; }
        .value { color: #222; }
        .total { background: #0d6efd; color: #fff; text-align: center; padding: 18px; border-radius: 8px; margin: 18px 0; }
        .amount { font-size: 24px; font-weight: 800; }
        .footer { text-align: center; margin-top: 28px; padding-top: 18px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 13px; }
        .muted { color: #6c757d; }
    </style>
    </head>
<body>
    <div class="card">
        <div class="header">
            <div class="brand">🏨 MARRON HOTEL</div>
            <h2>Hóa đơn mua bán - Xác nhận thanh toán</h2>
            <div class="invoice-tag">Mã đặt phòng: {{ $booking->booking_id }}</div>
        </div>

        <p>Xin chào <strong>{{ $user->name }}</strong>,</p>
        <p>Chúng tôi xác nhận đã nhận thanh toán cho đơn đặt phòng của bạn. Dưới đây là thông tin hóa đơn mua bán.</p>

        <div class="section">
            <h3>Thông tin khách hàng</h3>
            <div class="row"><div class="label">Tên khách hàng</div><div class="value">{{ $user->name }}</div></div>
            <div class="row"><div class="label">Email</div><div class="value">{{ $user->email }}</div></div>
            @if(!empty($user->phone))
            <div class="row"><div class="label">Số điện thoại</div><div class="value">{{ $user->phone }}</div></div>
            @endif
        </div>

        <div class="section">
            <h3>Chi tiết đặt phòng</h3>
            <div class="row"><div class="label">Phòng</div><div class="value">{{ $booking->room->name ?? 'N/A' }}</div></div>
            <div class="row"><div class="label">Loại phòng</div><div class="value">{{ $booking->room->roomType->name ?? 'N/A' }}</div></div>
            <div class="row"><div class="label">Check-in</div><div class="value">{{ \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y H:i') }}</div></div>
            <div class="row"><div class="label">Check-out</div><div class="value">{{ \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y H:i') }}</div></div>
            <div class="row"><div class="label">Số đêm</div><div class="value">{{ \Carbon\Carbon::parse($booking->check_in_date)->diffInDays(\Carbon\Carbon::parse($booking->check_out_date)) }}</div></div>
        </div>

        <div class="section">
            <h3>Thông tin thanh toán</h3>
            <div class="row"><div class="label">Phương thức</div><div class="value">{{ $payment->method === 'bank_transfer' ? 'Chuyển khoản ngân hàng' : ucfirst($payment->method) }}</div></div>
            <div class="row"><div class="label">Trạng thái</div><div class="value">{{ $payment->status_text ?? ucfirst($payment->status) }}</div></div>
            <div class="row"><div class="label">Thời gian</div><div class="value">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i:s') : $payment->created_at->format('d/m/Y H:i:s') }}</div></div>
            @if(!empty($payment->transaction_id))
            <div class="row"><div class="label">Mã giao dịch</div><div class="value">{{ $payment->transaction_id }}</div></div>
            @endif
        </div>

        <div class="total">
            <div class="amount">{{ number_format($booking->total_booking_price) }} VNĐ</div>
            <div>Giá trị đã thanh toán</div>
        </div>

        <p class="muted">Lưu ý: Đây là hóa đơn mua bán/biên nhận thanh toán. Nếu bạn cần hóa đơn VAT, vui lòng gửi yêu cầu xuất hóa đơn VAT trong trang hồ sơ khách hàng.</p>

        <div class="footer">
            <p>Cảm ơn bạn đã chọn MARRON HOTEL!</p>
            <p><small>Email được gửi tự động, vui lòng không trả lời.</small></p>
        </div>
    </div>
</body>
</html>

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
                    @if($payment->method == 'bank_transfer')
                        <strong>Chuyển khoản ngân hàng</strong>
                    @else
                        <strong>{{ ucfirst($payment->method) }}</strong>
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