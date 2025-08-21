@extends('admin.layouts.admin-master')

@section('header', 'Quản lý đánh giá loại phòng')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Quản lý đánh giá loại phòng</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <i class="fas fa-star me-1"></i>
                    Danh sách đánh giá loại phòng
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.room-type-reviews.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Tạo đánh giá mới
                    </a>
                    <a href="{{ route('admin.room-type-reviews.statistics') }}" class="btn btn-info btn-sm">
                        <i class="fas fa-chart-bar"></i> Thống kê
                    </a>
                </div>
            </div>
        </div>
                
        <div class="card-body">
            <div class="mb-3">
                <form action="{{ route('admin.room-type-reviews.index') }}" method="GET" class="row g-3">
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="status" class="form-label">Lọc theo trạng thái:</label>
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="room_type" class="form-label">Loại phòng:</label>
                        <select name="room_type" id="room_type" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            @foreach($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}" {{ request('room_type') == $roomType->id ? 'selected' : '' }}>
                                    {{ $roomType->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="rating" class="form-label">Điểm đánh giá:</label>
                        <select name="rating" id="rating" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            @for($i = 1; $i <= 5; $i++)
                                <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} sao</option>
                            @endfor
                        </select>
                    </div>
                </form>
            </div>

            <!-- Desktop Table View -->
            <div class="d-none d-lg-block">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th style="width: 60px;">STT</th>
                                <th style="width: 200px;">Người đánh giá</th>
                                <th style="width: 150px;">Loại phòng</th>
                                <th style="width: 200px;">Điểm</th>
                                <th>Nội dung đánh giá</th>
                                <th style="width: 100px;">Trạng thái</th>
                                <th style="width: 120px;">Ngày tạo</th>
                                <th style="width: 150px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviews as $review)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div>
                                        <strong>{{ $review->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $review->user->email }}</small>
                                        @if($review->is_anonymous)
                                            <br>
                                            <span class="badge bg-secondary">Ẩn danh</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $review->roomType->name }}</strong>
                                    <br>
                                    <small class="text-muted">{{ number_format($review->roomType->price) }}đ/đêm</small>
                                </td>
                                <td>
                                    <div class="text-warning">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                        @endfor
                                    </div>
                                    <small class="text-muted">{{ $review->rating }}/5</small>
                                </td>
                                <td>
                                    @if($review->comment)
                                        <div class="text-truncate" style="max-width: 200px;" title="{{ $review->comment }}">
                                            {{ Str::limit($review->comment, 100) }}
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic">Không có nội dung đánh giá</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }}">
                                        {{ $review->status_text }}
                                    </span>
                                </td>
                                <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.room-type-reviews.show', $review->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($review->status == 'pending')
                                            <form action="{{ route('admin.room-type-reviews.approve', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn duyệt đánh giá này?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('admin.room-type-reviews.reject', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối đánh giá này?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form action="{{ route('admin.room-type-reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tablet View -->
            <div class="d-none d-md-block d-lg-none">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dataTableTablet" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th style="width: 50px;">STT</th>
                                <th style="width: 150px;">Người đánh giá</th>
                                <th style="width: 120px;">Loại phòng</th>
                                <th style="width: 120px;">Điểm</th>
                                <th style="width: 150px;">Nội dung đánh giá</th>
                                <th style="width: 80px;">Trạng thái</th>
                                <th style="width: 100px;">Ngày tạo</th>
                                <th style="width: 120px;">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reviews as $review)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div>
                                        <strong class="small">{{ $review->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ Str::limit($review->user->email, 20) }}</small>
                                        @if($review->is_anonymous)
                                            <br>
                                            <span class="badge bg-secondary small">Ẩn danh</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <strong class="small">{{ Str::limit($review->roomType->name, 15) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ number_format($review->roomType->price) }}đ</small>
                                </td>
                                <td>
                                    <div class="text-warning">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }} small"></i>
                                        @endfor
                                    </div>
                                    <small class="text-muted">{{ $review->rating }}/5</small>
                                </td>
                                <td>
                                    @if($review->comment)
                                        <div class="text-truncate" style="max-width: 120px;" title="{{ $review->comment }}">
                                            {{ Str::limit($review->comment, 50) }}
                                        </div>
                                    @else
                                        <span class="text-muted fst-italic small">Không có nội dung đánh giá</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }} small">
                                        {{ $review->status_text }}
                                    </span>
                                </td>
                                <td>{{ $review->created_at->format('d/m/Y') }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.room-type-reviews.show', $review->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($review->status == 'pending')
                                            <form action="{{ route('admin.room-type-reviews.approve', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn duyệt đánh giá này?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                            
                                            <form action="{{ route('admin.room-type-reviews.reject', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối đánh giá này?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <form action="{{ route('admin.room-type-reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile Card View -->
            <div class="d-md-none">
                @foreach($reviews as $review)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="mb-1">{{ $review->user->name }}</h6>
                                <small class="text-muted">{{ $review->user->email }}</small>
                                @if($review->is_anonymous)
                                    <span class="badge bg-secondary ms-1">Ẩn danh</span>
                                @endif
                            </div>
                            <span class="badge bg-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }}">
                                {{ $review->status_text }}
                            </span>
                        </div>
                        
                        <div class="mb-2">
                            <strong>{{ $review->roomType->name }}</strong>
                            <br>
                            <small class="text-muted">{{ number_format($review->roomType->price) }}đ/đêm</small>
                        </div>
                        
                        <div class="mb-2">
                            <div class="text-warning">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                @endfor
                            </div>
                            <small class="text-muted">{{ $review->rating }}/5</small>
                        </div>
                        
                        @if($review->comment)
                        <div class="mb-2">
                            <small class="text-muted">{{ Str::limit($review->comment, 150) }}</small>
                        </div>
                        @else
                        <div class="mb-2">
                            <small class="text-muted fst-italic">Không có nội dung đánh giá</small>
                        </div>
                        @endif
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</small>
                            <div class="btn-group" role="group">
                                <a href="{{ route('admin.room-type-reviews.show', $review->id) }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($review->status == 'pending')
                                    <form action="{{ route('admin.room-type-reviews.approve', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn duyệt đánh giá này?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('admin.room-type-reviews.reject', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn từ chối đánh giá này?')">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-warning">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </form>
                                @endif
                                
                                <form action="{{ route('admin.room-type-reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đánh giá này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="d-flex justify-content-center mt-3">
            {{ $reviews->appends(request()->query())->links() }}
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
