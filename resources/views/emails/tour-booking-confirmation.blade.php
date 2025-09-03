<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác nhận đặt phòng tour thành công</title>
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
            border-bottom: 3px solid #17a2b8;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #17a2b8;
            margin-bottom: 10px;
        }
        .booking-id {
            background-color: #17a2b8;
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
        .total-section {
            background-color: #17a2b8;
            color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
        }
        .total-amount {
            font-size: 24px;
            font-weight: bold;
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
            background-color: #17a2b8;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .btn:hover {
            background-color: #138496;
        }
        .room-details {
            background-color: #e3f2fd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .room-item {
            padding: 10px;
            margin: 5px 0;
            background-color: white;
            border-radius: 3px;
            border-left: 4px solid #17a2b8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🏨 Khách Sạn MARRON</div>
            <h1 style="color: #17a2b8; margin: 10px 0;">Xác nhận đặt phòng tour thành công!</h1>
            <div class="booking-id">Mã Booking: {{ $tourBooking->booking_id }}</div>
        </div>

        <div class="info-section">
            <h3 style="color: #17a2b8; margin-top: 0;">Thông tin khách hàng</h3>
            <div class="info-row">
                <span class="info-label">Họ tên:</span>
                <span class="info-value">{{ $user->name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ $user->email }}</span>
            </div>
            @if($user->phone)
            <div class="info-row">
                <span class="info-label">Số điện thoại:</span>
                <span class="info-value">{{ $user->phone }}</span>
            </div>
            @endif
        </div>

        <div class="info-section">
            <h3 style="color: #17a2b8; margin-top: 0;">Thông tin tour</h3>
            <div class="info-row">
                <span class="info-label">Tên tour:</span>
                <span class="info-value">{{ $tourBooking->tour_name }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ngày check-in:</span>
                <span class="info-value">{{ $tourBooking->check_in_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Ngày check-out:</span>
                <span class="info-value">{{ $tourBooking->check_out_date->format('d/m/Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Số đêm:</span>
                <span class="info-value">{{ $tourBooking->total_nights }} đêm</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tổng số khách:</span>
                <span class="info-value">{{ $tourBooking->total_guests }} người</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tổng số phòng:</span>
                <span class="info-value">{{ $tourBooking->total_rooms }} phòng</span>
            </div>
        </div>

        @if($tourBooking->tourBookingRooms->count() > 0)
        <div class="room-details">
            <h3 style="color: #17a2b8; margin-top: 0;">Chi tiết phòng đã đặt</h3>
            @foreach($tourBooking->tourBookingRooms as $room)
            <div class="room-item">
                <strong>{{ $room->roomType->name }}</strong><br>
                <small>Số lượng: {{ $room->quantity }} phòng × {{ $room->guests_per_room }} khách/phòng</small><br>
                <small>Giá: {{ number_format($room->price_per_room, 0, ',', '.') }} VNĐ/phòng</small><br>
                <strong>Tổng: {{ number_format($room->total_price, 0, ',', '.') }} VNĐ</strong>
            </div>
            @endforeach
        </div>
        @endif

        @if($tourBooking->special_requests)
        <div class="info-section">
            <h3 style="color: #17a2b8; margin-top: 0;">Yêu cầu đặc biệt</h3>
            <p style="margin: 0; font-style: italic;">{{ $tourBooking->special_requests }}</p>
        </div>
        @endif

        <div class="total-section">
            <div>Tổng tiền thanh toán</div>
            <div class="total-amount">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</div>
        </div>

        <div class="contact-info">
            <h3 style="color: #17a2b8; margin-top: 0;">Thông tin liên hệ</h3>
            <p style="margin: 5px 0;"><strong>Địa chỉ:</strong> 123 Đường ABC, Quận XYZ, TP.HCM</p>
            <p style="margin: 5px 0;"><strong>Điện thoại:</strong> 028-1234-5678</p>
            <p style="margin: 5px 0;"><strong>Email:</strong> info@luxuryhotel.com</p>
            <p style="margin: 5px 0;"><strong>Website:</strong> www.luxuryhotel.com</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ route('tour-booking.show', $tourBooking->id) }}" class="btn">Xem chi tiết booking</a>
            <a href="{{ route('index') }}" class="btn">Về trang chủ</a>
        </div>

        <div class="footer">
            <p>Cảm ơn bạn đã chọn Khách Sạn MARRON!</p>
            <p>Chúng tôi rất hân hạnh được phục vụ bạn trong chuyến đi sắp tới.</p>
            <p><small>Email này được gửi tự động, vui lòng không trả lời.</small></p>
        </div>
    </div>
</body>
</html>
