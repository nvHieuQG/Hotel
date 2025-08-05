@extends('admin.layouts.admin-master')

@section('title', 'Quản lý thông báo')

@section('header', 'Quản lý thông báo')

@section('content')
<style>
    .sticky-filter-bar {
        position: sticky;
        top: 0;
        z-index: 100;
        background: #fff;
        border-bottom: 1px solid #eee;
        padding: 8px 0 4px 0;
        margin-bottom: 8px;
    }
    .notification-title-small {
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    .notification-content-main {
        font-size: 1.05rem;
        font-weight: 500;
        color: #222;
    }
    .notification-message-main {
        font-size: 0.98rem;
        color: #555;
    }
    .notification-bulk-actions {
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .notification-table th, .notification-table td {
        vertical-align: middle !important;
    }
    .notification-table tbody tr:hover {
        background: #f6f8fa;
    }
    .notification-table .btn {
        min-width: 32px;
        padding: 4px 8px;
    }
    /* Mobile card view */
    .notification-card {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 12px;
        transition: all 0.2s ease;
    }
    .notification-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .notification-card .card-body {
        padding: 12px;
    }
    .notification-card .notification-header {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .notification-card .notification-content {
        margin-bottom: 8px;
    }
    .notification-card .notification-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px;
    }
</style>

<div class="row">
    <div class="col-12 sticky-filter-bar">
        <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-2">
            <form id="filterForm" class="d-flex flex-column flex-md-row gap-2 mb-0 w-100" style="align-items: stretch;">
                <div class="row g-2 w-100">
                    <div class="col-12 col-sm-6 col-md-3">
                        <input type="text" name="search" id="search" class="form-control form-control-sm" placeholder="Tìm kiếm..." value="{{ $search ?? '' }}">
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <select name="type" id="type" class="form-select form-select-sm">
                            <option value="">Tất cả loại</option>
                            <option value="booking_created" {{ $type == 'booking_created' ? 'selected' : '' }}>Đặt phòng mới</option>
                            <option value="booking_status_changed" {{ $type == 'booking_status_changed' ? 'selected' : '' }}>Thay đổi trạng thái</option>
                            <option value="booking_cancelled" {{ $type == 'booking_cancelled' ? 'selected' : '' }}>Hủy đặt phòng</option>
                            <option value="booking_note_created" {{ $type == 'booking_note_created' ? 'selected' : '' }}>Ghi chú mới</option>
                            <option value="booking_note_updated" {{ $type == 'booking_note_updated' ? 'selected' : '' }}>Ghi chú cập nhật</option>
                            <option value="booking_note_deleted" {{ $type == 'booking_note_deleted' ? 'selected' : '' }}>Ghi chú xóa</option>
                            <option value="room_type_review_created" {{ $type == 'room_type_review_created' ? 'selected' : '' }}>Đánh giá mới</option>
                            <option value="room_type_review_updated" {{ $type == 'room_type_review_updated' ? 'selected' : '' }}>Đánh giá cập nhật</option>
                            <option value="payment_received" {{ $type == 'payment_received' ? 'selected' : '' }}>Thanh toán</option>
                            <option value="support_ticket" {{ $type == 'support_ticket' ? 'selected' : '' }}>Hỗ trợ</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <select name="priority" id="priority" class="form-select form-select-sm">
                            <option value="">Tất cả ưu tiên</option>
                            <option value="urgent" {{ $priority == 'urgent' ? 'selected' : '' }}>Khẩn cấp</option>
                            <option value="high" {{ $priority == 'high' ? 'selected' : '' }}>Cao</option>
                            <option value="normal" {{ $priority == 'normal' ? 'selected' : '' }}>Bình thường</option>
                            <option value="low" {{ $priority == 'low' ? 'selected' : '' }}>Thấp</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-2">
                        <select name="is_read" id="is_read" class="form-select form-select-sm">
                            <option value="">Tất cả trạng thái</option>
                            <option value="0" {{ $isRead === '0' ? 'selected' : '' }}>Chưa đọc</option>
                            <option value="1" {{ $isRead === '1' ? 'selected' : '' }}>Đã đọc</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="fas fa-search me-1"></i>Tìm kiếm</button>
                            <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
            <div class="notification-title-small"><i class="fas fa-bell me-2"></i>Quản lý thông báo</div>
            <form id="bulkActionForm" method="POST" action="{{ route('admin.notifications.delete-multi') }}" class="d-flex gap-2">
                @csrf
                <button type="submit" class="btn btn-danger btn-sm" id="deleteSelectedBtn" disabled>
                    <i class="fas fa-trash me-1"></i>Xóa đã chọn
                </button>
                <button type="submit" formaction="{{ route('admin.notifications.mark-read-multi') }}" class="btn btn-success btn-sm" id="markReadSelectedBtn" disabled>
                    <i class="fas fa-check me-1"></i>Đánh dấu đã đọc
                </button>
            </form>
        </div>

        <!-- Desktop Table View -->
        <div class="d-none d-lg-block">
            <div class="card">
                <div class="card-body p-0">
                    <div id="notificationsTable">
                        @if($notifications->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 30px;"><input type="checkbox" id="selectAllNotifications"></th>
                                            <th style="width: 50px;">#</th>
                                            <th style="width: 60px;">Icon</th>
                                            <th>Nội dung</th>
                                            <th style="width: 100px;">Loại</th>
                                            <th style="width: 80px;">Ưu tiên</th>
                                            <th style="width: 80px;">Trạng thái</th>
                                            <th style="width: 120px;">Thời gian</th>
                                            <th style="width: 100px;">Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($notifications as $notification)
                                            @php
                                                $link = null;
                                                if (str_contains($notification->type, 'note')) {
                                                    $bookingId = $notification->data['booking_id'] ?? null;
                                                    if ($bookingId) {
                                                        $link = route('admin.bookings.show', $bookingId);
                                                    }
                                                } elseif (str_contains($notification->type, 'review')) {
                                                    $reviewId = $notification->data['review_id'] ?? null;
                                                    if ($reviewId) {
                                                        $link = route('admin.room-type-reviews.show', $reviewId);
                                                    }
                                                } elseif (str_contains($notification->type, 'booking')) {
                                                    $bookingId = $notification->data['booking_id'] ?? null;
                                                    if ($bookingId) {
                                                        $link = route('admin.bookings.show', $bookingId);
                                                    }
                                                } elseif (str_contains($notification->type, 'room')) {
                                                    $roomId = $notification->data['room_id'] ?? null;
                                                    if ($roomId) {
                                                        $link = route('admin.rooms.show', $roomId);
                                                    }
                                                }
                                            @endphp
                                            <tr class="notification-row" data-id="{{ $notification->id }}">
                                                <td><input type="checkbox" name="notification_id[]" value="{{ $notification->id }}" class="notification-checkbox"></td>
                                                <td>{{ $notification->id }}</td>
                                                <td>
                                                    @if($link)
                                                        <a href="{{ $link }}" class="text-decoration-none">
                                                    @endif
                                                    <div class="icon-circle bg-{{ $notification->color }}" style="width: 35px; height: 35px;">
                                                        <i class="{{ $notification->display_icon }} text-white" style="font-size: 0.8rem;"></i>
                                                    </div>
                                                    @if($link)
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($link)
                                                        <a href="{{ $link }}" class="notification-content text-decoration-none text-dark">
                                                    @endif
                                                    <div class="notification-content-main fw-bold text-truncate" style="max-width: 350px;" title="{{ $notification->title }}">
                                                        {{ $notification->title }}
                                                    </div>
                                                    <div class="notification-message-main small text-muted text-truncate" style="max-width: 350px;" title="{{ $notification->message }}">
                                                        {{ $notification->message }}
                                                    </div>
                                                    @if($link)
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary small">{{ $notification->type }}</span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-{{ $notification->color }} small">
                                                        {{ ucfirst($notification->priority) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($notification->is_read)
                                                        <span class="badge bg-success small">Đã đọc</span>
                                                    @else
                                                        <span class="badge bg-warning small">Chưa đọc</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="small text-muted">
                                                        {{ $notification->time_ago }}
                                                    </div>
                                                </td>
                                                <td>
                                                    <form method="POST" action="{{ route('admin.notifications.destroy', $notification->id) }}" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Xóa"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state text-center py-5">
                                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                <h5>Không có thông báo</h5>
                                <p class="text-muted">Chưa có thông báo nào phù hợp với bộ lọc hiện tại.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Mobile/Tablet Card View -->
        <div class="d-lg-none">
            @if($notifications->count() > 0)
                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" id="selectAllNotificationsMobile" class="form-check-input">
                        <label class="form-check-label" for="selectAllNotificationsMobile">
                            Chọn tất cả
                        </label>
                    </div>
                </div>
                
                @foreach($notifications as $notification)
                    @php
                        $link = null;
                        if (str_contains($notification->type, 'note')) {
                            $bookingId = $notification->data['booking_id'] ?? null;
                            if ($bookingId) {
                                $link = route('admin.bookings.show', $bookingId);
                            }
                        } elseif (str_contains($notification->type, 'review')) {
                            $reviewId = $notification->data['review_id'] ?? null;
                            if ($reviewId) {
                                $link = route('admin.room-type-reviews.show', $reviewId);
                            }
                        } elseif (str_contains($notification->type, 'booking')) {
                            $bookingId = $notification->data['booking_id'] ?? null;
                            if ($bookingId) {
                                $link = route('admin.bookings.show', $bookingId);
                            }
                        } elseif (str_contains($notification->type, 'room')) {
                            $roomId = $notification->data['room_id'] ?? null;
                            if ($roomId) {
                                $link = route('admin.rooms.show', $roomId);
                            }
                        }
                    @endphp
                    
                    <div class="card notification-card">
                        <div class="card-body">
                            <div class="notification-header">
                                <div class="form-check">
                                    <input type="checkbox" name="notification_id[]" value="{{ $notification->id }}" class="notification-checkbox form-check-input">
                                </div>
                                @if($link)
                                    <a href="{{ $link }}" class="text-decoration-none">
                                @endif
                                <div class="icon-circle bg-{{ $notification->color }}" style="width: 35px; height: 35px;">
                                    <i class="{{ $notification->display_icon }} text-white" style="font-size: 0.8rem;"></i>
                                </div>
                                @if($link)
                                    </a>
                                @endif
                                <div class="flex-grow-1">
                                    @if($link)
                                        <a href="{{ $link }}" class="text-decoration-none text-dark">
                                    @endif
                                    <div class="notification-content-main fw-bold">
                                        {{ $notification->title }}
                                    </div>
                                    <div class="notification-message-main small text-muted">
                                        {{ $notification->message }}
                                    </div>
                                    @if($link)
                                        </a>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="notification-footer">
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-{{ $notification->color }} small">
                                        {{ ucfirst($notification->priority) }}
                                    </span>
                                    @if($notification->is_read)
                                        <span class="badge bg-success small">Đã đọc</span>
                                    @else
                                        <span class="badge bg-warning small">Chưa đọc</span>
                                    @endif
                                    <small class="text-muted">{{ $notification->time_ago }}</small>
                                </div>
                                <form method="POST" action="{{ route('admin.notifications.destroy', $notification->id) }}" style="display:inline;" onsubmit="return confirm('Bạn có chắc muốn xóa thông báo này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Xóa"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                        <h5>Không có thông báo</h5>
                        <p class="text-muted">Chưa có thông báo nào phù hợp với bộ lọc hiện tại.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Phân trang -->
        @if($notifications->count() > 0)
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center p-3 border-top mt-3">
                <div class="small text-muted mb-2 mb-md-0">
                    Hiển thị {{ $notifications->firstItem() ?? 0 }} - {{ $notifications->lastItem() ?? 0 }} 
                    trong tổng số {{ $notifications->total() }} thông báo
                </div>
                <div id="paginationContainer">
                    {{ $notifications->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Checkbox chọn tất cả - Desktop
    $('#selectAllNotifications').on('change', function() {
        $('.notification-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActionButtons();
    });
    
    // Checkbox chọn tất cả - Mobile
    $('#selectAllNotificationsMobile').on('change', function() {
        $('.notification-checkbox').prop('checked', $(this).is(':checked'));
        updateBulkActionButtons();
    });
    
    // Checkbox từng dòng
    $('.notification-checkbox').on('change', function() {
        updateBulkActionButtons();
    });
    
    function updateBulkActionButtons() {
        const anyChecked = $('.notification-checkbox:checked').length > 0;
        $('#deleteSelectedBtn').prop('disabled', !anyChecked);
        $('#markReadSelectedBtn').prop('disabled', !anyChecked);
    }
});
</script>
@endsection 