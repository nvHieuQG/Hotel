@extends('client.layouts.master')

@section('title', 'Đánh giá phòng')

@section('content')
<div class="hero-wrap" style="background-image: url('client/images/bg_1.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
            <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <p class="breadcrumbs mb-2">
                        <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                        @auth
                            <span class="mr-2"><a href="{{ route('my-bookings') }}">Đặt phòng của tôi</a></span>
                        @else
                            <span class="mr-2"><a href="{{ route('login') }}">Đăng nhập</a></span>
                        @endauth
                        <span>Đánh giá phòng</span>
                    </p>
                    <h1 class="mb-4 bread">Đánh giá phòng</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Đánh giá phòng</h3>
                        <div>
                            @auth
                                <a href="{{ route('reviews.my-reviews') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-star"></i> Đánh giá của tôi
                                </a>
                                <a href="{{ route('my-bookings') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-list"></i> Tất cả đặt phòng
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-sign-in-alt"></i> Đăng nhập để đánh giá
                                </a>
                            @endauth
                        </div>
                    </div>

                    @auth
                        @if ($bookings->count() > 0)
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                Dưới đây là danh sách các đặt phòng đã hoàn thành mà bạn chưa đánh giá. 
                                Hãy chia sẻ trải nghiệm của bạn để giúp chúng tôi cải thiện dịch vụ.
                            </div>

                            <div class="row">
                                @foreach ($bookings as $booking)
                                    <div class="col-md-6 col-lg-4 mb-4">
                                        <div class="card h-100 shadow-sm">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-3">
                                                    <h5 class="card-title mb-0">{{ $booking->room->name }}</h5>
                                                    <span class="badge badge-success">Đã hoàn thành</span>
                                                </div>
                                                
                                                <div class="booking-info mb-3">
                                                    <p class="mb-1"><strong>Mã đặt phòng:</strong> {{ $booking->booking_id }}</p>
                                                    <p class="mb-1"><strong>Ngày nhận:</strong> {{ $booking->check_in_date->format('d/m/Y') }}</p>
                                                    <p class="mb-1"><strong>Ngày trả:</strong> {{ $booking->check_out_date->format('d/m/Y') }}</p>
                                                    <p class="mb-1"><strong>Tổng tiền:</strong> {{ number_format($booking->price) }}đ</p>
                                                </div>

                                                <div class="room-info mb-3">
                                                    <p class="mb-1"><strong>Loại phòng:</strong> {{ $booking->room->roomType->name ?? 'N/A' }}</p>
                                                    @if($booking->room->capacity)
                                                        <p class="mb-1"><strong>Sức chứa:</strong> {{ $booking->room->capacity }} người</p>
                                                    @endif
                                                </div>

                                                <div class="text-center">
                                                    <a href="{{ route('reviews.create', $booking->id) }}" class="btn btn-primary btn-block">
                                                        <i class="fas fa-star"></i> Viết đánh giá
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <i class="fas fa-star-o fa-4x text-muted"></i>
                                </div>
                                <h4 class="text-muted mb-3">Chưa có đặt phòng nào cần đánh giá</h4>
                                <p class="text-muted mb-4">
                                    Bạn chưa có đặt phòng nào đã hoàn thành và chưa đánh giá. 
                                    Hãy đặt phòng và sử dụng dịch vụ để có thể đánh giá chúng tôi.
                                </p>
                                <div>
                                    <a href="{{ route('booking') }}" class="btn btn-primary">
                                        <i class="fas fa-calendar-plus"></i> Đặt phòng ngay
                                    </a>
                                    <a href="{{ route('my-bookings') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-list"></i> Xem tất cả đặt phòng
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-user-lock fa-4x text-muted"></i>
                            </div>
                            <h4 class="text-muted mb-3">Vui lòng đăng nhập để đánh giá</h4>
                            <p class="text-muted mb-4">
                                Bạn cần đăng nhập để có thể đánh giá các phòng đã sử dụng. 
                                Hãy đăng nhập hoặc đăng ký tài khoản để sử dụng tính năng này.
                            </p>
                            <div>
                                <a href="{{ route('login') }}" class="btn btn-primary">
                                    <i class="fas fa-sign-in-alt"></i> Đăng nhập
                                </a>
                                <a href="{{ route('register') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-user-plus"></i> Đăng ký
                                </a>
                            </div>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.card {
    transition: transform 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-5px);
}

.booking-info p {
    font-size: 0.9rem;
    color: #666;
}

.room-info p {
    font-size: 0.9rem;
    color: #666;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.btn-block {
    width: 100%;
}
</style>
@endsection 