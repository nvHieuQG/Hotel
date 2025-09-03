<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đổi phòng tour đã hoàn tất</title>
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
            background-color: #17a2b8;
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
        .completion-notice {
            background-color: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
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
        <h1>🎉 Đổi phòng tour đã hoàn tất</h1>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $user_name }}</strong>,</p>
        
        <div class="completion-notice">
            <h3>✅ Hoàn tất thành công</h3>
            <p>Yêu cầu đổi phòng tour của bạn đã được hoàn tất thành công. Bạn có thể yên tâm về việc sắp xếp phòng mới.</p>
        </div>
        
        <div class="info-box">
            <h3>Thông tin tour:</h3>
            <p><strong>Mã tour:</strong> #{{ $booking_id }}</p>
            <p><strong>Tên tour:</strong> {{ $tour_name }}</p>
            <p><strong>Ngày check-in:</strong> {{ \Carbon\Carbon::parse($check_in_date)->format('d/m/Y') }}</p>
            <p><strong>Ngày check-out:</strong> {{ \Carbon\Carbon::parse($check_out_date)->format('d/m/Y') }}</p>
        </div>
        
        <div class="info-box">
            <h3>Thông tin phòng mới:</h3>
            <p><strong>Phòng cũ:</strong> {{ $from_room }}</p>
            <p><strong>Phòng mới:</strong> {{ $to_room }}</p>
            
            @if($price_difference != 0)
                <div class="price-difference {{ $price_difference > 0 ? 'price-positive' : 'price-negative' }}">
                    @if($price_difference > 0)
                        Đã thanh toán thêm: +{{ number_format($price_difference) }} VNĐ
                    @else
                        Đã hoàn tiền: {{ number_format($price_difference) }} VNĐ
                    @endif
                </div>
            @else
                <div class="price-difference price-zero">
                    Không có chênh lệch giá
                </div>
            @endif
        </div>
        
        <p>Chúng tôi chúc bạn có một chuyến du lịch tuyệt vời!</p>
        
        <p>Trân trọng,<br>
        <strong>Đội ngũ khách sạn</strong></p>
    </div>
    
    <div class="footer">
        <p>Email này được gửi tự động, vui lòng không trả lời.</p>
    </div>
</body>
</html>
