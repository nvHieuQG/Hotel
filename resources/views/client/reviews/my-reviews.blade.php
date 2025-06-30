@extends('client.layouts.master')

@section('title', 'Đánh giá của tôi')

@section('content')
<div class="hero-wrap" style="background-image: url('client/images/bg_1.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
            <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <p class="breadcrumbs mb-2">
                        <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                        <span class="mr-2"><a href="{{ route('my-bookings') }}">Đặt phòng của tôi</a></span>
                        <span>Đánh giá của tôi</span>
                    </p>
                    <h1 class="mb-4 bread">Đánh giá của tôi</h1>
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

                <div class="bg-white p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3>Đánh giá của tôi</h3>
                        <div>
                            <a href="{{ route('reviews.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-plus"></i> Viết đánh giá mới
                            </a>
                            <a href="{{ route('my-bookings') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-list"></i> Tất cả đặt phòng
                            </a>
                        </div>
                    </div>

                    @if ($reviews->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Phòng</th>
                                        <th>Điểm đánh giá</th>
                                        <th>Bình luận</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày đánh giá</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($reviews as $review)
                                        <tr>
                                            <td>
                                                <div>
                                                    <strong>{{ $review->room->name }}</strong>
                                                    @if($review->booking)
                                                        <br>
                                                        <small class="text-muted">Mã: {{ $review->booking->booking_id }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-warning">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                                    @endfor
                                                    <span class="ml-2">{{ $review->rating }}/5</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($review->comment)
                                                    <div class="comment-preview">
                                                        {{ Str::limit($review->comment, 100) }}
                                                        @if(strlen($review->comment) > 100)
                                                            <button type="button" class="btn btn-link btn-sm p-0 ml-1" 
                                                                    data-toggle="modal" data-target="#commentModal{{ $review->id }}">
                                                                Xem thêm
                                                            </button>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">Không có bình luận</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }}">
                                                    {{ $review->status_text }}
                                                </span>
                                                @if($review->is_anonymous)
                                                    <br>
                                                    <small class="text-muted">Ẩn danh</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $review->created_at->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                @if ($review->canBeEdited())
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('reviews.edit', $review->id) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit"></i> Sửa
                                                        </a>
                                                        <form action="{{ route('reviews.destroy', $review->id) }}" method="post" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-trash"></i> Xóa
                                                            </button>
                                                        </form>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Không thể chỉnh sửa</span>
                                                @endif
                                            </td>
                                        </tr>

                                        <!-- Modal cho bình luận dài -->
                                        @if($review->comment && strlen($review->comment) > 100)
                                            <div class="modal fade" id="commentModal{{ $review->id }}" tabindex="-1" role="dialog">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">Bình luận đầy đủ</h5>
                                                            <button type="button" class="close" data-dismiss="modal">
                                                                <span>&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <p>{{ $review->comment }}</p>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="d-flex justify-content-center mt-4">
                            {{ $reviews->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="fas fa-star-o fa-4x text-muted"></i>
                            </div>
                            <h4 class="text-muted mb-3">Bạn chưa có đánh giá nào</h4>
                            <p class="text-muted mb-4">
                                Hãy đặt phòng và sử dụng dịch vụ để có thể đánh giá chúng tôi.
                            </p>
                            <div>
                                <a href="{{ route('reviews.index') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Viết đánh giá mới
                                </a>
                                <a href="{{ route('booking') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-calendar-plus"></i> Đặt phòng ngay
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.comment-preview {
    max-width: 300px;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}

.btn-group .btn {
    margin-right: 5px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}
</style>
@endsection 