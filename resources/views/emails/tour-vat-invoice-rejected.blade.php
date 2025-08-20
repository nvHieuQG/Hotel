<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Yêu cầu hóa đơn VAT bị từ chối - Tour Booking</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #dc3545; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; background-color: #f9f9f9; }
        .info-box { background-color: white; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .company-info { background-color: #e8f4f8; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .rejection-box { background-color: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 15px 0; border-radius: 5px; }
        .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Yêu cầu hóa đơn VAT bị từ chối</h1>
            <p>Tour Booking #{{ $tourBooking->booking_id }}</p>
        </div>
        
        <div class="content">
            <div class="rejection-box">
                <h2 style="color: #721c24; margin-top: 0;">
                    <i class="fas fa-exclamation-triangle"></i> Yêu cầu hóa đơn VAT đã bị từ chối
                </h2>
                <p><strong>Lý do từ chối:</strong> {{ $rejectionReason }}</p>
                <p><strong>Ngày từ chối:</strong> {{ now()->format('d/m/Y H:i') }}</p>
            </div>
            
            <h2>Thông tin Tour Booking</h2>
            <div class="info-box">
                <p><strong>Tên tour:</strong> {{ $tourBooking->tour_name }}</p>
                <p><strong>Số khách:</strong> {{ $tourBooking->total_guests }} người</p>
                <p><strong>Số phòng:</strong> {{ $tourBooking->total_rooms }} phòng</p>
                <p><strong>Check-in:</strong> {{ $tourBooking->check_in_date->format('d/m/Y') }}</p>
                <p><strong>Check-out:</strong> {{ $tourBooking->check_out_date->format('d/m/Y') }}</p>
                <p><strong>Tổng tiền:</strong> {{ number_format($tourBooking->total_amount_before_discount, 0, ',', '.') }} VNĐ</p>
                @if($tourBooking->promotion_discount > 0)
                    <p><strong>Giảm giá:</strong> -{{ number_format($tourBooking->promotion_discount, 0, ',', '.') }} VNĐ</p>
                    <p><strong>Giá cuối:</strong> {{ number_format($tourBooking->final_amount, 0, ',', '.') }} VNĐ</p>
                @endif
            </div>
            
            <h2>Thông tin công ty đã yêu cầu</h2>
            <div class="company-info">
                <p><strong>Tên công ty:</strong> {{ $tourBooking->company_name }}</p>
                <p><strong>Mã số thuế:</strong> {{ $tourBooking->company_tax_code }}</p>
                <p><strong>Địa chỉ:</strong> {{ $tourBooking->company_address }}</p>
                <p><strong>Email:</strong> {{ $tourBooking->company_email }}</p>
                <p><strong>Điện thoại:</strong> {{ $tourBooking->company_phone }}</p>
            </div>
            
            <h2>Thông tin khách hàng</h2>
            <div class="info-box">
                <p><strong>Họ tên:</strong> {{ $tourBooking->user->name }}</p>
                <p><strong>Email:</strong> {{ $tourBooking->user->email }}</p>
                <p><strong>Ngày đặt:</strong> {{ $tourBooking->created_at->format('d/m/Y H:i') }}</p>
            </div>
            
            <div style="text-align: center; margin: 30px 0;">
                <p><strong>Nếu bạn cần hỗ trợ hoặc có thắc mắc, vui lòng liên hệ:</strong></p>
                <p><strong>Hotline:</strong> 1900-xxxx</p>
                <p><strong>Email:</strong> support@hotel.com</p>
            </div>
            
            <div class="alert alert-info" style="background-color: #d1ecf1; border: 1px solid #bee5eb; padding: 15px; border-radius: 5px;">
                <p><strong>Lưu ý:</strong> Bạn có thể yêu cầu xuất hóa đơn VAT lại sau khi đã khắc phục các vấn đề được nêu trong lý do từ chối.</p>
            </div>
        </div>
        
        <div class="footer">
            <p>Email này được gửi tự động từ hệ thống đặt phòng khách sạn</p>
            <p>Vui lòng không trả lời email này</p>
        </div>
    </div>
</body>
</html>
