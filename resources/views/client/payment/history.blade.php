@extends('client.layouts.master')

@section('title', 'Lịch sử thanh toán')

@section('content')
<div class="hero-wrap" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');">
  <div class="overlay"></div>
  <div class="container">
    <div class="row no-gutters slider-text d-flex align-items-end justify-content-center">
      <div class="col-md-9 text-center d-flex align-items-end justify-content-center">
        <div class="text">
          <p class="breadcrumbs mb-2">
            <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
            <span class="mr-2"><a href="{{ route('booking.detail', $booking->id) }}">Đặt phòng</a></span>
            <span>Lịch sử thanh toán</span>
          </p>
          <h3 class="mb-4 bread">Lịch sử thanh toán</h3>
        </div>
      </div>
    </div>
  </div>
  </div>

  <section class="ftco-section bg-light">
    <div class="container">
      <div class="card shadow-sm rounded-lg">
        <div class="card-header">
          <i class="fas fa-receipt mr-2"></i> Đặt phòng #{{ $booking->booking_id }}
        </div>
        <div class="card-body">
          @if($paymentHistory->count())
            <div class="table-responsive">
              <table class="table table-sm align-middle">
                <thead>
                  <tr>
                    <th>Thời gian</th>
                    <th>Phương thức</th>
                    <th>Giá gốc</th>
                    <th>Khuyến mại</th>
                    <th>Thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Mã giao dịch</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($paymentHistory as $p)
                    <tr>
                      <td>{{ $p->paid_at ? $p->paid_at->format('d/m/Y H:i') : $p->created_at->format('d/m/Y H:i') }}</td>
                      <td>
                        @if($p->method === 'credit_card')
                            <i class="fas fa-credit-card text-primary"></i>
                        @elseif($p->method === 'bank_transfer')
                            <i class="fas fa-university text-success"></i>
                        @endif
                        {{ ucfirst($p->method) }}
                      </td>
                      <td>{{ number_format($booking->total_booking_price) }} VND</td>
                      <td class="text-success">
                        -{{ number_format((float)($p->discount_amount ?? 0)) }} VND
                        @if(($p->discount_amount ?? 0) > 0 && $p->promotion)
                          <div class="mt-1">
                            <span class="badge bg-success">
                              <i class="fas fa-gift"></i>
                              {{ $p->promotion->title }}
                              @if($p->promotion->code)
                                ({{ $p->promotion->code }})
                              @endif
                            </span>
                          </div>
                        @endif
                      </td>
                      <td class="text-primary"><strong>{{ number_format((float)$p->amount) }} VND</strong></td>
                      <td><span class="badge bg-{{ $p->status_color }}">{{ $p->status_text }}</span></td>
                      <td><code>{{ $p->transaction_id ?? 'N/A' }}</code></td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @else
            <div class="alert alert-info">Chưa có giao dịch nào.</div>
          @endif
        </div>
      </div>
    </div>
  </section>
@endsection


