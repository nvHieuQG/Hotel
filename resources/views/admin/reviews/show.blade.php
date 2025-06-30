@extends('admin.layouts.admin-master')
@section('header', 'Chi tiết đánh giá')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">Quản lý đánh giá</a></li>
        <li class="breadcrumb-item active">Chi tiết đánh giá</li>
    </ol>
    <div class="card mb-4">
        <div class="card-header bg-gradient-primary text-white">
            <i class="fas fa-star me-2"></i> Chi tiết đánh giá
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5 class="fw-bold">Khách hàng</h5>
                    <div class="d-flex align-items-center mb-2">
                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 48px; height: 48px;">
                            <span class="text-white fw-bold fs-4">{{ substr($review->user->name, 0, 1) }}</span>
                        </div>
                        <div>
                            <div class="fw-bold text-dark fs-5">{{ $review->user->name }}</div>
                            <div class="text-muted">{{ $review->user->email }}</div>
                            @if($review->is_anonymous)
                                <span class="badge bg-info">Ẩn danh</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-bold">Phòng</h5>
                    <div class="fw-bold text-dark">{{ $review->room->name }}</div>
                    <div class="text-muted">Booking: {{ $review->booking->booking_id ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <h5 class="fw-bold">Điểm đánh giá</h5>
                    <div class="text-warning fs-4 mb-2">
                        @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                        @endfor
                        <span class="ms-2 badge bg-warning text-dark">{{ $review->rating }}/5</span>
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="fw-bold">Trạng thái</h5>
                    @if($review->status == 'pending')
                        <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>Chờ duyệt</span>
                    @elseif($review->status == 'approved')
                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Đã duyệt</span>
                    @else
                        <span class="badge bg-danger"><i class="fas fa-times me-1"></i>Từ chối</span>
                    @endif
                </div>
            </div>
            <div class="mb-3">
                <h5 class="fw-bold">Bình luận</h5>
                @if($review->comment)
                    <div class="p-3 bg-light rounded border">
                        {{ $review->comment }}
                    </div>
                @else
                    <span class="text-muted fst-italic">Không có bình luận</span>
                @endif
            </div>
            <div class="mb-3">
                <h5 class="fw-bold">Ngày tạo</h5>
                <div>{{ $review->created_at->format('d/m/Y H:i') }}</div>
            </div>
            <div class="mt-4">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại danh sách
                </a>
                <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Xóa đánh giá
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Các mã JavaScript tùy chỉnh ở đây
    });
</script>
@endsection
