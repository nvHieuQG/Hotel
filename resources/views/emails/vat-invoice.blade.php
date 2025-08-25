<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hóa đơn VAT - {{ $booking->booking_id }}</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #C9A888; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px; }
        .info-box { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; border-left: 4px solid #C9A888; }
        .footer { text-align: center; margin-top: 20px; padding: 20px; color: #666; font-size: 14px; }
        .btn { display: inline-block; padding: 10px 20px; background-color: #C9A888; color: white; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .highlight { color: #C9A888; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏨 Marrom Hotel</h1>
            <h2>Hóa đơn VAT</h2>
        </div>
        
        <div class="content">
            <p>Xin chào <strong>{{ $info['receiverName'] ?? $booking->user->name }}</strong>,</p>
            
            <p>Marrom Hotel xin gửi kèm hóa đơn VAT cho đặt phòng của bạn.</p>
            
            <div class="info-box">
                <h3>📋 Thông tin đặt phòng</h3>
                <p><strong>Mã đặt phòng:</strong> <span class="highlight">{{ $booking->booking_id }}</span></p>
                <p><strong>Ngày check-in:</strong> {{ $booking->check_in_date->format('d/m/Y') }}</p>
                <p><strong>Ngày check-out:</strong> {{ $booking->check_out_date->format('d/m/Y') }}</p>
                <p><strong>Phòng:</strong> {{ $booking->room->name ?? 'N/A' }} - {{ $booking->room->roomType->name ?? 'N/A' }}</p>
            </div>
            
            <div class="info-box">
                <h3>🏢 Thông tin công ty</h3>
                <p><strong>Tên công ty:</strong> {{ $info['companyName'] ?? 'N/A' }}</p>
                <p><strong>Mã số thuế:</strong> {{ $info['taxCode'] ?? 'N/A' }}</p>
                <p><strong>Địa chỉ:</strong> {{ $info['companyAddress'] ?? 'N/A' }}</p>
            </div>
            
            <p><strong>📎 Hóa đơn VAT đã được đính kèm</strong> - Vui lòng mở file PDF để xem chi tiết đầy đủ.</p>
            
            <p><strong>💡 Lưu ý:</strong></p>
            <ul>
                <li>Hóa đơn này đã bao gồm VAT 10%</li>
                <li>Không thu thêm phí VAT</li>
                <li>Hóa đơn hợp lệ để kê khai thuế</li>
            </ul>
            
            <p>Nếu có bất kỳ thắc mắc nào, vui lòng liên hệ với chúng tôi:</p>
            <p><strong>📞 Điện thoại:</strong> 1900-xxxx</p>
            <p><strong>📧 Email:</strong> info@marromhotel.com</p>
        </div>
        
        <div class="footer">
            <p>Trân trọng,</p>
            <p><strong>Marrom Hotel</strong></p>
            <p>🏨 Khách sạn 5 sao hàng đầu Việt Nam</p>
        </div>
    </div>
</body>
</html>


