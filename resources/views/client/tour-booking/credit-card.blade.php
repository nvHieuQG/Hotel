@extends('client.layouts.master')

@section('title', 'Thanh toán bằng thẻ tín dụng - Tour Booking')

@section('content')
<div class="hero-wrap" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}'); font-family: 'Segoe UI', sans-serif;">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-items-end justify-content-center">
            <div class="col-md-9 text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <p class="breadcrumbs mb-2">
                        <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                        <span class="mr-2"><a href="{{ route('tour-booking.show', $tourBooking->id) }}">Tour Booking</a></span>
                        <span>Thanh toán thẻ tín dụng</span>
                    </p>
                    <h3 class="mb-4 bread">Thanh toán bằng thẻ tín dụng</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section bg-light" style="font-family: 'Segoe UI', sans-serif;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Thông tin đặt phòng -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #C9A888; color: white;">
                        <h5 class="mb-0">Thông tin đặt phòng Tour</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mã đặt phòng:</strong> {{ $tourBooking->booking_id }}</p>
                                <p><strong>Tên Tour:</strong> {{ $tourBooking->tour_name }}</p>
                                <p><strong>Tổng số khách:</strong> {{ $tourBooking->total_guests }} người</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Check-in:</strong> {{ $tourBooking->check_in_date->format('d/m/Y') }}</p>
                                <p><strong>Check-out:</strong> {{ $tourBooking->check_out_date->format('d/m/Y') }}</p>
                                <p><strong>Số tiền:</strong> <span class="text-primary font-weight-bold">{{ number_format($tourBooking->final_price ?? ($tourBooking->total_price - $tourBooking->promotion_discount), 0, ',', '.') }} VNĐ</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin thanh toán -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #C9A888; color: white;">
                        <h5 class="mb-0">Thông tin thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mã giao dịch:</strong> {{ $payment->transaction_id }}</p>
                                <p><strong>Phương thức:</strong> {{ $payment->gateway_name }}</p>
                                <p><strong>Trạng thái:</strong> <span class="badge badge-warning">{{ $payment->status }}</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Số tiền:</strong> {{ number_format($payment->amount, 0, ',', '.') }} VNĐ</p>
                                @if($payment->discount_amount > 0)
                                    <p><strong>Giảm giá:</strong> {{ number_format($payment->discount_amount, 0, ',', '.') }} VNĐ</p>
                                @endif
                                <p><strong>Ngày tạo:</strong> {{ $payment->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form thanh toán -->
                <form id="credit-card-form" action="{{ route('tour-booking.credit-card.confirm', $tourBooking->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_id" value="{{ $payment->transaction_id }}">
                    @if($payment->promotion_id)
                        <input type="hidden" name="promotion_id" value="{{ $payment->promotion_id }}">
                    @endif
                    
                    <div class="form-group mb-3">
                        <label for="cardholder_name" class="form-label">Tên chủ thẻ <span class="text-danger">*</span></label>
                        <input type="text" name="cardholder_name" id="cardholder_name" class="form-control" required
                               placeholder="Nhập tên như trên thẻ">
                    </div>

                    <div class="form-group mb-3">
                        <label for="card_number" class="form-label">Số thẻ <span class="text-danger">*</span></label>
                        <input type="text" name="card_number" id="card_number" class="form-control" required
                               placeholder="1234 5678 9012 3456" maxlength="16" pattern="\d{16}">
                        <small class="form-text text-muted">Nhập 16 chữ số trên thẻ</small>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="expiry_month" class="form-label">Tháng <span class="text-danger">*</span></label>
                                <select name="expiry_month" id="expiry_month" class="form-control" required>
                                    <option value="">MM</option>
                                    @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="expiry_year" class="form-label">Năm <span class="text-danger">*</span></label>
                                <select name="expiry_year" id="expiry_year" class="form-control" required>
                                    <option value="">YYYY</option>
                                    @for($i = date('Y'); $i <= date('Y') + 10; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group mb-3">
                                <label for="cvv" class="form-label">CVV <span class="text-danger">*</span></label>
                                <input type="text" name="cvv" id="cvv" class="form-control" required
                                       placeholder="123" maxlength="4" pattern="\d{3,4}">
                                <small class="form-text text-muted">3-4 chữ số ở mặt sau thẻ</small>
                            </div>
                        </div>
                    </div>

                    <button type="submit" id="submit-payment" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-lock mr-2"></i>Thanh toán {{ number_format($payment->amount, 0, ',', '.') }} VNĐ
                    </button>
                </form>
            </div>

            <!-- Sidebar: Thẻ test và thông tin bảo mật -->
            <div class="col-md-4">
                <div class="card shadow-sm rounded-lg p-4 h-100">
                    <h5 class="card-title text-dark mb-4">
                        <i class="fas fa-shield-alt text-primary mr-2"></i>
                        Thẻ test & Bảo mật
                    </h5>

                    <!-- Thẻ test -->
                    <div class="test-cards mb-4">
                        <h6 class="text-dark mb-3">Thẻ test để kiểm tra</h6>
                        @foreach($creditCardInfo['test_cards'] as $index => $card)
                            <div class="test-card-item mb-2 p-2 border rounded" style="cursor: pointer;" 
                                 onclick="fillTestCard('{{ $card['number'] }}', '{{ $card['cvv'] }}', '{{ $card['expiry'] }}')">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong class="text-dark">{{ $card['brand'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $card['description'] }}</small>
                                    </div>
                                    <div class="text-right">
                                        <small class="text-muted">{{ $card['number'] }}</small>
                                        <br>
                                        <small class="text-muted">CVV: {{ $card['cvv'] }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Hướng dẫn -->
                    <div class="instructions mb-4">
                        <h6 class="text-dark mb-3">Hướng dẫn</h6>
                        <ul class="list-unstyled">
                            @foreach($creditCardInfo['instructions'] as $instruction)
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success mr-2"></i>
                                    <small>{{ $instruction }}</small>
                                </li>
                            @endforeach
                        </ul>
                    </div>

                    <!-- Thông tin bảo mật -->
                    <div class="security-info">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            <small>{{ $creditCardInfo['security_note'] }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('credit-card-form');
    const submitButton = document.getElementById('submit-payment');

    // Format card number
    const cardNumberInput = document.getElementById('card_number');
    cardNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 16) {
            value = value.substring(0, 16);
        }
        e.target.value = value;
    });

    // Format CVV
    const cvvInput = document.getElementById('cvv');
    cvvInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.length > 4) {
            value = value.substring(0, 4);
        }
        e.target.value = value;
    });

    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Disable submit button
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';

        // Submit form
        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(Object.fromEntries(new FormData(form)))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = data.redirect || '{{ route("tour-booking.show", $tourBooking->id) }}';
            } else {
                alert(data.message || 'Có lỗi xảy ra khi thanh toán');
                resetSubmitButton();
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('Có lỗi xảy ra khi thanh toán!');
            resetSubmitButton();
        });
    });

    function resetSubmitButton() {
        submitButton.disabled = false;
        submitButton.innerHTML = '<i class="fas fa-lock mr-2"></i>Thanh toán {{ number_format($payment->amount, 0, ',', '.') }} VNĐ';
    }
});

// Function to fill test card data
function fillTestCard(number, cvv, expiry) {
    document.getElementById('card_number').value = number;
    document.getElementById('cvv').value = cvv;
    
    const [month, year] = expiry.split('/');
    document.getElementById('expiry_month').value = parseInt(month);
    document.getElementById('expiry_year').value = parseInt('20' + year);
    
    document.getElementById('cardholder_name').value = 'Test User';
    
    alert('Đã điền thông tin thẻ test. Bạn có thể nhấn "Thanh toán" để test.');
}
</script>

@section('styles')
<style>
.test-card-item:hover {
    background-color: #f8f9fa;
    border-color: #007bff !important;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
}

.booking-summary {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

/* Responsive */
@media (max-width: 768px) {
    .btn-lg {
        padding: 10px 20px;
        font-size: 1rem;
    }
}
</style>
@endsection
@endsection
