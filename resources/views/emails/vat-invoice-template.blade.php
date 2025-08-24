<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>Hóa đơn VAT</title>
    <style>
        body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 12px; }
        .header { text-align:center; font-weight:700; font-size:16px; margin-bottom:8px }
        .sub { text-align:center; color:#555; margin-bottom:16px }
        table { width:100%; border-collapse: collapse; }
        th, td { border:1px solid #333; padding:6px; }
        th { background:#f2f2f2; }
        .right { text-align:right }
    </style>
    </head>
<body>
    <div class="header">HÓA ĐƠN GIÁ TRỊ GIA TĂNG</div>
    <div class="sub">Căn cứ quy định pháp luật hiện hành (từ 01/07)</div>
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
                {{ $info['companyName'] ?? $booking->user->name }}<br>
                MST: {{ $info['taxCode'] ?? 'N/A' }}<br>
                Đ/c: {{ $info['companyAddress'] ?? 'N/A' }}
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
                <td>Tiền phòng ({{ number_format($nightly) }}đ/đêm × {{ $nights }} đêm)</td>
                <td class="right">{{ $nights }}</td>
                <td class="right">{{ number_format($nightly) }}</td>
                <td class="right">{{ number_format($roomCost) }}</td>
            </tr>
            @if($services > 0)
            <tr>
                <td>2</td>
                <td>Dịch vụ kèm theo</td>
                <td class="right">1</td>
                <td class="right">{{ number_format($services) }}</td>
                <td class="right">{{ number_format($services) }}</td>
            </tr>
            @endif
            @if($guestSurcharge > 0)
            <tr>
                <td>3</td>
                <td>Phụ phí khách</td>
                <td class="right">1</td>
                <td class="right">{{ number_format($guestSurcharge) }}</td>
                <td class="right">{{ number_format($guestSurcharge) }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="right">Giảm giá/khuyến mại</th>
                <th class="right">-{{ number_format($totalDiscount) }}</th>
            </tr>
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
        <p><em>Ghi chú: Để xuất hóa đơn VAT, vui lòng chuyển khoản công ty hoặc thanh toán bằng thẻ công ty. Liên hệ nhân viên để được hỗ trợ.</em></p>
    @endif
</body>
</html>


