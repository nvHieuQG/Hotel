<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Yêu cầu đổi phòng tour đã được duyệt</title>
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
            background-color: #28a745;
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
        }
        .info-box {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .price-difference {
            font-size: 18px;
            font-weight: bold;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
        }
        .price-positive {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .price-negative {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .price-zero {
            background-color: #e2e3e5;
            color: #383d41;
            border: 1px solid #d6d8db;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>✅ Yêu cầu đổi phòng tour đã được duyệt</h1>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $user_name }}</strong>,</p>
        
        <p>Chúng tôi xin thông báo rằng yêu cầu đổi phòng tour của bạn đã được duyệt thành công.</p>
        
        <div class="info-box">
            <h3>Thông tin tour:</h3>
            <p><strong>Mã tour:</strong> #{{ $booking_id }}</p>
            <p><strong>Tên tour:</strong> {{ $tour_name }}</p>
            <p><strong>Ngày check-in:</strong> {{ \Carbon\Carbon::parse($check_in_date)->format('d/m/Y') }}</p>
            <p><strong>Ngày check-out:</strong> {{ \Carbon\Carbon::parse($check_out_date)->format('d/m/Y') }}</p>
        </div>
        
        <div class="info-box">
            <h3>Thông tin đổi phòng:</h3>
            <p><strong>Từ phòng:</strong> {{ $from_room }}</p>
            <p><strong>Đến phòng:</strong> {{ $to_room }}</p>
            
            @if($price_difference != 0)
                <div class="price-difference {{ $price_difference > 0 ? 'price-positive' : 'price-negative' }}">
                    @if($price_difference > 0)
                        Cần thanh toán thêm: +{{ number_format($price_difference) }} VNĐ
                    @else
                        Được hoàn tiền: {{ number_format($price_difference) }} VNĐ
                    @endif
                </div>
            @else
                <div class="price-difference price-zero">
                    Không có chênh lệch giá
                </div>
            @endif
        </div>
        
        @if($admin_note)
        <div class="info-box">
            <h3>Ghi chú từ quản trị viên:</h3>
            <p>{{ $admin_note }}</p>
        </div>
        @endif
        
        <p>Vui lòng liên hệ với chúng tôi nếu bạn có bất kỳ thắc mắc nào.</p>
        
        <p>Trân trọng,<br>
        <strong>Đội ngũ khách sạn</strong></p>
    </div>
    
    <div class="footer">
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
</body>
</html>
