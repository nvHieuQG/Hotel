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
    @stack('styles')
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
        
        /* Notification dropdown table styling */
        .notification-table {
            margin-bottom: 0;
        }
        
        .notification-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .notification-table tbody tr:nth-child(odd) {
            background-color: #ffffff;
        }
        
        .notification-table tbody tr:hover {
            background-color: #e9ecef;
        }
        
        .notification-table th {
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: #6c757d;
            border-bottom: 2px solid #dee2e6;
        }
        
        .notification-table td {
            padding: 0.4rem 0.5rem;
            border: none;
            vertical-align: middle;
        }
        
        .notification-table tbody tr {
            height: 45px;
        }
        
        .icon-circle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            width: 28px;
            height: 28px;
        }
        
        .notification-row {
            transition: background-color 0.2s ease;
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
                <div class="dropdown-menu dropdown-menu-end animate slideIn" aria-labelledby="notificationsDropdown" style="width: 500px; max-height: 600px;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="m-0">
                            <i class="fas fa-bell me-2"></i>Thông báo
                        </h6>
                    </div>
                    <div id="notificationsList">
                        @if(isset($unreadCount) && $unreadCount > 0 && isset($unreadNotifications))
                            <!-- Table Header -->
                            <div class="table-responsive">
                                <table class="table table-sm mb-0 notification-table">
                                    <thead class="table-light">
                                        <tr>
                                            {{-- <th class="text-center" style="width: 50px;">#</th> --}}
                                            <th class="text-center" style="width: 60px;">ICON</th>
                                            <th>NỘI DUNG</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($unreadNotifications->take(15) as $index => $notification)
                                            @php
                                                $link = null;
                                                // Thông báo ghi chú hoặc bất kỳ thông báo nào liên quan đến booking
                                                if (str_contains($notification->type, 'note') || 
                                                    str_contains($notification->type, 'booking') ||
                                                    (isset($notification->data['booking_id']) && $notification->data['booking_id'])) {
                                                    $bookingId = $notification->data['booking_id'] ?? null;
                                                    if ($bookingId) {
                                                        $link = route('admin.bookings.show', $bookingId);
                                                    }
                                                } 
                                                // Thông báo đánh giá
                                                elseif (str_contains($notification->type, 'review')) {
                                                    $reviewId = $notification->data['review_id'] ?? null;
                                                    if ($reviewId) {
                                                        $link = route('admin.room-type-reviews.show', $reviewId);
                                                    }
                                                } 
                                                // Thông báo liên quan đến phòng
                                                elseif (str_contains($notification->type, 'room')) {
                                                    $roomId = $notification->data['room_id'] ?? null;
                                                    if ($roomId) {
                                                        $link = route('admin.rooms.show', $roomId);
                                                    }
                                                }
                                                
                                                // Nếu không có link cụ thể, không chuyển hướng
                                                if (!$link) {
                                                    $link = '#';
                                                }
                                            @endphp
                                            <tr class="notification-row" data-notification-id="{{ $notification->id }}" style="cursor: pointer;">
                                                {{-- <td class="text-center align-middle">
                                                    <span class="text-muted">{{ $notification->id }}</span>
                                                </td> --}}
                                                <td class="text-center align-middle">
                                                    <div class="icon-circle bg-{{ $notification->color ?? 'primary' }}" style="width: 28px; height: 28px; margin: 0 auto;">
                                                        <i class="{{ $notification->display_icon ?? 'fas fa-bell' }} text-white" style="font-size: 11px;"></i>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    @if($link !== '#')
                                                        <a href="{{ $link }}" class="text-decoration-none text-dark">
                                                    @endif
                                                        <div class="fw-bold" style="font-size: 0.8rem; line-height: 1.2; margin-bottom: 2px;" title="{{ $notification->title }}">
                                                            {{ $notification->title }}
                                                        </div>
                                                        <div class="text-muted text-truncate" style="font-size: 0.75rem; line-height: 1.1;" title="{{ $notification->message ?? $notification->data['message'] ?? '' }}">
                                                            {{ Str::limit($notification->message ?? $notification->data['message'] ?? '', 60) }}
                                                        </div>
                                                    @if($link !== '#')
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($unreadNotifications->count() > 15)
                                <div class="dropdown-divider"></div>
                                <div class="dropdown-item text-center small text-muted">
                                    Và {{ $unreadNotifications->count() - 15 }} thông báo khác...
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
                        {{ \App\Models\SupportMessage::where('sender_type', 'user')->where('is_read', false)->count() }}
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end animate slideIn" aria-labelledby="messagesDropdown">
                    <div class="dropdown-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="m-0">
                            <i class="fas fa-envelope me-2"></i>Tin nhắn hỗ trợ
                        </h6>
                    </div>
                    <div id="messagesList">
                        @php
                            $conversations = \App\Models\SupportMessage::select('conversation_id', 'subject', 'sender_id', 'message', 'created_at')
                                ->with('user')
                                ->whereIn('id', function($query) {
                                    $query->select(\DB::raw('MAX(id)'))
                                        ->from('support_messages')
                                        ->groupBy('conversation_id');
                                })
                                ->orderByDesc('created_at')
                                ->take(3)
                                ->get();
                        @endphp
                        @if($conversations->count() > 0)
                            @foreach($conversations as $conversation)
                                @php
                                    $unreadCount = \App\Models\SupportMessage::where('conversation_id', $conversation->conversation_id)
                                        ->where('sender_type', 'user')
                                        ->where('is_read', false)
                                        ->count();
                                    $iconColor = $unreadCount > 0 ? 'bg-danger' : 'bg-primary';
                                @endphp
                                <div class="dropdown-item notification-item d-flex align-items-start justify-content-between gap-2">
                                    <a href="{{ route('admin.support.showConversation', $conversation->conversation_id) }}" class="flex-grow-1 text-decoration-none text-dark">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="icon-circle {{ $iconColor }}">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold small text-truncate" title="{{ $conversation->user->name ?? 'Khách' }}">
                                                    {{ $conversation->user->name ?? 'Khách' }}
                                                    @if($unreadCount > 0)
                                                        <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">{{ $unreadCount }}</span>
                                                    @endif
                                                </div>
                                                <div class="small text-muted text-truncate mb-1" title="{{ $conversation->message }}">
                                                    {{ Str::limit($conversation->message, 30) }}
                                                </div>
                                                <div class="small text-gray-500">
                                                    <i class="fas fa-clock me-1"></i>{{ \Carbon\Carbon::parse($conversation->created_at)->diffForHumans() }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <div class="dropdown-item text-center small text-gray-500 py-3">
                                <i class="fas fa-check-circle text-success me-2"></i> Không có tin nhắn mới
                            </div>
                        @endif
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center small text-gray-500 py-2" href="{{ route('admin.support.index') }}">
                        <i class="fas fa-list me-1"></i>Xem tất cả tin nhắn
                    </a>
                </div>
            </div>

            <div class="topbar-divider"></div>

            <!-- User Information Dropdown -->
            <div class="topbar-item dropdown">
                <a class="user-profile" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name ?? 'User') }}&background=random" alt="Avatar">
                    <div class="user-profile-info d-none d-md-block">
                        <div class="user-profile-name">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="user-profile-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->role->name ?? '')) }}</div>
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
            @php $currentRole = auth()->user()->role->name ?? null; @endphp
            @if($currentRole === 'staff')
                <ul class="nav flex-column px-3 mt-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }}"
                            href="{{ route('staff.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('staff.bookings.*') ? 'active' : '' }}"
                            href="{{ route('staff.bookings.index') }}">
                            <i class="fas fa-calendar-check"></i> Đặt phòng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('staff.room-changes.*') ? 'active' : '' }}"
                            href="{{ route('staff.room-changes.index') }}">
                            <i class="fas fa-exchange-alt"></i> Yêu cầu đổi phòng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('staff.tour-bookings.*') ? 'active' : '' }}"
                            href="{{ route('staff.tour-bookings.index') }}">
                            <i class="fas fa-route"></i> Tour
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('staff.support.*') ? 'active' : '' }}"
                            href="{{ route('staff.support.index') }}">
                            <i class="fas fa-headset"></i> Hỗ trợ
                        </a>
                    </li>
                </ul>
            @else
                <ul class="nav flex-column px-3 mt-4">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <i class="fas fa-tachometer-alt"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ (request()->routeIs('admin.bookings.*') && !request()->routeIs('admin.bookings.report')) ? 'active' : '' }}"
                            href="{{ route('admin.bookings.index') }}">
                            <i class="fas fa-calendar-check"></i> Đặt phòng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.room-changes.*') ? 'active' : '' }}"
                            href="{{ route('admin.room-changes.index') }}">
                            <i class="fas fa-exchange-alt"></i> Yêu cầu đổi phòng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.tour-bookings.*') ? 'active' : '' }}"
                            href="{{ route('admin.tour-bookings.index') }}">
                            <i class="fas fa-route"></i> Tour 
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.rooms.*') || request()->routeIs('admin.room-type-services.*') || request()->routeIs('admin.service-categories.*') ? '' : 'collapsed' }}"
                            data-bs-toggle="collapse" href="#submenuRoom" role="button"
                            aria-expanded="{{ request()->routeIs('admin.rooms.*') || request()->routeIs('admin.room-type-services.*') || request()->routeIs('admin.service-categories.*') ? 'true' : 'false' }}"
                            aria-controls="submenuRoom">
                            <span><i class="fas fa-bed"></i> Phòng</span>
                            <i class="fas fa-chevron-down small {{ request()->routeIs('admin.rooms.*') || request()->routeIs('admin.room-type-services.*') || request()->routeIs('admin.service-categories.*') ? 'rotate-180' : '' }}"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.rooms.*') || request()->routeIs('admin.room-type-services.*') || request()->routeIs('admin.service-categories.*') ? 'show' : '' }}" id="submenuRoom">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.rooms.index') ? 'active' : '' }}" href="{{ route('admin.rooms.index') }}">
                                        Danh sách phòng
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.room-type-services.index') ? 'active' : '' }}" href="{{ route('admin.room-type-services.index') }}">
                                        Dịch vụ loại phòng
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.service-categories.index') ? 'active' : '' }}" href="{{ route('admin.service-categories.index') }}">
                                        Danh mục dịch vụ
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.promotions.*') ? 'active' : '' }}" href="{{ route('admin.promotions.index') }}">
                            <i class="fas fa-gift"></i> Khuyến mãi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.room-type-reviews.*') ? 'active' : '' }}" href="{{ route('admin.room-type-reviews.index') }}">
                            <i class="fas fa-star"></i> Đánh giá
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                            <i class="fas fa-users-cog"></i> Người dùng
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link d-flex justify-content-between align-items-center {{ request()->routeIs('admin.bookings.report') || request()->routeIs('admin.reports.extra-services') ? '' : 'collapsed' }}"
                           data-bs-toggle="collapse" href="#submenuReports" role="button"
                           aria-expanded="{{ request()->routeIs('admin.bookings.report') || request()->routeIs('admin.reports.extra-services') ? 'true' : 'false' }}"
                           aria-controls="submenuReports">
                            <span><i class="fas fa-chart-bar"></i> Báo cáo</span>
                            <i class="fas fa-chevron-down small {{ request()->routeIs('admin.bookings.report') || request()->routeIs('admin.reports.extra-services') ? 'rotate-180' : '' }}"></i>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.bookings.report') || request()->routeIs('admin.reports.extra-services') ? 'show' : '' }}" id="submenuReports">
                            <ul class="nav flex-column ms-3">
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.bookings.report') ? 'active' : '' }}" href="{{ route('admin.bookings.report') }}">
                                        Tổng quan đặt phòng
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ request()->routeIs('admin.reports.extra-services') ? 'active' : '' }}" href="{{ route('admin.reports.extra-services') }}">
                                        Báo cáo dịch vụ
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                            <i class="fas fa-bell"></i> Thông báo
                            @if(isset($unreadCount) && $unreadCount > 0)
                                <span class="badge bg-danger ms-auto" id="sidebarNotificationBadge">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.support.*') ? 'active' : '' }}" href="{{ route('admin.support.index') }}">
                            <i class="fas fa-headset"></i> Hỗ trợ
                            @php $unreadCount = \App\Models\SupportMessage::where('sender_type', 'user')->where('is_read', false)->count(); @endphp
                            @if($unreadCount > 0)
                                <span class="badge bg-danger ms-2">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item mt-5">
                        <a class="nav-link" href="{{ route('index') }}" target="_blank">
                            <i class="fas fa-external-link-alt"></i> Xem trang chủ
                        </a>
                    </li>
                </ul>
            @endif
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

    <!-- Debug script -->
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
                
                // Xử lý click cho notification rows mới
                $(document).on('click', '#notificationsList .notification-row', function(e) {
                    e.preventDefault();
                    const notificationId = $(this).data('notification-id');
                    const href = $(this).find('a').attr('href');
                    
                    // Kiểm tra nếu href là '#' thì không chuyển hướng
                    if (href === '#') {
                        // Chỉ đánh dấu đã đọc mà không chuyển hướng
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
                                    
                                    // Ẩn notification row này
                                    $(this).closest('.notification-row').fadeOut();
                                }
                            }
                        });
                        return;
                    }
                    
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
                                
                                // Chuyển hướng đến trang tương ứng
                                window.location.href = href;
                            }
                        },
                        error: function() {
                            // Nếu có lỗi, vẫn chuyển hướng
                            window.location.href = href;
                        }
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
            console.log('Document ready in layout');
            console.log('Notification elements:', {
                badge: $('#notificationBadge').length,
                list: $('#notificationsList').length,
                markAllBtn: $('#markAllReadBtn').length,
                sidebarBadge: $('#sidebarNotificationBadge').length,
            });

            // Test API call
            $.ajax({
                url: '/admin/api/notifications/unread',
                method: 'GET',
                dataType: 'json',
                success: function(response) {
                    console.log('API test successful:', response);
                    if (response.success) {
                        console.log('Found', response.count, 'unread notifications');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('API test failed:', {xhr, status, error});
                }
            });

            // Test notification manager
            setTimeout(function() {
                if (window.notificationManager) {
                    console.log('NotificationManager is available');
                } else {
                    console.error('NotificationManager not found!');
                }
            }, 1000);
        });
    </script>

    @yield('scripts')
    @stack('scripts')

</body>

</html>
