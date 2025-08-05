@extends('admin.layouts.admin-master')

@section('header', 'Chi tiết thông báo')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Thông báo</a></li>
        <li class="breadcrumb-item active">Chi tiết thông báo</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-bell me-1"></i>
                    Chi tiết thông báo
                </div>
                <div>
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($notification)
                <div class="row">
                    <div class="col-md-8">
                        <div class="notification-detail">
                            <!-- Header với icon và thông tin cơ bản -->
                            <div class="d-flex align-items-center mb-4">
                                <div class="icon-circle bg-{{ $notification->color }} me-3" style="width: 60px; height: 60px;">
                                    <i class="{{ $notification->display_icon }} text-white" style="font-size: 24px;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h3 class="mb-1">{{ $notification->title }}</h3>
                                    <div class="d-flex gap-2 mb-2">
                                        <span class="badge bg-{{ $notification->color }}">{{ $notification->type_text }}</span>
                                        <span class="badge bg-{{ $notification->badge_color }}">{{ $notification->priority_text }}</span>
                                        @if($notification->is_read)
                                            <span class="badge bg-success">Đã đọc</span>
                                        @else
                                            <span class="badge bg-warning">Chưa đọc</span>
                                        @endif
                                    </div>
                                    <p class="text-muted mb-0">
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $notification->time_ago }} ({{ $notification->created_at->format('d/m/Y H:i:s') }})
                                    </p>
                                </div>
                            </div>

                            <!-- Thông tin chi tiết -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Thông tin cơ bản
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>ID:</strong></td>
                                                    <td>{{ $notification->id }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Loại:</strong></td>
                                                    <td>{{ $notification->type_text }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Độ ưu tiên:</strong></td>
                                                    <td>{{ $notification->priority_text }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Trạng thái:</strong></td>
                                                    <td>
                                                        @if($notification->is_read)
                                                            <span class="badge bg-success">Đã đọc</span>
                                                        @else
                                                            <span class="badge bg-warning">Chưa đọc</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card">
                                        <div class="card-header">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Thông tin thời gian
                                        </div>
                                        <div class="card-body">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Ngày tạo:</strong></td>
                                                    <td>{{ $notification->created_at->format('d/m/Y H:i:s') }}</td>
                                                </tr>
                                                @if($notification->read_at)
                                                <tr>
                                                    <td><strong>Đã đọc lúc:</strong></td>
                                                    <td>{{ $notification->read_at->format('d/m/Y H:i:s') }}</td>
                                                </tr>
                                                @endif
                                                <tr>
                                                    <td><strong>Cập nhật lúc:</strong></td>
                                                    <td>{{ $notification->updated_at->format('d/m/Y H:i:s') }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Thời gian tạo:</strong></td>
                                                    <td>{{ $notification->time_ago }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Nội dung thông báo -->
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-envelope me-1"></i>
                                    Nội dung thông báo
                                </div>
                                <div class="card-body">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        {{ $notification->message }}
                                    </div>
                                </div>
                            </div>

                            <!-- Dữ liệu bổ sung -->
                            @if($notification->data)
                                <div class="card">
                                    <div class="card-header">
                                        <i class="fas fa-database me-1"></i>
                                        Dữ liệu bổ sung
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Thuộc tính</th>
                                                        <th>Giá trị</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($notification->data as $key => $value)
                                                        <tr>
                                                            <td><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}</strong></td>
                                                            <td>
                                                                @if(is_array($value))
                                                                    <pre class="mb-0">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                                                @else
                                                                    {{ $value }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <!-- Thao tác -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <i class="fas fa-cogs me-1"></i>
                                Thao tác
                            </div>
                            <div class="card-body">
                                @if(!$notification->is_read)
                                    <form action="{{ route('admin.notifications.mark-read', $notification->id) }}" method="POST" class="mb-3">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="fas fa-check me-2"></i> Đánh dấu đã đọc
                                        </button>
                                    </form>
                                @else
                                    <div class="alert alert-success mb-3">
                                        <i class="fas fa-check-circle me-2"></i>
                                        Thông báo đã được đánh dấu đã đọc
                                    </div>
                                @endif
                                
                                <form action="{{ route('admin.notifications.delete', $notification->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa thông báo này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-trash me-2"></i> Xóa thông báo
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Thông tin nhanh -->
                        <div class="card mb-3">
                            <div class="card-header">
                                <i class="fas fa-info-circle me-1"></i>
                                Thông tin nhanh
                            </div>
                            <div class="card-body">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-hashtag me-2 text-muted"></i>
                                    <span><strong>ID:</strong> {{ $notification->id }}</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="{{ $notification->display_icon }} me-2 text-{{ $notification->color }}"></i>
                                    <span><strong>Icon:</strong> {{ $notification->display_icon }}</span>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-clock me-2 text-muted"></i>
                                    <span><strong>Thời gian:</strong> {{ $notification->time_ago }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar me-2 text-muted"></i>
                                    <span><strong>Ngày:</strong> {{ $notification->created_at->format('d/m/Y') }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Liên kết nhanh -->
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-link me-1"></i>
                                Liên kết nhanh
                            </div>
                            <div class="card-body">
                                <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-primary w-100 mb-2">
                                    <i class="fas fa-list me-2"></i> Danh sách thông báo
                                </a>
                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-home me-2"></i> Dashboard
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Không tìm thấy thông báo này.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<style>
    .icon-circle {
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .table-borderless td {
        border: none;
        padding: 0.5rem 0;
    }
    
    .table-borderless td:first-child {
        width: 40%;
        color: #6c757d;
    }
    
    .notification-detail .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        border: 1px solid rgba(0, 0, 0, 0.125);
    }
    
    .notification-detail .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        font-weight: 600;
    }
    
    .badge {
        font-size: 0.75em;
    }
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>
<script>
    $(document).ready(function() {
        // Auto refresh page after 30 seconds if notification is unread
        @if(!$notification->is_read)
            setTimeout(function() {
                location.reload();
            }, 30000);
        @endif
        
        // Thêm hiệu ứng hover cho các card
        $('.card').hover(
            function() {
                $(this).addClass('shadow-sm');
            },
            function() {
                $(this).removeClass('shadow-sm');
            }
        );
    });
</script>
@endsection 