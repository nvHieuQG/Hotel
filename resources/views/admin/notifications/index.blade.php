@extends('admin.layouts.admin-master')

@section('title', 'Quản lý thông báo')

@section('header', 'Quản lý thông báo')

@section('content')
<div class="row">
    <!-- Thống kê - Thu gọn -->
    <div class="col-xl-12 mb-3">
        <div class="card">
            <div class="card-header py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                    </h6>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#statsCollapse">
                        <i class="fas fa-chart-bar"></i>
                    </button>
                </div>
            </div>
            <div class="collapse show" id="statsCollapse">
                <div class="card-body py-2">
                    <div class="row g-2">
                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card border-left-primary h-100 stats-card" data-filter="all" style="cursor: pointer;">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-xs text-uppercase mb-0 text-muted">Tổng cộng</div>
                                            <div class="h5 mb-0 font-weight-bold">{{ $stats['total'] }}</div>
                                        </div>
                                        <div class="icon-circle bg-primary" style="width: 35px; height: 35px;">
                                            <i class="fas fa-bell text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card border-left-warning h-100 stats-card" data-filter="unread" style="cursor: pointer;">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-xs text-uppercase mb-0 text-muted">Chưa đọc</div>
                                            <div class="h5 mb-0 font-weight-bold">{{ $stats['unread'] }}</div>
                                        </div>
                                        <div class="icon-circle bg-warning" style="width: 35px; height: 35px;">
                                            <i class="fas fa-envelope text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card border-left-danger h-100 stats-card" data-filter="urgent" style="cursor: pointer;">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-xs text-uppercase mb-0 text-muted">Khẩn cấp</div>
                                            <div class="h5 mb-0 font-weight-bold">{{ $stats['by_priority']['urgent'] }}</div>
                                        </div>
                                        <div class="icon-circle bg-danger" style="width: 35px; height: 35px;">
                                            <i class="fas fa-exclamation-triangle text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card border-left-info h-100 stats-card" data-filter="high" style="cursor: pointer;">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-xs text-uppercase mb-0 text-muted">Cao</div>
                                            <div class="h5 mb-0 font-weight-bold">{{ $stats['by_priority']['high'] }}</div>
                                        </div>
                                        <div class="icon-circle bg-info" style="width: 35px; height: 35px;">
                                            <i class="fas fa-arrow-up text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card border-left-success h-100 stats-card" data-filter="normal" style="cursor: pointer;">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-xs text-uppercase mb-0 text-muted">Bình thường</div>
                                            <div class="h5 mb-0 font-weight-bold">{{ $stats['by_priority']['normal'] }}</div>
                                        </div>
                                        <div class="icon-circle bg-success" style="width: 35px; height: 35px;">
                                            <i class="fas fa-minus text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-4 col-6">
                            <div class="card border-left-secondary h-100 stats-card" data-filter="low" style="cursor: pointer;">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="text-xs text-uppercase mb-0 text-muted">Thấp</div>
                                            <div class="h5 mb-0 font-weight-bold">{{ $stats['by_priority']['low'] }}</div>
                                        </div>
                                        <div class="icon-circle bg-secondary" style="width: 35px; height: 35px;">
                                            <i class="fas fa-arrow-down text-white" style="font-size: 0.8rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bộ lọc - Thu gọn -->
    <div class="col-xl-12 mb-3">
        <div class="card filter-section">
            <div class="card-header py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-filter me-2"></i>Bộ lọc
                    </h6>
                    <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                        <i class="fas fa-filter"></i>
                    </button>
                </div>
            </div>
            <div class="collapse show" id="filterCollapse">
                <div class="card-body py-2">
                    <form id="filterForm" class="row g-2">
                        <div class="col-xl-3 col-lg-4 col-md-6">
                            <select name="type" id="type" class="form-select form-select-sm">
                                <option value="">Tất cả loại</option>
                                <optgroup label="Đặt phòng">
                                    <option value="booking_created" {{ $type == 'booking_created' ? 'selected' : '' }}>Đặt phòng mới</option>
                                    <option value="booking_status_changed" {{ $type == 'booking_status_changed' ? 'selected' : '' }}>Thay đổi trạng thái</option>
                                    <option value="booking_cancelled" {{ $type == 'booking_cancelled' ? 'selected' : '' }}>Hủy đặt phòng</option>
                                </optgroup>
                                <optgroup label="Ghi chú">
                                    <option value="booking_note_created" {{ $type == 'booking_note_created' ? 'selected' : '' }}>Ghi chú mới</option>
                                    <option value="booking_note_updated" {{ $type == 'booking_note_updated' ? 'selected' : '' }}>Ghi chú cập nhật</option>
                                    <option value="booking_note_deleted" {{ $type == 'booking_note_deleted' ? 'selected' : '' }}>Ghi chú xóa</option>
                                </optgroup>
                                <optgroup label="Đánh giá">
                                    <option value="room_type_review_created" {{ $type == 'room_type_review_created' ? 'selected' : '' }}>Đánh giá mới</option>
                                    <option value="room_type_review_updated" {{ $type == 'room_type_review_updated' ? 'selected' : '' }}>Đánh giá cập nhật</option>
                                </optgroup>
                                <optgroup label="Khác">
                                    <option value="payment_received" {{ $type == 'payment_received' ? 'selected' : '' }}>Thanh toán</option>
                                    <option value="support_ticket" {{ $type == 'support_ticket' ? 'selected' : '' }}>Hỗ trợ</option>
                                </optgroup>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <select name="priority" id="priority" class="form-select form-select-sm">
                                <option value="">Tất cả độ ưu tiên</option>
                                <option value="urgent" {{ $priority == 'urgent' ? 'selected' : '' }}>Khẩn cấp</option>
                                <option value="high" {{ $priority == 'high' ? 'selected' : '' }}>Cao</option>
                                <option value="normal" {{ $priority == 'normal' ? 'selected' : '' }}>Bình thường</option>
                                <option value="low" {{ $priority == 'low' ? 'selected' : '' }}>Thấp</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-6">
                            <select name="is_read" id="is_read" class="form-select form-select-sm">
                                <option value="">Tất cả trạng thái</option>
                                <option value="0" {{ $isRead === '0' ? 'selected' : '' }}>Chưa đọc</option>
                                <option value="1" {{ $isRead === '1' ? 'selected' : '' }}>Đã đọc</option>
                            </select>
                        </div>
                        <div class="col-xl-5 col-lg-2 col-md-6">
                            <div class="d-flex gap-2 flex-wrap">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> <span class="d-none d-md-inline">Lọc</span>
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="clearFilterBtn">
                                    <i class="fas fa-times"></i> <span class="d-none d-md-inline">Xóa lọc</span>
                                </button>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-success" id="markAllReadBtn" title="Đánh dấu tất cả đã đọc">
                                        <i class="fas fa-check-double"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger" id="deleteReadBtn" title="Xóa đã đọc">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <button type="button" class="btn btn-warning" id="deleteOldBtn" title="Xóa cũ">
                                        <i class="fas fa-clock"></i>
                                    </button>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-info dropdown-toggle" data-bs-toggle="dropdown" title="Test">
                                            <i class="fas fa-flask"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" id="testNoteBtn">Test ghi chú</a></li>
                                            <li><a class="dropdown-item" href="#" id="testReviewBtn">Test đánh giá</a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách thông báo - Ưu tiên hiển thị -->
    <div class="col-xl-12">
        <div class="card">
            <div class="card-header py-2">
                <h6 class="m-0 font-weight-bold">
                    <i class="fas fa-list me-2"></i>Danh sách thông báo (<span id="notificationCount">{{ $notifications->total() }}</span>)
                </h6>
            </div>
            <div class="card-body p-0">
                <div id="notificationsTable">
                    @if($notifications->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 50px;" class="d-none d-md-table-cell">#</th>
                                        <th style="width: 60px;">Icon</th>
                                        <th>Nội dung</th>
                                        <th style="width: 100px;" class="d-none d-lg-table-cell">Loại</th>
                                        <th style="width: 80px;" class="d-none d-md-table-cell">Ưu tiên</th>
                                        <th style="width: 80px;" class="d-none d-sm-table-cell">Trạng thái</th>
                                        <th style="width: 120px;" class="d-none d-lg-table-cell">Thời gian</th>
                                        <th style="width: 100px;">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($notifications as $notification)
                                        <tr class="notification-row" data-id="{{ $notification->id }}">
                                            <td class="d-none d-md-table-cell">{{ $notification->id }}</td>
                                            <td>
                                                <div class="icon-circle bg-{{ $notification->color }}" style="width: 35px; height: 35px;">
                                                    <i class="{{ $notification->display_icon }} text-white" style="font-size: 0.8rem;"></i>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="notification-content">
                                                    <div class="fw-bold text-truncate" style="max-width: 300px;" title="{{ $notification->title }}">
                                                        {{ $notification->title }}
                                                    </div>
                                                    <div class="small text-muted text-truncate" style="max-width: 300px;" title="{{ $notification->message }}">
                                                        {{ $notification->message }}
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 mt-1 d-lg-none">
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
                                                        <small class="text-muted">{{ $notification->time_ago }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                <span class="badge bg-secondary small">{{ $notification->type }}</span>
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                <span class="badge bg-{{ $priorityColors[$notification->priority] }} small">
                                                    {{ ucfirst($notification->priority) }}
                                                </span>
                                            </td>
                                            <td class="d-none d-sm-table-cell">
                                                @if($notification->is_read)
                                                    <span class="badge bg-success small">Đã đọc</span>
                                                @else
                                                    <span class="badge bg-warning small">Chưa đọc</span>
                                                @endif
                                            </td>
                                            <td class="d-none d-lg-table-cell">
                                                <div class="small text-muted">
                                                    {{ $notification->time_ago }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="{{ route('admin.notifications.show', $notification->id) }}" 
                                                       class="btn btn-outline-primary" 
                                                       title="Xem chi tiết">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if(!$notification->is_read)
                                                        <button type="button" 
                                                                class="btn btn-outline-success mark-read-btn" 
                                                                data-id="{{ $notification->id }}"
                                                                title="Đánh dấu đã đọc">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" 
                                                            class="btn btn-outline-danger delete-btn" 
                                                            data-id="{{ $notification->id }}"
                                                            title="Xóa">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Phân trang -->
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <div class="small text-muted">
                                Hiển thị {{ $notifications->firstItem() ?? 0 }} - {{ $notifications->lastItem() ?? 0 }} 
                                trong tổng số {{ $notifications->total() }} thông báo
                            </div>
                            <div id="paginationContainer">
                                {{ $notifications->appends(request()->query())->links() }}
                            </div>
                        </div>
                    @else
                        <div class="empty-state">
                            <i class="fas fa-bell-slash"></i>
                            <h5>Không có thông báo</h5>
                            <p>Chưa có thông báo nào phù hợp với bộ lọc hiện tại.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    let currentFilters = {
        type: '{{ $type }}',
        priority: '{{ $priority }}',
        is_read: '{{ $isRead }}',
        page: 1
    };

    // Hàm load danh sách thông báo bằng AJAX
    function loadNotifications(filters = {}) {
        const params = { ...currentFilters, ...filters };
        
        // Hiển thị loading
        $('#notificationsTable').html(`
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <p class="mt-2 text-muted">Đang tải danh sách thông báo...</p>
            </div>
        `);

        $.get('{{ route("admin.notifications.list") }}', params)
            .done(function(response) {
                if (response.success) {
                    renderNotifications(response.notifications, response.pagination);
                    updateFilters(response.filters);
                } else {
                    showError('Có lỗi xảy ra khi tải danh sách thông báo');
                }
            })
            .fail(function() {
                showError('Không thể kết nối đến máy chủ');
            });
    }

    // Hàm render danh sách thông báo
    function renderNotifications(notifications, pagination) {
        if (notifications.length === 0) {
            $('#notificationsTable').html(`
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <h5>Không có thông báo</h5>
                    <p>Chưa có thông báo nào phù hợp với bộ lọc hiện tại.</p>
                </div>
            `);
            return;
        }

        const priorityColors = {
            'urgent': 'danger',
            'high': 'warning',
            'normal': 'primary',
            'low': 'secondary'
        };

        let tableHtml = `
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 50px;" class="d-none d-md-table-cell">#</th>
                            <th style="width: 60px;">Icon</th>
                            <th>Nội dung</th>
                            <th style="width: 100px;" class="d-none d-lg-table-cell">Loại</th>
                            <th style="width: 80px;" class="d-none d-md-table-cell">Ưu tiên</th>
                            <th style="width: 80px;" class="d-none d-sm-table-cell">Trạng thái</th>
                            <th style="width: 120px;" class="d-none d-lg-table-cell">Thời gian</th>
                            <th style="width: 100px;">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        notifications.forEach(function(notification) {
            tableHtml += `
                <tr class="notification-row" data-id="${notification.id}">
                    <td class="d-none d-md-table-cell">${notification.id}</td>
                    <td>
                        <div class="icon-circle bg-${notification.color}" style="width: 35px; height: 35px;">
                            <i class="${notification.display_icon} text-white" style="font-size: 0.8rem;"></i>
                        </div>
                    </td>
                    <td>
                        <div class="notification-content">
                            <div class="fw-bold text-truncate" style="max-width: 300px;" title="${notification.title}">
                                ${notification.title}
                            </div>
                            <div class="small text-muted text-truncate" style="max-width: 300px;" title="${notification.message}">
                                ${notification.message}
                            </div>
                            <div class="d-flex align-items-center gap-2 mt-1 d-lg-none">
                                <span class="badge bg-${priorityColors[notification.priority]} small">
                                    ${notification.priority.charAt(0).toUpperCase() + notification.priority.slice(1)}
                                </span>
                                ${notification.is_read ? 
                                    '<span class="badge bg-success small">Đã đọc</span>' : 
                                    '<span class="badge bg-warning small">Chưa đọc</span>'
                                }
                                <small class="text-muted">${notification.time_ago}</small>
                            </div>
                        </div>
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <span class="badge bg-secondary small">${notification.type}</span>
                    </td>
                    <td class="d-none d-md-table-cell">
                        <span class="badge bg-${priorityColors[notification.priority]} small">
                            ${notification.priority.charAt(0).toUpperCase() + notification.priority.slice(1)}
                        </span>
                    </td>
                    <td class="d-none d-sm-table-cell">
                        ${notification.is_read ? 
                            '<span class="badge bg-success small">Đã đọc</span>' : 
                            '<span class="badge bg-warning small">Chưa đọc</span>'
                        }
                    </td>
                    <td class="d-none d-lg-table-cell">
                        <div class="small text-muted">
                            ${notification.time_ago}
                        </div>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="${notification.show_url}" 
                               class="btn btn-outline-primary" 
                               title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </a>
                            ${!notification.is_read ? `
                                <button type="button" 
                                        class="btn btn-outline-success mark-read-btn" 
                                        data-id="${notification.id}"
                                        title="Đánh dấu đã đọc">
                                    <i class="fas fa-check"></i>
                                </button>
                            ` : ''}
                            <button type="button" 
                                    class="btn btn-outline-danger delete-btn" 
                                    data-id="${notification.id}"
                                    title="Xóa">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

        tableHtml += `
                    </tbody>
                </table>
            </div>
        `;

        // Thêm phân trang
        if (pagination.last_page > 1) {
            tableHtml += `
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <div class="small text-muted">
                        Hiển thị ${pagination.from} - ${pagination.to} 
                        trong tổng số ${pagination.total} thông báo
                    </div>
                    <div id="paginationContainer">
                        ${generatePagination(pagination)}
                    </div>
                </div>
            `;
        }

        $('#notificationsTable').html(tableHtml);
        $('#notificationCount').text(pagination.total);
        
        // Re-attach event handlers
        attachEventHandlers();
    }

    // Hàm tạo phân trang
    function generatePagination(pagination) {
        let paginationHtml = '<ul class="pagination pagination-sm mb-0">';
        
        // Previous button
        if (pagination.current_page > 1) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="#" data-page="${pagination.current_page - 1}">Trước</a>
            </li>`;
        }

        // Page numbers
        for (let i = 1; i <= pagination.last_page; i++) {
            if (i === pagination.current_page) {
                paginationHtml += `<li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>`;
            } else {
                paginationHtml += `<li class="page-item">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>`;
            }
        }

        // Next button
        if (pagination.current_page < pagination.last_page) {
            paginationHtml += `<li class="page-item">
                <a class="page-link" href="#" data-page="${pagination.current_page + 1}">Sau</a>
            </li>`;
        }

        paginationHtml += '</ul>';
        return paginationHtml;
    }

    // Hàm cập nhật filters
    function updateFilters(filters) {
        currentFilters = { ...currentFilters, ...filters };
        
        // Cập nhật form
        $('#type').val(filters.type || '');
        $('#priority').val(filters.priority || '');
        $('#is_read').val(filters.is_read || '');
        
        // Cập nhật URL mà không reload trang
        const url = new URL(window.location);
        if (filters.type) url.searchParams.set('type', filters.type);
        else url.searchParams.delete('type');
        if (filters.priority) url.searchParams.set('priority', filters.priority);
        else url.searchParams.delete('priority');
        if (filters.is_read !== undefined && filters.is_read !== '') url.searchParams.set('is_read', filters.is_read);
        else url.searchParams.delete('is_read');
        url.searchParams.delete('page');
        
        window.history.pushState({}, '', url);
    }

    // Hàm hiển thị lỗi
    function showError(message) {
        $('#notificationsTable').html(`
            <div class="text-center py-5">
                <i class="fas fa-exclamation-triangle text-warning" style="font-size: 3rem;"></i>
                <h5 class="mt-3">Có lỗi xảy ra</h5>
                <p class="text-muted">${message}</p>
                <button class="btn btn-primary btn-sm" onclick="location.reload()">
                    <i class="fas fa-redo"></i> Thử lại
                </button>
            </div>
        `);
    }

    // Hàm attach event handlers
    function attachEventHandlers() {
        // Mark as read
        $('.mark-read-btn').on('click', function() {
            const id = $(this).data('id');
            const btn = $(this);
            
            $.post('{{ route("admin.notifications.mark-read") }}', { notification_id: id })
                .done(function(response) {
                    if (response.success) {
                        btn.closest('tr').find('.badge').removeClass('bg-warning').addClass('bg-success').text('Đã đọc');
                        btn.remove();
                        showToast('Đã đánh dấu thông báo đã đọc', 'success');
                    }
                })
                .fail(function() {
                    showToast('Có lỗi xảy ra', 'danger');
                });
        });

        // Delete notification
        $('.delete-btn').on('click', function() {
            const id = $(this).data('id');
            const row = $(this).closest('tr');
            
            if (confirm('Bạn có chắc chắn muốn xóa thông báo này?')) {
                $.ajax({
                    url: '{{ route("admin.notifications.delete") }}',
                    type: 'DELETE',
                    data: { notification_id: id },
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                })
                .done(function(response) {
                    if (response.success) {
                        row.fadeOut(300, function() {
                            $(this).remove();
                            loadNotifications(); // Reload để cập nhật số lượng
                        });
                        showToast('Đã xóa thông báo', 'success');
                    }
                })
                .fail(function() {
                    showToast('Có lỗi xảy ra', 'danger');
                });
            }
        });

        // Pagination
        $(document).on('click', '.pagination .page-link', function(e) {
            e.preventDefault();
            const page = $(this).data('page');
            if (page) {
                loadNotifications({ page: page });
            }
        });
    }

    // Hàm hiển thị toast
    function showToast(message, type = 'info') {
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

        $('.toast-container').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();

        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    // Event handlers
    // Click vào stats cards
    $('.stats-card').on('click', function() {
        const filterType = $(this).data('filter');
        
        // Reset page về 1 khi filter
        loadNotifications({ 
            type: filterType,
            page: 1
        });
        
        // Highlight card được chọn
        $('.stats-card').removeClass('active border-primary').addClass('border-secondary');
        $(this).removeClass('border-secondary').addClass('active border-primary');
        
        // Thêm hiệu ứng ripple
        $(this).addClass('ripple-effect');
        setTimeout(() => {
            $(this).removeClass('ripple-effect');
        }, 600);
    });

    // Hover effect cho stats cards
    $('.stats-card').on('mouseenter', function() {
        if (!$(this).hasClass('active')) {
            $(this).addClass('hover-effect');
        }
    }).on('mouseleave', function() {
        $(this).removeClass('hover-effect');
    });

    // Submit form filter
    $('#filterForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = $(this).serializeArray();
        const filters = {};
        
        formData.forEach(function(item) {
            if (item.value) {
                filters[item.name] = item.value;
            }
        });
        
        // Reset page về 1 khi filter
        filters.page = 1;
        
        loadNotifications(filters);
    });

    // Clear filter
    $('#clearFilterBtn').on('click', function() {
        $('#filterForm')[0].reset();
        loadNotifications({ 
            type: '',
            priority: '',
            is_read: '',
            page: 1
        });
        
        // Reset stats cards highlight
        $('.stats-card').removeClass('border-primary').addClass('border-secondary');
    });

    // Mark all as read
    $('#markAllReadBtn').on('click', function() {
        if (confirm('Bạn có chắc chắn muốn đánh dấu tất cả thông báo đã đọc?')) {
            $.post('{{ route("admin.notifications.mark-all-read") }}')
                .done(function(response) {
                    if (response.success) {
                        loadNotifications(); // Reload để cập nhật trạng thái
                        showToast(response.message, 'success');
                    }
                })
                .fail(function() {
                    showToast('Có lỗi xảy ra', 'danger');
                });
        }
    });

    // Delete read notifications
    $('#deleteReadBtn').on('click', function() {
        if (confirm('Bạn có chắc chắn muốn xóa tất cả thông báo đã đọc?')) {
            $.ajax({
                url: '{{ route("admin.notifications.delete-read") }}',
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                if (response.success) {
                    loadNotifications(); // Reload để cập nhật danh sách
                    showToast(response.message, 'success');
                }
            })
            .fail(function() {
                showToast('Có lỗi xảy ra', 'danger');
            });
        }
    });

    // Delete old notifications
    $('#deleteOldBtn').on('click', function() {
        if (confirm('Bạn có chắc chắn muốn xóa tất cả thông báo cũ (quá 30 ngày)?')) {
            $.ajax({
                url: '{{ route("admin.notifications.delete-old") }}',
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            })
            .done(function(response) {
                if (response.success) {
                    loadNotifications(); // Reload để cập nhật danh sách
                    showToast(response.message, 'success');
                }
            })
            .fail(function() {
                showToast('Có lỗi xảy ra', 'danger');
            });
        }
    });

    // Test buttons
    $('#testNoteBtn').on('click', function() {
        $.post('{{ route("admin.notifications.test-note") }}')
            .done(function(response) {
                if (response.success) {
                    loadNotifications(); // Reload để hiển thị thông báo mới
                    showToast(response.message, 'success');
                }
            })
            .fail(function() {
                showToast('Có lỗi xảy ra', 'danger');
            });
    });

    $('#testReviewBtn').on('click', function() {
        $.post('{{ route("admin.notifications.test-review") }}')
            .done(function(response) {
                if (response.success) {
                    loadNotifications(); // Reload để hiển thị thông báo mới
                    showToast(response.message, 'success');
                }
            })
            .fail(function() {
                showToast('Có lỗi xảy ra', 'danger');
            });
    });

    // Initial load
    attachEventHandlers();
});
</script>
@endsection 