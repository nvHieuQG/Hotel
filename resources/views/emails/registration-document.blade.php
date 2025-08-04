<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giấy đăng ký tạm chú tạm vắng</title>
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
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .hotel-name {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        .booking-info {
            background-color: #e8f4fd;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .label {
            font-weight: bold;
            color: #2c3e50;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
            font-size: 14px;
            color: #666;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
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
        <div class="hotel-name">{{ $hotelInfo['name'] }}</div>
        <div>{{ $hotelInfo['address'] }}</div>
        <div>Điện thoại: {{ $hotelInfo['phone'] }}</div>
        <div>Email: {{ $hotelInfo['email'] }}</div>
    </div>

    <h2>Giấy đăng ký tạm chú tạm vắng</h2>
    
    <p>Kính gửi {{ $booking->guest_full_name }},</p>
    
    <p>Chúng tôi đã tạo giấy đăng ký tạm chú tạm vắng cho đặt phòng của bạn. File đính kèm là file PDF (.pdf) có thể mở bằng Adobe Reader, trình duyệt web hoặc các ứng dụng tương tự theo mẫu CT01 chuẩn của Bộ Công an Việt Nam.</p>

    <div class="booking-info">
        <h3>Thông tin đặt phòng:</h3>
        <div class="info-row">
            <span class="label">Mã đặt phòng:</span> {{ $booking->booking_id }}
        </div>
        <div class="info-row">
            <span class="label">Khách lưu trú:</span> {{ $booking->guest_full_name }}
        </div>
        <div class="info-row">
            <span class="label">Số CMND:</span> {{ $booking->guest_id_number }}
        </div>
        <div class="info-row">
            <span class="label">Ngày sinh:</span> {{ $booking->guest_birth_date->format('d/m/Y') }}
        </div>
        <div class="info-row">
            <span class="label">Ngày nhận phòng:</span> {{ $booking->check_in_date->format('d/m/Y') }}
        </div>
        <div class="info-row">
            <span class="label">Ngày trả phòng:</span> {{ $booking->check_out_date->format('d/m/Y') }}
        </div>
        <div class="info-row">
            <span class="label">Số đêm lưu trú:</span> {{ $booking->check_in_date->diffInDays($booking->check_out_date) }} đêm
        </div>
    </div>

    <p><strong>Lưu ý quan trọng:</strong></p>
    <ul>
        <li>File đính kèm có định dạng PDF (.pdf), có thể mở bằng Adobe Reader, trình duyệt web hoặc các ứng dụng tương tự</li>
        <li><strong>Mật khẩu mở file: {{ $booking->guest_id_number }}</strong></li>
        <li>Giấy đăng ký tuân thủ mẫu CT01 chuẩn của Bộ Công an Việt Nam</li>
        <li>Vui lòng in và ký tên trước khi sử dụng cho mục đích chính thức</li>
        <li>Nếu cần hỗ trợ, vui lòng liên hệ với chúng tôi</li>
    </ul>

    <div class="footer">
        <p><strong>Trân trọng,</strong></p>
        <p>{{ $hotelInfo['name'] }}</p>
        <p>Điện thoại: {{ $hotelInfo['phone'] }}</p>
        <p>Email: {{ $hotelInfo['email'] }}</p>
        <p>Địa chỉ: {{ $hotelInfo['address'] }}</p>
    </div>
</body>
</html> 