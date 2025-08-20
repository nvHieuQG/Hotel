<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Hóa đơn VAT - Tour Booking</title>
    <style>
        body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 12px; }
        .header { text-align:center; font-weight:700; font-size:16px; margin-bottom:8px }
        .sub { text-align:center; color:#555; margin-bottom:16px }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #333; padding:6px; }
        th { background:#f2f2f2; }
        .right { text-align:right }
        .tour-info { background:#f9f9f9; padding:10px; margin:10px 0; border:1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">HÓA ĐƠN GIÁ TRỊ GIA TĂNG - TOUR BOOKING</div>
    <div class="sub">Căn cứ quy định pháp luật hiện hành (từ 01/07)</div>
    
    @php
        $nights = $tourBooking->check_in_date && $tourBooking->check_out_date 
            ? $tourBooking->check_in_date->copy()->startOfDay()->diffInDays($tourBooking->check_out_date->copy()->startOfDay()) 
            : 0;
        $roomCost = $tourBooking->total_rooms_amount ?? 0;
        $services = $tourBooking->total_services_amount ?? 0;
        $discount = $tourBooking->promotion_discount ?? 0;
        $grandTotal = $roomCost + $services - $discount;
        $vatRate = 0.1;
        $subtotal = round($grandTotal / (1 + $vatRate));
        $vatAmount = $grandTotal - $subtotal;
    @endphp
    
    <div class="tour-info">
        <strong>Thông tin Tour:</strong> {{ $tourBooking->tour_name }}<br>
        <strong>Mã Tour:</strong> {{ $tourBooking->booking_code }}<br>
        <strong>Check-in:</strong> {{ $tourBooking->check_in_date->format('d/m/Y') }} - Check-out: {{ $tourBooking->check_out_date->format('d/m/Y') }}<br>
        <strong>Số đêm:</strong> {{ $nights }} đêm | <strong>Số khách:</strong> {{ $tourBooking->total_guests }} người | <strong>Số phòng:</strong> {{ $tourBooking->total_rooms }} phòng
    </div>
    
    <table>
        <tr>
            <td>
                <strong>Bên bán:</strong><br>
                Marrom Hotel<br>
                MST: 0123456789<br>
                Đ/c: 123 Đường A, Quận B, TP. C
            </td>
            <td>
                <strong>Bên mua:</strong><br>
                {{ $info['companyName'] }}<br>
                MST: {{ $info['taxCode'] }}<br>
                Đ/c: {{ $info['companyAddress'] }}
            </td>
        </tr>
    </table>
    <br>
    <table>
        <thead>
            <tr>
                <th>STT</th>
                <th>Tên hàng hóa/dịch vụ</th>
                <th class="right">SL</th>
                <th class="right">Đơn giá</th>
                <th class="right">Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>Tiền phòng ({{ number_format($roomCost / max(1, $nights)) }}đ/đêm × {{ $nights }} đêm)</td>
                <td class="right">{{ $nights }}</td>
                <td class="right">{{ number_format($roomCost / max(1, $nights)) }}</td>
                <td class="right">{{ number_format($roomCost) }}</td>
            </tr>
            @if($services > 0)
            <tr>
                <td>2</td>
                <td>Dịch vụ kèm theo ({{ $tourBooking->total_rooms }} phòng × {{ $nights }} đêm)</td>
                <td class="right">{{ $tourBooking->total_rooms * $nights }}</td>
                <td class="right">{{ number_format($services / max(1, $tourBooking->total_rooms * $nights)) }}</td>
                <td class="right">{{ number_format($services) }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            @if($discount > 0)
            <tr>
                <th colspan="4" class="right">Giảm giá/khuyến mại</th>
                <th class="right">-{{ number_format($discount) }}</th>
            </tr>
            @endif
            <tr>
                <th colspan="4" class="right">Tổng tiền hàng (đã bao gồm VAT)</th>
                <th class="right">{{ number_format($grandTotal) }}</th>
            </tr>
            <tr>
                <th colspan="4" class="right">Trong đó:</th>
                <th class="right"></th>
            </tr>
            <tr>
                <th colspan="4" class="right">- Giá trước VAT</th>
                <th class="right">{{ number_format($subtotal) }}</th>
            </tr>
            <tr>
                <th colspan="4" class="right">- Thuế VAT ({{ (int)($vatRate * 100) }}%)</th>
                <th class="right">{{ number_format($vatAmount) }}</th>
            </tr>
        </tfoot>
    </table>
    
    <p><em><strong>Lưu ý:</strong> Tổng tiền {{ number_format($grandTotal) }} VNĐ đã bao gồm VAT 10%. Khách hàng không phải trả thêm phí VAT. Hóa đơn này chỉ để khách hàng kê khai thuế.</em></p>
    
    @if($grandTotal >= 5000000)
        <p><em>Ghi chú: Với hóa đơn từ 5.000.000đ, phương thức thanh toán phải là thẻ/tài khoản công ty hoặc chuyển khoản công ty.</em></p>
    @else
        <p><em>Ghi chú: Với hóa đơn dưới 5.000.000đ, khách hàng có thể thanh toán bằng phương thức cá nhân.</em></p>
    @endif
    
    <p><em><strong>Thông tin bổ sung:</strong></em></p>
    <ul>
        <li>Hóa đơn này được tạo tự động bởi hệ thống</li>
        <li>Vui lòng giữ hóa đơn này để làm bằng chứng thanh toán</li>
        <li>Mọi thắc mắc vui lòng liên hệ: 1900-xxxx</li>
        <li>Ngày tạo: {{ now()->format('d/m/Y H:i:s') }}</li>
    </ul>
</body>
</html>
