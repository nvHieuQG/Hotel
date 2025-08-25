<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yêu cầu xuất hóa đơn VAT - Tour Booking</title>
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
            text-align: center;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .info-section {
            background-color: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .highlight {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 20px;
            background-color: #f8f9fa;
            border-radius: 5px;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>🏨 Marrom Hotel</h2>
        <h3>Yêu cầu xuất hóa đơn VAT</h3>
    </div>

    <div class="content">
        <p>Xin chào Admin,</p>
        
        <p>Khách hàng đã yêu cầu xuất hóa đơn VAT cho tour booking sau:</p>

        <div class="info-section">
            <h4>📋 Thông tin Tour Booking</h4>
            <p><strong>Mã booking:</strong> #{{ $tourBooking->id }}</p>
            <p><strong>Tên tour:</strong> {{ $tourBooking->tour_name ?? 'N/A' }}</p>
            <p><strong>Ngày check-in:</strong> {{ $tourBooking->check_in_date ? $tourBooking->check_in_date->format('d/m/Y') : 'N/A' }}</p>
            <p><strong>Ngày check-out:</strong> {{ $tourBooking->check_out_date ? $tourBooking->check_out_date->format('d/m/Y') : 'N/A' }}</p>
            <p><strong>Số khách:</strong> {{ $tourBooking->total_guests ?? 0 }} người</p>
            <p><strong>Số phòng:</strong> {{ $tourBooking->total_rooms ?? 0 }} phòng</p>
        </div>

        <div class="info-section">
            <h4>🏢 Thông tin công ty</h4>
            <p><strong>Tên công ty:</strong> {{ $tourBooking->company_name ?? 'N/A' }}</p>
            <p><strong>Mã số thuế:</strong> {{ $tourBooking->company_tax_code ?? 'N/A' }}</p>
            <p><strong>Địa chỉ:</strong> {{ $tourBooking->company_address ?? 'N/A' }}</p>
            <p><strong>Email:</strong> {{ $tourBooking->company_email ?? 'N/A' }}</p>
            <p><strong>Điện thoại:</strong> {{ $tourBooking->company_phone ?? 'N/A' }}</p>
        </div>

        <div class="highlight">
            <h4>⚠️ Hành động cần thiết</h4>
            <p>Vui lòng xử lý yêu cầu xuất hóa đơn VAT này trong hệ thống admin.</p>
            <p><strong>Link xử lý:</strong> <a href="{{ route('admin.tour-vat-invoices.show', $tourBooking->id) }}">Xem chi tiết và xử lý</a></p>
        </div>

        <p>Trân trọng,<br>
        Hệ thống Marrom Hotel</p>
    </div>

    <div class="footer">
        <p>Email này được gửi tự động từ hệ thống quản lý khách sạn Marrom Hotel</p>
        <p>Vui lòng không trả lời email này</p>
    </div>
</body>
</html>
