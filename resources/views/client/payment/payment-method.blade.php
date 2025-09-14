@extends('client.layouts.master')

@section('title', 'Chọn phương thức thanh toán')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-credit-card"></i> Chọn phương thức thanh toán
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Thông tin đặt phòng -->
                    <div class="alert alert-info">
                        <h6 class="mb-2">Thông tin đặt phòng: {{ $booking->booking_id }}</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1">Giá gốc: <strong>{{ number_format($booking->price, 0, ',', '.') }} VND</strong></p>
                                <p class="mb-1">Dịch vụ & phụ phí: <strong>{{ number_format($booking->surcharge + $booking->extra_services_total + $booking->total_services_price, 0, ',', '.') }} VND</strong></p>
                                @if($booking->promotion_discount > 0)
                                    <p class="mb-1 text-success">Giảm giá: <strong>-{{ number_format($booking->promotion_discount, 0, ',', '.') }} VND</strong></p>
                                    <p class="mb-1 text-danger">Tổng tiền: <strong>{{ number_format($booking->final_price, 0, ',', '.') }} VND</strong></p>
                                @else
                                    <p class="mb-1">Tổng tiền: <strong>{{ number_format($booking->price + $booking->surcharge + $booking->extra_services_total + $booking->total_services_price, 0, ',', '.') }} VND</strong></p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                @if($booking->promotion_discount > 0)
                                    <div class="text-center">
                                        <div class="badge bg-success text-white mb-2">
                                            <i class="fas fa-gift"></i> {{ $booking->promotion_code }}
                                        </div>
                                        <div class="small text-muted">Mã khuyến mại đã áp dụng</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách phương thức thanh toán -->
                    <div class="row">
                        @foreach($paymentMethods as $method => $info)
                            @if($info['enabled'])
                            <div class="col-md-6 mb-3">
                                <div class="card payment-method-card" data-method="{{ $method }}">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-3">
                                            <i class="{{ $info['icon'] }} fa-3x text-primary"></i>
                                        </div>
                                        <h5 class="card-title">{{ $info['name'] }}</h5>
                                        <p class="card-text text-muted">{{ $info['description'] }}</p>
                                        
                                        @if($method === 'credit_card')
                                            <a href="{{ route('payment.credit-card', $booking->id) }}" class="btn btn-primary">
                                                <i class="fas fa-credit-card"></i> Thanh toán bằng thẻ
                                            </a>
                                        @elseif($method === 'bank_transfer')
                                            <a href="{{ route('payment.bank-transfer', $booking->id) }}" class="btn btn-primary">
                                                <i class="fas fa-university"></i> Chuyển khoản ngân hàng
                                            </a>
                                        @elseif($method === 'cod')
                                            <button type="button" class="btn btn-secondary" onclick="confirmCOD()">
                                                <i class="fas fa-money-bill-wave"></i> Chọn thanh toán tại khách sạn
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    <!-- Thông tin bảo mật -->
                    <div class="mt-4">
                        <div class="alert alert-warning">
                            <h6><i class="fas fa-shield-alt"></i> Bảo mật thanh toán</h6>
                            <ul class="mb-0">
                                <li>Thông tin thanh toán của bạn được mã hóa và bảo mật</li>
                                <li>Chúng tôi không lưu trữ thông tin thẻ tín dụng</li>
                                <li>Giao dịch được xử lý bởi các cổng thanh toán uy tín</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Nút quay lại -->
                    <div class="text-center mt-3">
                        <a href="{{ route('confirm-info-payment', $booking->id) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function confirmCOD() {
    if (confirm('Bạn có chắc chắn muốn chọn thanh toán tại khách sạn?')) {
        // Xử lý thanh toán COD
        alert('Tính năng thanh toán tại khách sạn sẽ được phát triển sau.');
    }
}
</script>

@push('styles')
<link rel="stylesheet" href="{{ asset('client/css/pages/payment-method-simple.css') }}">
@endpush
@endsection 