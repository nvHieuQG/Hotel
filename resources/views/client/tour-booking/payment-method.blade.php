@extends('client.layouts.master')

@section('title', 'Chọn phương thức thanh toán - Tour Booking')

@section('content')
<section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate pb-5 text-center">
                <h1 class="mb-3 bread">Chọn phương thức thanh toán</h1>
                <p class="breadcrumbs">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ <i class="ion-ios-arrow-forward"></i></a></span>
                    <span class="mr-2"><a href="{{ route('tour-booking.index') }}">Tour Bookings <i class="ion-ios-arrow-forward"></i></a></span>
                    <span>Thanh toán</span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Thông tin đặt phòng -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #C9A888; color: white;">
                        <h4>Thông tin đặt phòng Tour</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mã đặt phòng:</strong> {{ $tourBooking->booking_id }}</p>
                                <p><strong>Tên Tour:</strong> {{ $tourBooking->tour_name }}</p>
                                <p><strong>Tổng số khách:</strong> {{ $tourBooking->total_guests }} người</p>
                                <p><strong>Tổng số phòng:</strong> {{ $tourBooking->total_rooms }} phòng</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Check-in:</strong> {{ $tourBooking->check_in_date->format('d/m/Y') }}</p>
                                <p><strong>Check-out:</strong> {{ $tourBooking->check_out_date->format('d/m/Y') }}</p>
                                <p><strong>Số đêm:</strong> {{ $tourBooking->total_nights }} đêm</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tóm tắt giá -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #C9A888; color: white;">
                        <h5>Tóm tắt giá</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Tiền phòng:</strong></td>
                                            <td class="text-right">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</td>
                                        </tr>
                                        @if($tourBooking->promotion_discount > 0)
                                            <tr class="text-success">
                                                <td><strong>Giảm giá:</strong></td>
                                                <td class="text-right">-{{ number_format($tourBooking->promotion_discount, 0, ',', '.') }} VNĐ</td>
                                            </tr>
                                            <tr class="table-info">
                                                <td><strong>Tổng cộng:</strong></td>
                                                <td class="text-right"><strong>{{ number_format($tourBooking->final_price, 0, ',', '.') }} VNĐ</strong></td>
                                            </tr>
                                        @else
                                            <tr class="table-info">
                                                <td><strong>Tổng cộng:</strong></td>
                                                <td class="text-right"><strong>{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</strong></td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <h6><i class="fas fa-info-circle"></i> Thông tin thanh toán</h6>
                                    <p class="mb-1"><strong>Mã đặt phòng:</strong> {{ $tourBooking->booking_id }}</p>
                                    @if($tourBooking->promotion_code)
                                        <p class="mb-1"><strong>Mã giảm giá:</strong> {{ $tourBooking->promotion_code }}</p>
                                    @endif
                                    <p class="mb-0"><strong>Ngày đặt:</strong> {{ $tourBooking->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chọn phương thức thanh toán -->
                <div class="card">
                    <div class="card-header" style="background-color: #C9A888; color: white;">
                        <h4>Chọn phương thức thanh toán</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <!-- Thẻ tín dụng -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border-primary">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-3">
                                            <i class="fas fa-credit-card fa-3x text-primary"></i>
                                        </div>
                                        <h5 class="card-title">Thẻ tín dụng/ghi nợ</h5>
                                        <p class="card-text text-muted">Thanh toán an toàn qua cổng thanh toán</p>
                                        <a href="{{ route('tour-booking.credit-card', $tourBooking->id) }}" class="btn btn-primary btn-lg">
                                            <i class="fas fa-credit-card"></i> Thanh toán bằng thẻ
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Chuyển khoản -->
                            <div class="col-md-6 mb-3">
                                <div class="card h-100 border-success">
                                    <div class="card-body text-center">
                                        <div class="payment-icon mb-3">
                                            <i class="fas fa-university fa-3x text-success"></i>
                                        </div>
                                        <h5 class="card-title">Chuyển khoản ngân hàng</h5>
                                        <p class="card-text text-muted">Chuyển khoản trực tiếp đến tài khoản ngân hàng</p>
                                        <a href="{{ route('tour-booking.bank-transfer', $tourBooking->id) }}" class="btn btn-success btn-lg">
                                            <i class="fas fa-university"></i> Chuyển khoản ngân hàng
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lưu ý -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5><i class="fas fa-info-circle"></i> Lưu ý quan trọng</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Vui lòng chọn phương thức thanh toán phù hợp</li>
                            <li>Đặt phòng sẽ được xác nhận sau khi thanh toán thành công</li>
                            <li>Bạn có thể hủy đặt phòng trong vòng 24 giờ trước ngày check-in</li>
                            <li>Liên hệ hotline nếu cần hỗ trợ: <strong>1900-xxxx</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.card {
    transition: all 0.3s ease;
    cursor: pointer;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.payment-icon {
    margin-bottom: 1rem;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

.table-borderless td {
    border: none;
    padding: 0.5rem 0;
}
</style>
@endsection
