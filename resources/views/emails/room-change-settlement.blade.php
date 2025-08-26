<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <h2 style="margin-bottom: 8px;">@if($isRefund) Thông báo hoàn tiền @else Thông báo chênh lệch cần thanh toán @endif</h2>
    <p>Xin chào {{ $booking->user->name ?? 'Quý khách' }},</p>
    <p>
        Yêu cầu đổi phòng của quý khách cho mã đặt phòng <strong>#{{ $booking->booking_id }}</strong> đã được hoàn tất.
        Dưới đây là thông tin quyết toán chênh lệch:
    </p>

    <ul>
        <li>Phòng cũ: {{ $roomChange->oldRoom->name ?? ('Phòng #' . ($roomChange->old_room_id)) }}</li>
        <li>Phòng mới: {{ $roomChange->newRoom->name ?? ('Phòng #' . ($roomChange->new_room_id)) }}</li>
        <li>Khoảng thời gian lưu trú: {{ optional($booking->check_in_date)->format('d/m/Y') }} → {{ optional($booking->check_out_date)->format('d/m/Y') }}</li>
    </ul>

    @if($isRefund)
        <p>Số tiền hoàn lại: <strong>{{ number_format($amount, 0, ',', '.') }} VNĐ</strong>.</p>
        <p>Chúng tôi sẽ hoàn tiền qua phương thức đã thanh toán trước đó trong vòng 3-5 ngày làm việc.</p>
    @else
        <p>Số tiền cần thanh toán thêm: <strong>{{ number_format($amount, 0, ',', '.') }} VNĐ</strong>.</p>
        <p>Quý khách vui lòng thanh toán số tiền chênh lệch để hoàn tất việc đổi phòng.</p>
    @endif

    <p>Nếu cần hỗ trợ, vui lòng phản hồi email này. Xin cảm ơn!</p>
    <p>Trân trọng,<br/>Marron Hotel</p>
</div>


