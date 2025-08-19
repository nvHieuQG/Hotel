@extends('client.layouts.master')

@section('title', 'Mã khuyến mại của tôi')

@section('content')
<div class="hero-wrap" style="background-image: url('client/images/bg_1.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
            <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <p class="breadcrumbs mb-2">
                        <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                        <span class="mr-2"><a href="{{ route('user.profile') }}">Hồ sơ</a></span>
                        <span>Mã khuyến mại</span>
                    </p>
                    <h1 class="mb-4 bread">Mã khuyến mại của tôi</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section bg-light">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <!-- Thông báo -->
                @if (session('success'))
                    <div class="alert alert-success d-flex align-items-center justify-content-center mb-4" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger d-flex align-items-center justify-content-center mb-4" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <div class="bg-white p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Lịch sử sử dụng mã khuyến mại</h3>
                        <div>
                            <a href="{{ route('user.bookings') }}" class="btn btn-outline-primary">
                                <i class="fas fa-calendar-check"></i> Xem đặt phòng
                            </a>
                        </div>
                    </div>
                    
                    @php
                        $bookingsWithPromotions = auth()->user()->bookings()
                            ->whereNotNull('promotion_id')
                            ->with(['promotion', 'room.roomType'])
                            ->orderBy('created_at', 'desc')
                            ->get();
                    @endphp
                    
                    @if ($bookingsWithPromotions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã đặt phòng</th>
                                        <th>Loại phòng</th>
                                        <th>Mã khuyến mại</th>
                                        <th>Giảm giá</th>
                                        <th>Giá gốc</th>
                                        <th>Giá cuối</th>
                                        <th>Ngày sử dụng</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bookingsWithPromotions as $booking)
                                        <tr>
                                            <td><a href="{{ route('user.bookings.detail.partial', $booking->id) }}" class="text-primary">{{ $booking->booking_id }}</a></td>
                                            <td>{{ $booking->room->roomType->name }}</td>
                                            <td>
                                                <div class="badge bg-warning text-dark">
                                                    <i class="fas fa-gift"></i> {{ $booking->promotion->code }}
                                                </div>
                                                <div class="small text-muted mt-1">{{ $booking->promotion->title }}</div>
                                            </td>
                                            <td class="text-success font-weight-bold">
                                                -{{ number_format($booking->promotion_discount) }}đ
                                            </td>
                                            <td class="text-decoration-line-through text-muted">
                                                {{ number_format($booking->price) }}đ
                                            </td>
                                            <td class="text-danger font-weight-bold">
                                                {{ number_format($booking->final_price) }}đ
                                            </td>
                                            <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                <span class="badge badge-{{ $booking->status == 'pending' ? 'warning' : ($booking->status == 'confirmed' ? 'success' : ($booking->status == 'cancelled' ? 'danger' : 'primary')) }}">
                                                    {{ $booking->status_text }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="text-muted">
                                <i class="fas fa-gift fa-3x mb-3"></i>
                                <h5>Bạn chưa sử dụng mã khuyến mại nào</h5>
                                <p class="mb-3">Hãy đặt phòng và sử dụng mã khuyến mại để tiết kiệm chi phí</p>
                                <a href="{{ route('rooms') }}" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Tìm phòng ngay
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Thống kê khuyến mại -->
                    @if ($bookingsWithPromotions->count() > 0)
                        <div class="row mt-4">
                            <div class="col-md-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-gift fa-2x text-primary mb-2"></i>
                                        <h4 class="text-primary">{{ $bookingsWithPromotions->count() }}</h4>
                                        <p class="text-muted mb-0">Lần sử dụng</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-money-bill-wave fa-2x text-success mb-2"></i>
                                        <h4 class="text-success">{{ number_format($bookingsWithPromotions->sum('promotion_discount')) }}đ</h4>
                                        <p class="text-muted mb-0">Tổng tiết kiệm</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-percentage fa-2x text-warning mb-2"></i>
                                        <h4 class="text-warning">{{ number_format($bookingsWithPromotions->avg('promotion_discount') / $bookingsWithPromotions->avg('price') * 100, 1) }}%</h4>
                                        <p class="text-muted mb-0">Giảm giá trung bình</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
