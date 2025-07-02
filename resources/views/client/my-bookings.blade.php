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
                        <h3>Lịch Sử Đặt Phòng</h3>
                        <div>
                            <a href="{{ route('reviews.index') }}" class="btn btn-outline-success">
                                <i class="fas fa-star"></i> Viết đánh giá
                            </a>
                            <a href="{{ route('reviews.my-reviews') }}" class="btn btn-outline-primary">
                                <i class="fas fa-list"></i> Đánh giá của tôi
                            </a>
                        </div>
                    </div>
                    
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
                                        <th>Đánh giá</th>
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
                                                <span class="badge badge-{{ $booking->status == 'pending' ? 'warning' : ($booking->status == 'confirmed' ? 'success' : ($booking->status == 'cancelled' ? 'danger' : 'primary')) }}">
                                                    {{ $booking->status_text }}
                                                </span>
                                            </td>
                                            <td>
                                                @if ($booking->hasReview())
                                                    @php $review = $booking->review @endphp
                                                    <div class="d-flex align-items-center">
                                                        <div class="text-warning mr-2">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                                            @endfor
                                                        </div>
                                                        <span class="badge badge-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }}">
                                                            {{ $review->status_text }}
                                                        </span>
                                                    </div>
                                                    @if ($review->canBeEdited())
                                                        <div class="mt-1">
                                                            <a href="{{ route('reviews.edit', $review->id) }}" class="btn btn-sm btn-outline-primary">Sửa</a>
                                                            <form action="{{ route('reviews.destroy', $review->id) }}" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-outline-danger">Xóa</button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                @elseif ($booking->canBeReviewed())
                                                    <a href="{{ route('reviews.create', $booking->id) }}" class="btn btn-sm btn-success">
                                                        <i class="fas fa-star"></i> Đánh giá ngay
                                                    </a>
                                                @else
                                                    <span class="text-muted">Chưa thể đánh giá</span>
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
                        
                        {{-- <div class="d-flex justify-content-center mt-4">
                            {{ $bookings->links() }}
                        </div> --}}
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