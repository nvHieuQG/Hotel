<div class="container-fluid">
  <div class="row">
    <div class="col-lg-12 mb-3">
      <div class="position-relative">
        @php
          $room       = $booking->room;
          $roomType   = $room->roomType ?? null;
          $imageModel = $room->primaryImage ?? $room->firstImage ?? ($room->images->first() ?? null);
          $roomImage  = $imageModel ? ($imageModel->full_image_url ?? $imageModel->image_url) : null;
        @endphp
        @if($roomImage)
          <img src="{{ $roomImage }}" alt="Ảnh phòng {{ $roomType->name ?? '' }}" class="img-fluid rounded w-100" style="max-height:320px; object-fit:cover;">
        @else
          <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:320px;">
            <div class="text-center text-muted">
              <i class="fas fa-image fa-3x mb-2"></i>
              <div>Chưa có ảnh phòng</div>
            </div>
          </div>
        @endif
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-light fw-bold"><i class="fas fa-info-circle me-2"></i>Thông Tin Đặt Phòng</div>
        <div class="card-body">
          <div class="row g-2 small">
            <div class="col-md-6">Mã đặt phòng</div>
            <div class="col-md-6 fw-semibold">{{ $booking->booking_id }}</div>
            <div class="col-md-6">Loại phòng</div>
            <div class="col-md-6 fw-semibold">{{ $roomType->name ?? 'N/A' }}</div>
            <div class="col-md-6">Check-in</div>
            <div class="col-md-6 fw-semibold">{{ $booking->check_in_date->format('d/m/Y') }}</div>
            <div class="col-md-6">Check-out</div>
            <div class="col-md-6 fw-semibold">{{ $booking->check_out_date->format('d/m/Y') }}</div>
            <div class="col-md-6">Số đêm</div>
            <div class="col-md-6 fw-semibold">{{ $booking->nights ?? $booking->check_in_date->diffInDays($booking->check_out_date) }} đêm</div>
            <div class="col-md-6">Tổng cộng</div>
            <div class="col-md-6 fw-bold text-success">{{ number_format($booking->total_booking_price,0,',','.') }} VNĐ</div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card border-0 shadow-sm mb-3">
        <div class="card-header bg-light fw-bold"><i class="fas fa-user me-2"></i>Thông Tin Khách Hàng</div>
        <div class="card-body small">
          <div>Họ tên: <strong>{{ auth()->user()->name }}</strong></div>
          <div>Email: <strong>{{ auth()->user()->email }}</strong></div>
          <div>Sức chứa phòng: <strong>{{ $roomType->capacity ?? 'N/A' }}</strong></div>
          <div>Ngày đặt: <strong>{{ $booking->created_at->format('d/m/Y H:i') }}</strong></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  @php
    // Tính toán thanh toán
    $totalAmount = (float) (
        $booking->final_amount
        ?? $booking->final_price
        ?? $booking->total_booking_price
        ?? $booking->total_price
        ?? $booking->price
        ?? 0
    );
    $totalPaid = (float) ($booking->payments->where('status', 'completed')->sum('amount'));
    $remaining  = max(0, $totalAmount - $totalPaid);
    $percent    = $totalAmount > 0 ? round(($totalPaid / $totalAmount) * 100, 1) : 0;
  @endphp

  <div class="col-lg-12">
    <div class="card border-0 shadow-sm mb-3">
      <div class="card-header bg-light fw-bold"><i class="fas fa-receipt me-2"></i>Trạng thái thanh toán</div>
      <div class="card-body">
        <div class="row g-3 align-items-center">
          <div class="col-md-3">
            <div class="text-muted small">Tổng tiền</div>
            <div class="fw-bold">{{ number_format($totalAmount, 0, ',', '.') }} VNĐ</div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Đã thanh toán</div>
            <div class="fw-bold text-success">{{ number_format($totalPaid, 0, ',', '.') }} VNĐ</div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Còn lại</div>
            <div class="fw-bold text-danger">{{ number_format($remaining, 0, ',', '.') }} VNĐ</div>
          </div>
          <div class="col-md-3">
            <div class="text-muted small">Tỷ lệ hoàn thành</div>
            <div class="fw-bold">{{ $percent }}%</div>
          </div>
        </div>

        <div class="progress mt-3" style="height: 10px;">
          <div class="progress-bar" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>

        @if($remaining > 0)
          <div class="mt-3">
            <a href="{{ route('payment.bank-transfer', $booking->id) }}" class="btn btn-primary">
              <i class="fas fa-credit-card me-1"></i> Thanh toán thêm
            </a>
            <span class="text-muted small ms-2">Bạn có thể thanh toán tối thiểu 20%.</span>
          </div>
        @else
          <div class="mt-3 text-success"><i class="fas fa-check-circle me-1"></i>Đã thanh toán đủ.</div>
        @endif
      </div>
    </div>
  </div>

  <div class="col-lg-12">
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-light fw-bold"><i class="fas fa-history me-2"></i>Lịch sử thanh toán</div>
      <div class="card-body table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead>
            <tr>
              <th>Phương thức</th>
              <th>Số tiền</th>
              <th>Trạng thái</th>
              <th>Thời gian</th>
              <th>Mã giao dịch</th>
            </tr>
          </thead>
          <tbody>
            @forelse($booking->payments->sortByDesc('created_at') as $payment)
              <tr>
                <td>{{ $payment->method === 'credit_card' ? 'Thẻ tín dụng' : 'Chuyển khoản' }}</td>
                <td>{{ number_format($payment->amount, 0, ',', '.') }} VNĐ</td>
                <td>
                  @php
                    $badge = match($payment->status) {
                      'completed' => 'success',
                      'processing' => 'warning',
                      'pending' => 'secondary',
                      'failed' => 'danger',
                      default => 'light'
                    };
                  @endphp
                  <span class="badge badge-{{ $badge }}">{{ $payment->status }}</span>
                </td>
                <td>{{ optional($payment->created_at)->format('d/m/Y H:i') }}</td>
                <td>{{ $payment->transaction_id ?? '-' }}</td>
              </tr>
            @empty
              <tr><td colspan="5" class="text-center text-muted">Chưa có giao dịch</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

