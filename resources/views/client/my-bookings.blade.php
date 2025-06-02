@extends('client.layouts.master')

@section('title', 'Đặt Phòng Của Tôi')

@section('content')
<div class="hero-wrap" style="background-image: url('client/images/bg_1.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
            <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <p class="breadcrumbs mb-2"><span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span> <span>Đặt Phòng Của Tôi</span></p>
                    <h1 class="mb-4 bread">Đặt Phòng Của Tôi</h1>
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
                    <h3 class="mb-4">Lịch Sử Đặt Phòng</h3>
                    
                    @if ($bookings->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Mã đặt phòng</th>
                                        <th>Loại phòng</th>
                                        <th>Ngày nhận</th>
                                        <th>Ngày trả</th>
                                        <th>Tổng tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($bookings as $booking)
                                        <tr>
                                            <td>{{ $booking->booking_id }}</td>
                                            <td>{{ $booking->room->name }}</td>
                                            <td>{{ $booking->check_in_date->format('d/m/Y') }}</td>
                                            <td>{{ $booking->check_out_date->format('d/m/Y') }}</td>
                                            <td>{{ number_format($booking->price) }}đ</td>
                                            <td>
                                                @if ($booking->status == 'pending')
                                                    <span class="badge badge-warning">Chờ xác nhận</span>
                                                @elseif ($booking->status == 'confirmed')
                                                    <span class="badge badge-success">Đã xác nhận</span>
                                                @elseif ($booking->status == 'cancelled')
                                                    <span class="badge badge-danger">Đã hủy</span>
                                                @elseif ($booking->status == 'completed')
                                                    <span class="badge badge-primary">Hoàn thành</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($booking->status == 'pending')
                                                    <form action="{{ route('booking.cancel', $booking->id) }}" method="post" onsubmit="return confirm('Bạn có chắc chắn muốn hủy đặt phòng này?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-danger">Hủy đặt phòng</button>
                                                    </form>
                                                @else
                                                    <button class="btn btn-sm btn-secondary" disabled>{{ $booking->status == 'cancelled' ? 'Đã hủy' : 'Không thể hủy' }}</button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p>Bạn chưa có đặt phòng nào.</p>
                            <a href="{{ route('booking') }}" class="btn btn-primary">Đặt phòng ngay</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 