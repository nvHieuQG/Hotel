<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - Marron Hotel Management</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Admin Main CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/admin-main.css') }}">
    <!-- Admin Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/admin-responsive.css') }}">
    <!-- Admin Components CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/admin-components.css') }}">
    @yield('styles')
    <style>
        .page-header h1 {
            font-size: 1.7rem;
            font-weight: 700;
            margin-bottom: 0.1rem;
        }
        
        /* Xử lý thông báo */
        .alert-container {
            position: relative;
            z-index: 1000;
        }
        
        .alert {
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .alert-success {
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            border-left: 4px solid #dc3545;
        }
        
        .alert-warning {
            border-left: 4px solid #ffc107;
        }
        
        .alert-info {
            border-left: 4px solid #17a2b8;
        }
        
        /* Đảm bảo chỉ hiển thị một thông báo */
        .alert-container .alert:not(:first-child) {
            display: none;
        }
    </style>
</head>

<body>


    <!-- Top Navbar -->
    <nav class="topbar">

        <div class="topbar-search">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Tìm kiếm...">
        </div>

        <div class="topbar-menu">
            <div class="topbar-divider"></div>

            <!-- Notifications Dropdown -->
            <div class="topbar-item dropdown">
                <a class="topbar-icon" href="#" id="notificationsDropdown" role="button"
                    data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <span class="topbar-badge" id="notificationBadge">{{ isset($unreadCount) && $unreadCount > 0 ? $unreadCount : 0 }}</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end animate slideIn" aria-labelledby="notificationsDropdown">
                    <div class="dropdown-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="m-0">
                            <i class="fas fa-bell me-2"></i>Thông báo
                        </h6>
                    </div>
                    <div id="notificationsList">
                        @if(isset($unreadCount) && $unreadCount > 0 && isset($unreadNotifications))
                            @foreach($unreadNotifications->take(5) as $notification)
                                <div class="dropdown-item notification-item" data-notification-id="{{ $notification->id }}">
                                    <a href="{{ route('admin.notifications.show', $notification->id) }}" class="text-decoration-none text-dark">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="icon-circle bg-{{ $notification->color }}">
                                                <i class="{{ $notification->display_icon }} text-white"></i>
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold small text-truncate" title="{{ $notification->title }}">{{ $notification->title }}</div>
                                                <div class="small text-gray-500">{{ $notification->time_ago }}</div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                            @if($unreadNotifications->count() > 5)
                                <div class="dropdown-divider"></div>
                                <div class="dropdown-item text-center small text-muted">
                                    Và {{ $unreadNotifications->count() - 5 }} thông báo khác...
                                </div>
                            @endif
                        @else
                            <div class="dropdown-item text-center small text-gray-500 py-3">
                                <i class="fas fa-check-circle text-success me-2"></i> Không có thông báo mới
                            </div>
                        @endif
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center small text-gray-500 py-2" href="{{ route('admin.notifications.index') }}">
                        <i class="fas fa-list me-1"></i>Xem tất cả thông báo
                    </a>
                </div>
            </div>

            <!-- Messages Dropdown -->
            <div class="topbar-item dropdown">
                <a class="topbar-icon" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <i class="fas fa-envelope"></i>
                    <span class="topbar-badge">
                        {{ \App\Models\SupportTicket::with('messages')->whereHas('messages')->count() }}
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end animate slideIn" aria-labelledby="messagesDropdown">
                    <h6 class="dropdown-header">Tin nhắn hỗ trợ</h6>
                    @php
                        $tickets = \App\Models\SupportTicket::with([
                            'user',
                            'messages' => function ($q) {
                                $q->latest();
                            },
                        ])
                            ->whereHas('messages')
                            ->latest('updated_at')
                            ->take(5)
                            ->get();
                    @endphp
                    @forelse($tickets as $ticket)
                        <a class="dropdown-item" href="{{ route('admin.support.showTicket', $ticket->id) }}">
                            <div class="dropdown-item-message">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->user->name ?? 'Khach') }}&background=random"
                                    alt="{{ $ticket->user->name ?? 'Khách' }}">
                                <div class="dropdown-item-message-content">
                                    <div class="dropdown-item-message-title">{{ $ticket->user->name ?? 'Khách' }}</div>
                                    <p class="dropdown-item-message-text">
                                        {{ optional($ticket->messages->first())->message ?? '...' }}</p>
                                    <div class="dropdown-item-message-time">
                                        {{ optional($ticket->messages->first())->created_at ? optional($ticket->messages->first())->created_at->diffForHumans() : '' }}
                                    </div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="dropdown-item text-center small text-gray-500">Không có tin nhắn mới</div>
                    @endforelse
                    <a class="dropdown-item text-center small text-gray-500"
                        href="{{ route('admin.support.index') }}">Xem tất cả tin nhắn</a>
                </div>
            </div>

            <div class="topbar-divider"></div>

            <!-- User Information Dropdown -->
            <div class="topbar-item dropdown">
                <a class="user-profile" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name=Admin&background=random" alt="Admin">
                    <div class="user-profile-info d-none d-md-block">
                        <div class="user-profile-name">Admin</div>
                        <div class="user-profile-role">Quản trị viên</div>
                    </div>
                </a>
                <div class="dropdown-menu dropdown-menu-end animate slideIn" aria-labelledby="userDropdown">
                    <h6 class="dropdown-header">Tài khoản</h6>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-user"></i> Hồ sơ
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-cogs"></i> Cài đặt
                    </a>
                    <a class="dropdown-item" href="#">
                        <i class="fas fa-list"></i> Hoạt động
                    </a>
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-sign-out-alt"></i> Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <!-- Sidebar -->
    <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="position-sticky pt-3">
            <div class="navbar-brand text-center py-4">
                <i class="fas fa-hotel me-2"></i> Marron Hotel
            </div>
            <hr class="mx-3 opacity-25">
            <ul class="nav flex-column px-3 mt-4">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}"
                        href="{{ route('admin.bookings.index') }}">
                        <i class="fas fa-calendar-check"></i> Đặt phòng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.rooms.*') || request()->routeIs('admin.room-type-services.*') || request()->routeIs('admin.service-categories.*') ? '' : 'collapsed' }}"
                        data-bs-toggle="collapse" href="#submenuRoom" role="button"
                        aria-expanded="{{ request()->routeIs('admin.rooms.*') || request()->routeIs('admin.room-type-services.*') || request()->routeIs('admin.service-categories.*') ? 'true' : 'false' }}"
                        aria-controls="submenuRoom">
                        <span><i class="fas fa-bed"></i> Phòng</span>
                        <i
                            class="fas fa-chevron-down small {{ request()->routeIs('admin.rooms.*') || request()->routeIs('admin.room-type-services.*') || request()->routeIs('admin.service-categories.*') ? 'rotate-180' : '' }}"></i>
                    </a>
                    <div class="collapse {{ request()->routeIs('admin.rooms.*') || request()->routeIs('admin.room-type-services.*') || request()->routeIs('admin.service-categories.*') ? 'show' : '' }}"
                        id="submenuRoom">
                        <ul class="nav flex-column ms-3">
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.rooms.index') ? 'active' : '' }}"
                                    href="{{ route('admin.rooms.index') }}">
                                    Danh sách phòng
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('admin.room-type-services.index') ? 'active' : '' }}"
                                    href="{{ route('admin.room-type-services.index') }}">
                                    Dịch vụ loại phòng
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}"
                        href="{{ route('admin.users.index') }}">
                        <i class="fas fa-users"></i> Người dùng
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.room-type-reviews.*') ? 'active' : '' }}"
                        href="{{ route('admin.room-type-reviews.index') }}">
                        <i class="fas fa-star"></i> Đánh giá
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.bookings.report') ? 'active' : '' }}"
                        href="{{ route('admin.bookings.report') }}">
                        <i class="fas fa-chart-bar"></i> Báo cáo
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                        <i class="fas fa-bell"></i> Thông báo
                        @if(isset($unreadCount) && $unreadCount > 0)
                            <span class="badge bg-danger ms-auto" id="sidebarNotificationBadge">{{ $unreadCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item mt-5">
                    <a class="nav-link" href="{{ route('index') }}" target="_blank">
                        <i class="fas fa-external-link-alt"></i> Xem trang chủ
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Main content -->
    <main class="main-content">
                <div class="page-header fade-in">
                    <h1>@yield('header', 'Dashboard')</h1>
                </div>

                <!-- Hiển thị thông báo -->
                <div class="alert-container">
                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert" id="success-alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" id="error-alert">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert" id="warning-alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert" id="info-alert">
                            <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                        </div>
                    @endif
                </div>

                <div class="content-wrapper fade-in">
                    @yield('content')
                </div>
            </main>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <!-- Toast notifications will be inserted here -->
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Admin Main JavaScript -->
    <script src="{{ asset('admin/js/admin-main.js') }}"></script>
    
    @yield('scripts')
    <style>
        .notification-item {
            padding: 0.5rem 0.75rem;
            border-bottom: 1px solid #f0f0f0;
            transition: background-color 0.2s;
        }
        
        .notification-item:hover {
            background-color: #f8f9fa;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-item a {
            display: block;
            width: 100%;
        }
        
        .icon-circle {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .dropdown-menu {
            max-width: 450px;
            min-width: 400px;
        }
        
        .dropdown-item {
            white-space: normal;
            word-wrap: break-word;
        }
    </style>
    <script>
        $(document).ready(function() {
            // Xử lý thông báo - đảm bảo chỉ hiển thị một thông báo
            $('.alert').each(function() {
                // Tự động ẩn thông báo sau 5 giây
                setTimeout(() => {
                    $(this).fadeOut();
                }, 5000);
                
                // Xử lý nút đóng thông báo
                $(this).find('.btn-close').on('click', function() {
                    $(this).closest('.alert').fadeOut();
                });
            });
            
            // Khi bấm chuông, load danh sách thông báo chưa đọc
            $('#notificationsDropdown').on('show.bs.dropdown', function () {
                $.get('/admin/api/notifications/unread', function(res) {
                    if (res.success) {
                        let html = '';
                        if (res.notifications.length > 0) {
                                                    // Chỉ hiển thị tối đa 5 thông báo
                        res.notifications.slice(0, 5).forEach(function(notification) {
                                                            html += `<div class="dropdown-item notification-item" data-notification-id="${notification.id}">
                                    <a href="/admin/notifications/${notification.id}" class="text-decoration-none text-dark">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-circle bg-${notification.color}">
                                            <i class="${notification.display_icon} text-white"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold small text-truncate" title="${notification.title}">${notification.title}</div>
                                            <div class="small text-gray-500">${notification.time_ago}</div>
                                        </div>
                                    </div>
                                </a>
                            </div>`;
                        });
                        
                        // Thêm thông báo nếu có nhiều hơn 5 thông báo
                        if (res.notifications.length > 5) {
                            html += `<div class="dropdown-divider"></div>
                                <div class="dropdown-item text-center small text-muted">
                                    Và ${res.notifications.length - 5} thông báo khác...
                                </div>`;
                        }
                    } else {
                        html = `<div class="dropdown-item text-center small text-gray-500 py-3">
                            <i class="fas fa-check-circle text-success me-2"></i> Không có thông báo mới
                        </div>`;
                    }
                    $('#notificationsList').html(html);
                    
                    // Thêm event listener cho việc click vào notification
                    $('#notificationsList .notification-item a').on('click', function(e) {
                        e.preventDefault();
                        const notificationId = $(this).closest('.notification-item').data('notification-id');
                        const href = $(this).attr('href');
                        
                        // Đánh dấu thông báo cụ thể này là đã đọc
                        $.ajax({
                            url: `/admin/api/notifications/${notificationId}/mark-read`,
                            type: 'PATCH',
                            data: {
                                _token: $('meta[name=csrf-token]').attr('content')
                            },
                            success: function(response) {
                                if (response.success) {
                                    // Cập nhật badge count
                                    const currentCount = parseInt($('#notificationBadge').text()) || 0;
                                    const newCount = Math.max(0, currentCount - 1);
                                    $('#notificationBadge').text(newCount);
                                    $('#sidebarNotificationBadge').text(newCount);
                                    
                                    // Chuyển hướng đến trang chi tiết thông báo
                                    window.location.href = href;
                                }
                            },
                            error: function() {
                                // Nếu có lỗi, vẫn chuyển hướng
                                window.location.href = href;
                            }
                        });
                    });
                });
                
                // Xử lý click cho notification items tĩnh (khi trang được load lần đầu)
                $(document).on('click', '#notificationsList .notification-item a', function(e) {
                    e.preventDefault();
                    const notificationId = $(this).closest('.notification-item').data('notification-id');
                    const href = $(this).attr('href');
                    
                    // Đánh dấu thông báo cụ thể này là đã đọc
                    $.ajax({
                        url: `/admin/api/notifications/${notificationId}/mark-read`,
                        type: 'PATCH',
                        data: {
                            _token: $('meta[name=csrf-token]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // Cập nhật badge count
                                const currentCount = parseInt($('#notificationBadge').text()) || 0;
                                const newCount = Math.max(0, currentCount - 1);
                                $('#notificationBadge').text(newCount);
                                $('#sidebarNotificationBadge').text(newCount);
                                
                                // Chuyển hướng đến trang chi tiết thông báo
                                window.location.href = href;
                            }
                        },
                        error: function() {
                            // Nếu có lỗi, vẫn chuyển hướng
                            window.location.href = href;
                        }
                    });
                });
                    }
                });
            });
        });
    </script>
</body>

</html>
