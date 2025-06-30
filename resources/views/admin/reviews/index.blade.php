@extends('admin.layouts.admin-master')

@section('header', 'Quản lý đánh giá')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Quản lý đánh giá</li>
    </ol>
    
    <!-- Thống kê nhanh -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-bold">{{ $reviews->total() }}</div>
                            <div>Tổng đánh giá</div>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-bold">{{ $reviews->where('status', 'pending')->count() }}</div>
                            <div>Chờ duyệt</div>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-bold">{{ $reviews->where('status', 'approved')->count() }}</div>
                            <div>Đã duyệt</div>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-4 fw-bold">{{ $reviews->where('status', 'rejected')->count() }}</div>
                            <div>Từ chối</div>
                        </div>
                        <div class="fs-1">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-gradient-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-star me-2"></i>
                    <h5 class="mb-0">Danh sách đánh giá</h5>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.reviews.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i> Tạo mới
                    </a>
                    <a href="{{ route('admin.reviews.statistics') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-chart-bar me-1"></i> Thống kê
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <!-- Bộ lọc -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card border-0 bg-light">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-3">
                                <div class="col-md-2">
                                    <label for="status" class="form-label fw-bold text-muted">
                                        <i class="fas fa-filter me-1"></i>Trạng thái
                                    </label>
                                    <select name="status" id="status" class="form-select form-select-sm">
                                        <option value="">Tất cả</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="room_id" class="form-label fw-bold text-muted">
                                        <i class="fas fa-bed me-1"></i>Phòng
                                    </label>
                                    <select name="room_id" id="room_id" class="form-select form-select-sm">
                                        <option value="">Tất cả phòng</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                                {{ $room->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="rating" class="form-label fw-bold text-muted">
                                        <i class="fas fa-star me-1"></i>Số sao
                                    </label>
                                    <select name="rating" id="rating" class="form-select form-select-sm">
                                        <option value="">Tất cả</option>
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} sao</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold text-muted">
                                        <i class="fas fa-search me-1"></i>Tìm kiếm
                                    </label>
                                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Tìm theo tên khách hàng..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <div class="btn-group w-100" role="group">
                                        <button type="submit" class="btn btn-primary btn-sm">
                                            <i class="fas fa-search me-1"></i>Lọc
                                        </button>
                                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-times me-1"></i>Xóa
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bảng dữ liệu -->
            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th class="text-center" style="width: 60px;">#</th>
                            <th style="width: 200px;">Khách hàng</th>
                            <th style="width: 180px;">Phòng</th>
                            <th class="text-center" style="width: 120px;">Đánh giá</th>
                            <th>Bình luận</th>
                            <th class="text-center" style="width: 100px;">Trạng thái</th>
                            <th class="text-center" style="width: 120px;">Ngày tạo</th>
                            <th class="text-center" style="width: 150px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reviews as $review)
                        <tr class="review-row" data-review-id="{{ $review->id }}">
                            <td class="text-center fw-bold text-primary">{{ ($reviews->currentPage() - 1) * $reviews->perPage() + $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                        <span class="text-white fw-bold fs-6">{{ substr($review->user->name, 0, 1) }}</span>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $review->user->name }}</div>
                                        <small class="text-muted d-block">{{ $review->user->email }}</small>
                                        @if($review->is_anonymous)
                                            <span class="badge bg-info bg-opacity-75 fs-6">Ẩn danh</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="me-2">
                                        <i class="fas fa-bed text-primary"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark">{{ $review->room->name }}</div>
                                        <small class="text-muted">Booking: {{ $review->booking->booking_id ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="rating-display">
                                    <div class="text-warning mb-1">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }} fs-5"></i>
                                        @endfor
                                    </div>
                                    <span class="badge bg-warning text-dark fs-6">{{ $review->rating }}/5</span>
                                </div>
                            </td>
                            <td>
                                @if($review->comment)
                                    <div class="comment-preview" style="max-width: 300px;">
                                        <p class="mb-0 text-dark">{{ Str::limit($review->comment, 120) }}</p>
                                        @if(strlen($review->comment) > 120)
                                            <small class="text-primary cursor-pointer" onclick="showFullComment({{ $review->id }})">
                                                Xem thêm...
                                            </small>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">Không có bình luận</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($review->status == 'pending')
                                    <span class="badge bg-warning bg-opacity-75 text-dark fs-6">
                                        <i class="fas fa-clock me-1"></i>Chờ duyệt
                                    </span>
                                @elseif($review->status == 'approved')
                                    <span class="badge bg-success bg-opacity-75 fs-6">
                                        <i class="fas fa-check me-1"></i>Đã duyệt
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-75 fs-6">
                                        <i class="fas fa-times me-1"></i>Từ chối
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="text-dark fw-bold">{{ $review->created_at->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $review->created_at->format('H:i') }}</small>
                            </td>
                            <td class="text-center">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.reviews.show', $review->id) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($review->status == 'pending')
                                        <form action="{{ route('admin.reviews.approve', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn duyệt đánh giá này?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Duyệt">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reviews.reject', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối đánh giá này?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-outline-warning" title="Từ chối">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="empty-state">
                                    <i class="fas fa-star fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">Chưa có đánh giá nào</h5>
                                    <p class="text-muted mb-3">Khách hàng sẽ đánh giá sau khi sử dụng dịch vụ</p>
                                    <a href="{{ route('admin.reviews.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus me-1"></i>Tạo đánh giá đầu tiên
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Phân trang -->
            @if($reviews->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Hiển thị {{ $reviews->firstItem() ?? 0 }} - {{ $reviews->lastItem() ?? 0 }} 
                    trong tổng số {{ $reviews->total() }} đánh giá
                </div>
                <div>
                    {{ $reviews->appends(request()->query())->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal hiển thị bình luận đầy đủ -->
<div class="modal fade" id="commentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bình luận chi tiết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="fullComment"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.avatar-sm {
    transition: transform 0.2s ease;
}

.avatar-sm:hover {
    transform: scale(1.1);
}

.review-row {
    transition: all 0.2s ease;
}

.review-row:hover {
    background-color: rgba(0, 123, 255, 0.05) !important;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.rating-display {
    transition: transform 0.2s ease;
}

.rating-display:hover {
    transform: scale(1.05);
}

.comment-preview {
    line-height: 1.5;
}

.cursor-pointer {
    cursor: pointer;
}

.empty-state {
    padding: 2rem;
}

.btn-group .btn {
    border-radius: 0.375rem !important;
    margin: 0 1px;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem !important;
    border-bottom-left-radius: 0.375rem !important;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem !important;
    border-bottom-right-radius: 0.375rem !important;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.875rem;
    letter-spacing: 0.5px;
}

.table td {
    vertical-align: middle;
    padding: 1rem 0.75rem;
}

.badge {
    font-weight: 500;
    padding: 0.5em 0.75em;
}

.form-select-sm, .form-control-sm {
    font-size: 0.875rem;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    border-bottom: none;
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.875rem;
    }
    
    .btn-group .btn {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
    
    .comment-preview {
        max-width: 150px !important;
    }
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto submit form khi thay đổi filter
    $('select[name="status"], select[name="room_id"], select[name="rating"]').change(function() {
        $(this).closest('form').submit();
    });

    // Hiệu ứng hover cho các nút
    $('.btn-group .btn').hover(
        function() {
            $(this).addClass('shadow-sm');
        },
        function() {
            $(this).removeClass('shadow-sm');
        }
    );

    // Hiệu ứng loading khi submit form
    $('form').submit(function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i>Đang xử lý...');
    });
});

function showFullComment(reviewId) {
    // Lấy bình luận đầy đủ từ data attribute hoặc AJAX
    const row = document.querySelector(`[data-review-id="${reviewId}"]`);
    const commentPreview = row.querySelector('.comment-preview p');
    const fullComment = commentPreview.textContent;
    
    document.getElementById('fullComment').innerHTML = `
        <div class="p-3 bg-light rounded">
            <p class="mb-0">${fullComment}</p>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('commentModal')).show();
}
</script>
@endsection 