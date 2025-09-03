<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Hóa đơn VAT - Tour Booking</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            font-size: 14px; 
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header { 
            background-color: #2c3e50; 
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
            font-size: 12px;
        }
        .info-box {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin: 15px 0;
        }
        .highlight {
            background-color: #e8f4f8;
            border-left: 4px solid #17a2b8;
            padding: 10px;
            margin: 15px 0;
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
        <h1>HÓA ĐƠN VAT - TOUR BOOKING</h1>
        <p>MARRON HOTEL</p>
    </div>
    
    <div class="content">
        <p>Xin chào <strong>{{ $info['receiverName'] }}</strong>,</p>
        
        <p>Chúng tôi xin thông báo rằng hóa đơn VAT cho tour booking của bạn đã được tạo thành công.</p>
        
        <div class="info-box">
            <h3>Thông tin Tour Booking:</h3>
            <p><strong>Mã Tour:</strong> {{ $tourBooking->booking_code }}</p>
            <p><strong>Tên Tour:</strong> {{ $tourBooking->tour_name }}</p>
            <p><strong>Check-in:</strong> {{ $tourBooking->check_in_date->format('d/m/Y') }}</p>
            <p><strong>Check-out:</strong> {{ $tourBooking->check_out_date->format('d/m/Y') }}</p>
            <p><strong>Số khách:</strong> {{ $tourBooking->total_guests }} người</p>
            <p><strong>Số phòng:</strong> {{ $tourBooking->total_rooms }} phòng</p>
        </div>
        
        <div class="highlight">
            <h3>Thông tin Hóa đơn VAT:</h3>
            <p><strong>Số hóa đơn:</strong> {{ $tourBooking->vat_invoice_number ?? 'N/A' }}</p>
            <p><strong>Ngày tạo:</strong> {{ now()->format('d/m/Y H:i') }}</p>
            <p><strong>Tổng tiền:</strong> {{ number_format($priceData['grandTotal'] ?? $tourBooking->final_price ?? 0, 0, ',', '.') }} VNĐ</p>
        </div>
        
        <p>Hóa đơn VAT đã được đính kèm trong email này. Bạn có thể tải xuống để sử dụng cho mục đích kê khai thuế.</p>
        
        <p><strong>Lưu ý:</strong></p>
        <ul>
            <li>Hóa đơn này đã bao gồm thuế VAT 10%</li>
            <li>Vui lòng giữ hóa đơn này để làm bằng chứng thanh toán</li>
            <li>Mọi thắc mắc vui lòng liên hệ: +84 98 348 06 83</li>
        </ul>
        
        <p>Cảm ơn bạn đã sử dụng dịch vụ của chúng tôi!</p>
        
        <p>Trân trọng,<br>
        <strong>MARRON HOTEL</strong></p>
    </div>
    
    <div class="footer">
        <p>Email này được gửi tự động từ hệ thống đặt phòng</p>
        <p>© {{ date('Y') }} MARRON HOTEL. All rights reserved.</p>
    </div>
</body>
</html>
