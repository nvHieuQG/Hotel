@extends('client.layouts.master')

@section('title', 'Danh sách Tour Bookings')

@section('content')
<section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate pb-5 text-center">
                <h1 class="mb-3 bread">Tour Bookings của tôi</h1>
                <p class="breadcrumbs">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ <i class="ion-ios-arrow-forward"></i></a></span>
                    <span>Tour Bookings</span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Danh sách Tour Bookings</h3>
                    <a href="{{ route('tour-booking.search') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Đặt Tour mới
                    </a>
                </div>

                @if($tourBookings->count() > 0)
                    <div class="row">
                        @foreach($tourBookings as $tourBooking)
                            <div class="col-md-6 mb-4">
                                <div class="card h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">{{ $tourBooking->tour_name }}</h5>
                                        <span class="badge badge-{{ $tourBooking->status === 'pending' ? 'warning' : ($tourBooking->status === 'confirmed' ? 'success' : ($tourBooking->status === 'cancelled' ? 'danger' : 'info')) }}">
                                            {{ $tourBooking->status_text }}
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><strong>Mã đặt phòng:</strong><br>{{ $tourBooking->booking_id }}</p>
                                                <p><strong>Tổng số khách:</strong><br>{{ $tourBooking->total_guests }} người</p>
                                                <p><strong>Tổng số phòng:</strong><br>{{ $tourBooking->total_rooms }} phòng</p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><strong>Check-in:</strong><br>{{ $tourBooking->check_in_date->format('d/m/Y') }}</p>
                                                <p><strong>Check-out:</strong><br>{{ $tourBooking->check_out_date->format('d/m/Y') }}</p>
                                                <p><strong>Tổng tiền:</strong><br><span class="text-primary font-weight-bold">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</span></p>
                                            </div>
                                        </div>
                                        
                                        @if($tourBooking->special_requests)
                                            <div class="mt-3">
                                                <strong>Yêu cầu đặc biệt:</strong>
                                                <p class="text-muted mb-0">{{ $tourBooking->special_requests }}</p>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-footer">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">Đặt lúc: {{ $tourBooking->created_at->format('d/m/Y H:i') }}</small>
                                            <div>
                                                <a href="{{ route('tour-booking.show', $tourBooking->id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Chi tiết
                                                </a>
                                                @if($tourBooking->status === 'pending')
                                                    <a href="{{ route('tour-booking.payment', $tourBooking->booking_id) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-credit-card"></i> Thanh toán
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                        <h5>Chưa có Tour Booking nào</h5>
                        <p class="text-muted">Bạn chưa có tour booking nào. Hãy đặt tour đầu tiên!</p>
                        <a href="{{ route('tour-booking.search') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Đặt Tour ngay
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
.card {
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.badge {
    font-size: 0.8em;
}
</style>
@endsection 