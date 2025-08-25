@extends('client.layouts.master')

@section('title', 'Chuyển khoản ngân hàng - Tour Booking')

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
                        <span>Chuyển khoản ngân hàng</span>
                    </p>
                    <h3 class="mb-4 bread">Chuyển khoản ngân hàng</h3>
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
                                <p><strong>Số tiền:</strong> <span class="text-primary font-weight-bold">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</span></p>
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

                <!-- Thông tin tài khoản ngân hàng -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-university"></i> Thông tin tài khoản ngân hàng</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Ngân hàng:</strong> {{ $bankInfo['bank_name'] }}</p>
                                <p class="mb-1"><strong>Số tài khoản:</strong> {{ $bankInfo['account_number'] }}</p>
                                <p class="mb-1"><strong>Chủ tài khoản:</strong> {{ $bankInfo['account_holder'] }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Chi nhánh:</strong> {{ $bankInfo['branch'] }}</p>
                                <p class="mb-1"><strong>Swift code:</strong> {{ $bankInfo['swift_code'] }}</p>
                                <p class="mb-1"><strong>Nội dung:</strong> {{ $tourBooking->booking_id }}</p>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="copyBankInfo()">
                                <i class="fas fa-copy"></i> Sao chép thông tin
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hướng dẫn chuyển khoản -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Hướng dẫn chuyển khoản</h6>
                    </div>
                    <div class="card-body">
                        <ol class="mb-0">
                            @foreach($bankInfo['instructions'] as $instruction)
                                <li>{{ $instruction }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>

                <!-- Nút quay lại -->
                <div class="text-center mt-4">
                    <a href="{{ route('tour-booking.payment', $tourBooking->booking_id) }}" class="btn btn-secondary btn-lg px-5">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>

                <!-- Lưu ý -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Lưu ý quan trọng</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $bankInfo['note'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>

function copyBankInfo() {
    const bankInfo = `Ngân hàng: {{ $bankInfo['bank_name'] }}\nSố tài khoản: {{ $bankInfo['account_number'] }}\nChủ tài khoản: {{ $bankInfo['account_holder'] }}\nNội dung: {{ $tourBooking->booking_id }}`;
    
    if (navigator.clipboard) {
        navigator.clipboard.writeText(bankInfo).then(() => {
            alert('Đã sao chép thông tin ngân hàng vào clipboard!');
        });
    } else {
        // Fallback cho các trình duyệt cũ
        const textArea = document.createElement('textarea');
        textArea.value = bankInfo;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        alert('Đã sao chép thông tin ngân hàng vào clipboard!');
    }
}
</script>

<style>
.form-control {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    padding: 0.75rem;
    font-size: 1rem;
}

.form-control:focus {
    border-color: #C9A888;
    box-shadow: 0 0 0 0.2rem rgba(201, 168, 136, 0.25);
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection
