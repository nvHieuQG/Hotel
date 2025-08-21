@extends('admin.layouts.admin-master')

@section('title', 'Quản Lý Khuyến Mại')

@section('header', 'Quản lý khuyến mại')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Quản lý khuyến mại</li>
    </ol>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-tags fa-2x text-primary mb-2"></i>
                    <h3 class="text-primary mb-1">{{ $stats['total'] }}</h3>
                    <p class="text-muted mb-0">Tổng khuyến mại</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                    <h3 class="text-success mb-1">{{ $stats['active'] }}</h3>
                    <p class="text-muted mb-0">Đang hoạt động</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-star fa-2x text-warning mb-2"></i>
                    <h3 class="text-warning mb-1">{{ $stats['featured'] }}</h3>
                    <p class="text-muted mb-0">Nổi bật</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-clock fa-2x text-danger mb-2"></i>
                    <h3 class="text-danger mb-1">{{ $stats['expired'] }}</h3>
                    <p class="text-muted mb-0">Đã hết hạn</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold">Danh sách khuyến mại</h6>
            <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Thêm mới
            </a>
        </div>
        <div class="card-body">
            {{-- Filters --}}
            <form method="GET" action="{{ route('admin.promotions.index') }}" class="mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Không hoạt động</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="discount_type" class="form-select">
                            <option value="">Tất cả loại</option>
                            <option value="percentage" {{ request('discount_type') == 'percentage' ? 'selected' : '' }}>Phần trăm</option>
                            <option value="fixed" {{ request('discount_type') == 'fixed' ? 'selected' : '' }}>Cố định</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên, mã, mô tả..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-outline-primary w-100">
                            <i class="fas fa-search me-2"></i>Tìm
                        </button>
                    </div>
                </div>
            </form>

            {{-- Table --}}
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 80px;">Hình ảnh</th>
                            <th style="width: 250px;">Thông tin</th>
                            <th style="width: 120px;">Giảm giá</th>
                            <th style="width: 150px;">Hạn sử dụng</th>
                            <th style="width: 150px;">Phạm vi áp dụng</th>
                            <th style="width: 120px;">Trạng thái</th>
                            <th style="width: 100px;">Thống kê</th>
                            <th style="width: 120px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($promotions as $promotion)
                            <tr>
                                <td>
                                    @if($promotion->image)
                                        <img src="{{ asset('storage/' . $promotion->image) }}" alt="{{ $promotion->title }}" class="img-thumbnail rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                        <div class="bg-primary text-white d-flex align-items-center justify-content-center rounded" style="width: 60px; height: 60px;">
                                            <i class="fas fa-percentage"></i>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <strong class="text-dark">{{ $promotion->title }}</strong>
                                        @if($promotion->is_featured)
                                            <span class="badge bg-warning text-dark ms-2">⭐ Nổi bật</span>
                                        @endif
                                    </div>
                                    <div class="small text-muted mb-1">{{ Str::limit($promotion->description, 60) }}</div>
                                    <div class="small">
                                        <strong class="text-primary">Mã:</strong> 
                                        <code class="bg-light px-2 py-1 rounded">{{ $promotion->code }}</code>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success text-white fs-6">{{ $promotion->discount_text }}</span>
                                    @if($promotion->minimum_amount > 0)
                                        <div class="small text-muted mt-1">
                                            Tối thiểu: {{ number_format($promotion->minimum_amount, 0, ',', '.') }}đ
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($promotion->valid_from)
                                        <div class="small text-muted mb-1">
                                            Từ: {{ $promotion->valid_from->format('d/m/Y') }}
                                        </div>
                                    @endif
                                    <div class="fw-bold {{ $promotion->expired_at < now() ? 'text-danger' : 'text-success' }}">
                                        Đến: {{ $promotion->expired_at->format('d/m/Y') }}
                                    </div>
                                    <div class="small text-muted">
                                        @if($promotion->valid_from && $promotion->valid_from > now())
                                            {{ $promotion->valid_from->diffForHumans() }}
                                        @else
                                            {{ $promotion->expired_at->diffForHumans() }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($promotion->roomTypes && $promotion->roomTypes->count() > 0)
                                        <div class="mb-1">
                                            <span class="badge bg-info text-white">
                                                <i class="fas fa-layer-group me-1"></i>
                                                {{ $promotion->roomTypes->count() }} loại phòng
                                            </span>
                                        </div>
                                        @if($promotion->roomTypes->count() <= 2)
                                            @foreach($promotion->roomTypes as $roomType)
                                                <div class="small text-secondary">{{ $roomType->name }}</div>
                                            @endforeach
                                        @else
                                            <div class="small text-secondary">
                                                {{ $promotion->roomTypes->first()->name }} + {{ $promotion->roomTypes->count() - 1 }} loại khác
                                            </div>
                                        @endif
                                    @else
                                        <span class="badge bg-primary text-white">
                                            <i class="fas fa-globe me-1"></i> Tất cả phòng
                                        </span>
                                    @endif
                                    
                                    @if($promotion->can_combine)
                                        <div class="mt-1">
                                            <span class="badge bg-secondary text-white">
                                                <i class="fas fa-layer-group me-1"></i> Có thể gộp
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $status = $promotion->status_text;
                                        $statusColor = $promotion->status_color;
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }} text-white">
                                        {{ $status }}
                                    </span>
                                </td>
                                <td>
                                    <div class="small">
                                        <div class="mb-1">
                                            <strong>Đã dùng:</strong> {{ $promotion->used_count }}
                                        </div>
                                        @if($promotion->usage_limit)
                                            <div>
                                                <strong>Còn lại:</strong> {{ $promotion->usage_limit - $promotion->used_count }}
                                            </div>
                                        @else
                                            <div class="text-success">
                                                <strong>Không giới hạn</strong>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.promotions.show', $promotion->id) }}" class="btn btn-sm btn-outline-info" title="Xem chi tiết">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.promotions.edit', $promotion->id) }}" class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($promotion->can_be_deleted ?? true)
                                            <form method="POST" action="{{ route('admin.promotions.destroy', $promotion->id) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa" onclick="return confirm('Bạn có chắc muốn xóa khuyến mại này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @else
                                            <button class="btn btn-sm btn-outline-secondary" disabled title="Không thể xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <h5>Không có khuyến mại nào</h5>
                                        <p class="mb-3">Bắt đầu tạo khuyến mại đầu tiên để thu hút khách hàng</p>
                                        <a href="{{ route('admin.promotions.create') }}" class="btn btn-primary">
                                            <i class="fas fa-plus me-2"></i>Tạo khuyến mại đầu tiên
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($promotions->hasPages())
                <div class="d-flex justify-content-center mt-4">
                    {{ $promotions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Show alert notification
    function showAlert(type, message) {
        const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        
        const alert = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 80px; right: 20px; z-index: 9999; min-width: 300px; max-width: 500px;">
                <i class="fas ${iconClass} me-2"></i>${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append(alert);
        
        // Auto remove after 4 seconds
        setTimeout(function() {
            alert.alert('close');
        }, 4000);
    }
    
    // Show success message if exists
    @if(session('success'))
        showAlert('success', '{{ session('success') }}');
    @endif
    
    @if(session('error'))
        showAlert('error', '{{ session('error') }}');
    @endif
});
</script>

<style>
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

.card-header {
    border-bottom: 1px solid #e9ecef;
    background-color: #f8f9fa;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
    background-color: #f8f9fa;
}

.table td {
    vertical-align: middle;
    border-top: 1px solid #e9ecef;
}

.badge {
    font-size: 0.75rem;
    padding: 0.5rem 0.75rem;
    border-radius: 6px;
}

.btn-group .btn {
    border-radius: 4px;
    margin-right: 2px;
}

.btn-group .btn:last-child {
    margin-right: 0;
}

.form-select, .form-control {
    border-radius: 6px;
    border: 1px solid #ced4da;
}

.form-select:focus, .form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

code {
    background-color: #f8f9fa;
    color: #e83e8c;
    font-size: 0.875em;
}
</style>
@endpush
@endsection 