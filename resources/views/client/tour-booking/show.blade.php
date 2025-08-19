@extends('client.layouts.master')

@section('title', 'Chi tiết Tour Booking')

@section('content')
<section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate pb-5 text-center">
                <h1 class="mb-3 bread">Chi tiết Tour Booking</h1>
                <p class="breadcrumbs">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ <i class="ion-ios-arrow-forward"></i></a></span>
                    <span class="mr-2"><a href="{{ route('tour-booking.index') }}">Tour Bookings <i class="ion-ios-arrow-forward"></i></a></span>
                    <span>Chi tiết</span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Thông tin chính -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>{{ $tourBooking->tour_name }}</h4>
                        <span class="badge badge-{{ $tourBooking->status === 'pending' ? 'warning' : ($tourBooking->status === 'confirmed' ? 'success' : ($tourBooking->status === 'cancelled' ? 'danger' : 'info')) }} badge-lg">
                            {{ $tourBooking->status_text }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mã đặt phòng:</strong> {{ $tourBooking->booking_id }}</p>
                                <p><strong>Tổng số khách:</strong> {{ $tourBooking->total_guests }} người</p>
                                <p><strong>Tổng số phòng:</strong> {{ $tourBooking->total_rooms }} phòng</p>
                                <p><strong>Số đêm:</strong> {{ $tourBooking->total_nights }} đêm</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Check-in:</strong> {{ $tourBooking->check_in_date->format('d/m/Y') }}</p>
                                <p><strong>Check-out:</strong> {{ $tourBooking->check_out_date->format('d/m/Y') }}</p>
                                <p><strong>Tổng tiền:</strong> <span class="text-primary font-weight-bold">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</span></p>
                                <p><strong>Ngày đặt:</strong> {{ $tourBooking->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($tourBooking->special_requests)
                            <div class="mt-3 pt-3 border-top">
                                <strong>Yêu cầu đặc biệt:</strong>
                                <p class="text-muted mb-0">{{ $tourBooking->special_requests }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Chi tiết phòng -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Chi tiết phòng đã đặt</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Loại phòng</th>
                                        <th>Số lượng</th>
                                        <th>Số khách/phòng</th>
                                        <th>Giá/phòng</th>
                                        <th>Tổng tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tourBooking->tourBookingRooms as $tourBookingRoom)
                                        <tr>
                                            <td>
                                                <strong>{{ $tourBookingRoom->roomType->name }}</strong>
                                                <br><small class="text-muted">{{ $tourBookingRoom->roomType->description }}</small>
                                            </td>
                                            <td>{{ $tourBookingRoom->quantity }}</td>
                                            <td>{{ $tourBookingRoom->guests_per_room }}</td>
                                            <td>{{ number_format($tourBookingRoom->price_per_room, 0, ',', '.') }} VNĐ</td>
                                            <td>{{ number_format($tourBookingRoom->total_price, 0, ',', '.') }} VNĐ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-info">
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Tổng tiền:</strong></td>
                                        <td><strong>{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Thông tin thanh toán -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Thông tin thanh toán</h5>
                    </div>
                    <div class="card-body">
                        @if($tourBooking->payments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Ngày thanh toán</th>
                                            <th>Phương thức</th>
                                            <th>Số tiền</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tourBooking->payments as $payment)
                                            <tr>
                                                <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                                <td>{{ $payment->payment_method }}</td>
                                                <td>{{ number_format($payment->amount, 0, ',', '.') }} VNĐ</td>
                                                <td>
                                                    <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                                        {{ $payment->status === 'completed' ? 'Hoàn thành' : ($payment->status === 'pending' ? 'Đang xử lý' : 'Thất bại') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-credit-card fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Chưa có thanh toán nào</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Hành động -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('tour-booking.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                                </a>
                            </div>
                            <div class="col-md-6">
                                @if($tourBooking->status === 'pending')
                                    <a href="{{ route('tour-booking.payment', $tourBooking->booking_id) }}" class="btn btn-success btn-block">
                                        <i class="fas fa-credit-card"></i> Thanh toán ngay
                                    </a>
                                @elseif($tourBooking->status === 'confirmed')
                                    <button class="btn btn-info btn-block" disabled>
                                        <i class="fas fa-check"></i> Đã xác nhận
                                    </button>
                                @elseif($tourBooking->status === 'completed')
                                    <button class="btn btn-success btn-block" disabled>
                                        <i class="fas fa-star"></i> Hoàn thành
                                    </button>
                                @else
                                    <button class="btn btn-danger btn-block" disabled>
                                        <i class="fas fa-times"></i> Đã hủy
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.badge-lg {
    font-size: 1em;
    padding: 0.5em 1em;
}

.table th {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection 