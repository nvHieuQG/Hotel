@extends('admin.layouts.master')

@section('title', 'Quản lý đánh giá')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-star text-warning"></i> Quản lý đánh giá
                        </h3>
                        <div>
                            <a href="{{ route('admin.reviews.statistics') }}" class="btn btn-info">
                                <i class="fas fa-chart-bar"></i> Thống kê
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Filter Section -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-3">
                                <div class="col-md-2">
                                    <select name="status" class="form-control">
                                        <option value="">Tất cả trạng thái</option>
                                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="room_id" class="form-control">
                                        <option value="">Tất cả phòng</option>
                                        @foreach($rooms as $room)
                                            <option value="{{ $room->id }}" {{ request('room_id') == $room->id ? 'selected' : '' }}>
                                                {{ $room->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="rating" class="form-control">
                                        <option value="">Tất cả rating</option>
                                        @for($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>
                                                {{ $i }} sao
                                            </option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Lọc
                                    </button>
                                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Xóa lọc
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Reviews Table -->
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Khách hàng</th>
                                    <th>Phòng</th>
                                    <th>Rating</th>
                                    <th>Bình luận</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày tạo</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $review)
                                    <tr>
                                        <td>{{ $review->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    <span class="text-white fw-bold">{{ substr($review->user->name, 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold">{{ $review->user->name }}</div>
                                                    <small class="text-muted">{{ $review->user->email }}</small>
                                                    @if($review->is_anonymous)
                                                        <span class="badge badge-info">Ẩn danh</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <div class="fw-bold">{{ $review->room->name }}</div>
                                                <small class="text-muted">Booking: {{ $review->booking->booking_id }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="text-warning">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                                @endfor
                                            </div>
                                            <small class="text-muted">{{ $review->rating }}/5</small>
                                        </td>
                                        <td>
                                            @if($review->comment)
                                                <div class="comment-preview" style="max-width: 200px;">
                                                    {{ Str::limit($review->comment, 100) }}
                                                </div>
                                            @else
                                                <span class="text-muted">Không có bình luận</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($review->status == 'pending')
                                                <span class="badge badge-warning">Chờ duyệt</span>
                                            @elseif($review->status == 'approved')
                                                <span class="badge badge-success">Đã duyệt</span>
                                            @else
                                                <span class="badge badge-danger">Từ chối</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>{{ $review->created_at->format('d/m/Y') }}</div>
                                            <small class="text-muted">{{ $review->created_at->format('H:i') }}</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.reviews.show', $review->id) }}" 
                                                   class="btn btn-sm btn-info" 
                                                   title="Xem chi tiết">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                
                                                @if($review->status == 'pending')
                                                    <form action="{{ route('admin.reviews.approve', $review->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn duyệt đánh giá này?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-success" title="Duyệt">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    
                                                    <form action="{{ route('admin.reviews.reject', $review->id) }}" 
                                                          method="POST" 
                                                          class="d-inline"
                                                          onsubmit="return confirm('Bạn có chắc chắn muốn từ chối đánh giá này?')">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="btn btn-sm btn-warning" title="Từ chối">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <form action="{{ route('admin.reviews.destroy', $review->id) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <i class="fas fa-star fa-3x text-muted mb-3"></i>
                                            <h5>Chưa có đánh giá nào</h5>
                                            <p class="text-muted">Khách hàng sẽ đánh giá sau khi sử dụng dịch vụ</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($reviews->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $reviews->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

.comment-preview {
    word-wrap: break-word;
}

.badge {
    font-size: 0.75rem;
}

.btn-group .btn {
    margin-right: 2px;
}

.table th {
    font-weight: 600;
    background-color: #343a40;
    color: white;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,.075);
}
</style>
@endsection 