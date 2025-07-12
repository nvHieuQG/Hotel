@extends('admin.layouts.admin-master')

@section('title', 'Chi tiết thông báo')

@section('header', 'Chi tiết thông báo')

@section('content')
<div class="row">
    <div class="col-xl-10 mx-auto">
        <div class="card">
            <div class="card-header py-2">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center">
                        <a href="{{ route('admin.notifications.index') }}" class="btn btn-sm btn-outline-secondary me-3">
                            <i class="fas fa-arrow-left"></i>
                        </a>
                        <h6 class="m-0 font-weight-bold">Thông báo #{{ $notification->id }}</h6>
                    </div>
                    <div class="btn-group btn-group-sm mt-2 mt-md-0" role="group">
                        @if(!$notification->is_read)
                            <button type="button" class="btn btn-success mark-read-btn" data-id="{{ $notification->id }}" title="Đánh dấu đã đọc">
                                <i class="fas fa-check"></i>
                            </button>
                        @endif
                        <button type="button" class="btn btn-danger delete-btn" data-id="{{ $notification->id }}" title="Xóa thông báo">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Nội dung chính - Ưu tiên hiển thị -->
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Header thông báo -->
                        <div class="notification-detail-header">
                            <div class="d-flex align-items-center flex-column flex-md-row">
                                <div class="icon-circle bg-{{ $notification->color }} me-3 mb-3 mb-md-0" style="width: 50px; height: 50px;">
                                    <i class="{{ $notification->display_icon }} text-white" style="font-size: 1.2rem;"></i>
                                </div>
                                <div class="flex-grow-1 text-center text-md-start">
                                    <h4 class="mb-1">{{ $notification->title }}</h4>
                                    <div class="d-flex align-items-center gap-3 flex-wrap justify-content-center justify-content-md-start">
                                        <span class="text-muted small">{{ $notification->time_ago }}</span>
                                        @php
                                            $priorityColors = [
                                                'urgent' => 'danger',
                                                'high' => 'warning',
                                                'normal' => 'primary',
                                                'low' => 'secondary'
                                            ];
                                        @endphp
                                        <span class="badge bg-{{ $priorityColors[$notification->priority] }} small">
                                            {{ ucfirst($notification->priority) }}
                                        </span>
                                        @if($notification->is_read)
                                            <span class="badge bg-success small">Đã đọc</span>
                                        @else
                                            <span class="badge bg-warning small">Chưa đọc</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Nội dung thông báo -->
                        <div class="notification-detail-content mb-4">
                            <h6 class="card-title mb-3">
                                <i class="fas fa-comment me-2"></i>Nội dung thông báo
                            </h6>
                            <p class="mb-0">{{ $notification->message }}</p>
                        </div>

                        <!-- Dữ liệu bổ sung -->
                        @if($notification->data && count($notification->data) > 0)
                            <div class="notification-detail-content mb-4">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-database me-2"></i>Dữ liệu bổ sung
                                </h6>
                                <div class="data-display">
                                    <div class="row g-3">
                                        @foreach($formattedData as $item)
                                            <div class="col-md-6 col-lg-4">
                                                <div class="data-item">
                                                    <div class="data-label">
                                                        <i class="{{ $item['icon'] }} me-1"></i>{{ $item['label'] }}
                                                    </div>
                                                    <div class="data-value">
                                                        {!! $item['formatted_value'] !!}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    
                                </div>
                            </div>
                        @endif

                        <!-- Liên kết nhanh -->
                        @if($notification->data)
                            <div class="notification-detail-content mb-4">
                                <h6 class="card-title mb-3">
                                    <i class="fas fa-link me-2"></i>Liên kết nhanh
                                </h6>
                                <div class="quick-links-grid">
                                    @if(isset($notification->data['booking_id']))
                                        <a href="{{ route('admin.bookings.show', $notification->data['booking_id']) }}" 
                                           class="quick-link-btn btn btn-outline-primary">
                                            <i class="fas fa-calendar-check me-1"></i>Đặt phòng
                                        </a>
                                    @endif
                                    
                                    @if(isset($notification->data['user_id']))
                                        <a href="#" class="quick-link-btn btn btn-outline-info">
                                            <i class="fas fa-user me-1"></i>Khách hàng
                                        </a>
                                    @endif
                                    
                                    @if(isset($notification->data['ticket_id']))
                                        <a href="{{ route('admin.support.showTicket', $notification->data['ticket_id']) }}" 
                                           class="quick-link-btn btn btn-outline-warning">
                                            <i class="fas fa-headset me-1"></i>Ticket hỗ trợ
                                        </a>
                                    @endif
                                    
                                    @if(isset($notification->data['note_id']))
                                        <a href="{{ route('admin.bookings.show', $notification->data['booking_id']) }}#notes" 
                                           class="quick-link-btn btn btn-outline-secondary">
                                            <i class="fas fa-sticky-note me-1"></i>Ghi chú
                                        </a>
                                    @endif
                                    
                                    @if(isset($notification->data['review_id']))
                                        <a href="#" class="quick-link-btn btn btn-outline-success">
                                            <i class="fas fa-star me-1"></i>Đánh giá
                                        </a>
                                    @endif
                                    
                                    @if(isset($notification->data['room_type_id']))
                                        <a href="#" class="quick-link-btn btn btn-outline-info">
                                            <i class="fas fa-bed me-1"></i>Loại phòng
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Sidebar thông tin - Thu gọn -->
                    <div class="col-lg-4 mt-4 mt-lg-0">
                        <div class="notification-detail-sidebar">
                            <div class="card-header">
                                <h6 class="m-0">
                                    <i class="fas fa-info-circle me-2"></i>Thông tin
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted">Loại:</span>
                                        <span class="badge bg-secondary small">{{ $notification->type }}</span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small text-muted">Trạng thái:</span>
                                        @if($notification->is_read)
                                            <span class="badge bg-success small">Đã đọc</span>
                                        @else
                                            <span class="badge bg-warning small">Chưa đọc</span>
                                        @endif
                                    </div>
                                    @if($notification->is_read)
                                        <div class="small text-muted">
                                            Đọc: {{ $notification->read_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </div>
                                
                                <div class="mb-3">
                                    <div class="small text-muted mb-1">Tạo lúc:</div>
                                    <div class="small">{{ $notification->created_at->format('d/m/Y H:i:s') }}</div>
                                </div>
                                
                                @if($notification->updated_at != $notification->created_at)
                                    <div class="mb-3">
                                        <div class="small text-muted mb-1">Cập nhật:</div>
                                        <div class="small">{{ $notification->updated_at->format('d/m/Y H:i:s') }}</div>
                                    </div>
                                @endif
                                
                                <hr>
                                
                                <div class="d-grid">
                                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-list me-1"></i>Danh sách thông báo
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Đánh dấu đã đọc
    $('.mark-read-btn').on('click', function() {
        const id = $(this).data('id');
        $.post('/admin/api/notifications/mark-read', { notification_id: id })
            .done(function(response) {
                if (response.success) {
                    location.reload();
                }
            });
    });

    // Xóa thông báo
    $('.delete-btn').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Bạn có chắc chắn muốn xóa thông báo này?')) {
            $.ajax({
                url: '/admin/api/notifications/delete',
                type: 'DELETE',
                data: { notification_id: id },
                success: function(response) {
                    if (response.success) {
                        window.location.href = '{{ route("admin.notifications.index") }}';
                    }
                }
            });
        }
    });
});
</script>
@endsection 