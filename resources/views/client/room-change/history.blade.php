@extends('client.layouts.master')

@section('title', 'Lịch sử đổi phòng')

@section('content')
<div class="hero-wrap" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center" data-scrollax-parent="true">
            <div class="col-md-9 ftco-animate text-center" data-scrollax=" properties: { translateY: '70%' }">
                <p class="breadcrumbs" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                    <span class="mr-2"><a href="{{ route('user.bookings') }}">Đặt phòng</a></span>
                    <span>Lịch sử đổi phòng</span>
                </p>
                <h1 class="mb-3 bread" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">Lịch sử đổi phòng</h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">Lịch sử đổi phòng - Booking #{{ $booking->booking_id }}</h4>
                        <a href="{{ route('booking.detail', $booking->id) }}" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                    <div class="card-body">
                        @if($roomChanges->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Ngày yêu cầu</th>
                                            <th>Phòng cũ</th>
                                            <th>Phòng mới</th>
                                            <th>Lý do</th>
                                            <th>Chênh lệch giá</th>
                                            <th>Trạng thái</th>
                                            <th>Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roomChanges as $roomChange)
                                            <tr>
                                                <td>{{ $roomChange->created_at->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <strong>{{ $roomChange->oldRoom->room_number }}</strong><br>
                                                    <small class="text-muted">{{ $roomChange->oldRoom->roomType->name }}</small>
                                                </td>
                                                <td>
                                                    <strong>{{ $roomChange->newRoom->room_number }}</strong><br>
                                                    <small class="text-muted">{{ $roomChange->newRoom->roomType->name }}</small>
                                                </td>
                                                <td>{{ $roomChange->reason ?: 'Không có' }}</td>
                                                <td>
                                                    @if($roomChange->price_difference > 0)
                                                        <span class="text-danger">+{{ number_format($roomChange->price_difference, 0, ',', '.') }} VNĐ</span>
                                                    @elseif($roomChange->price_difference < 0)
                                                        <span class="text-success">{{ number_format($roomChange->price_difference, 0, ',', '.') }} VNĐ</span>
                                                    @else
                                                        <span class="text-muted">Không có chênh lệch</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $roomChange->getStatusColor() }}">
                                                        {{ $roomChange->getStatusText() }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($roomChange->customer_note)
                                                        <strong>Ghi chú của bạn:</strong><br>
                                                        <small>{{ $roomChange->customer_note }}</small>
                                                    @endif
                                                    @if($roomChange->admin_note)
                                                        <br><strong>Phản hồi từ khách sạn:</strong><br>
                                                        <small class="text-info">{{ $roomChange->admin_note }}</small>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fa fa-info-circle fa-3x text-muted mb-3"></i>
                                <h5>Chưa có lịch sử đổi phòng</h5>
                                <p class="text-muted">Booking này chưa có yêu cầu đổi phòng nào.</p>
                                <a href="{{ route('room-change.request', $booking->id) }}" class="btn btn-primary">
                                    <i class="fa fa-exchange"></i> Yêu cầu đổi phòng
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 