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
    <!-- Admin Reviews CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/reviews.css') }}">
    <!-- Admin Notifications CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/notifications.css') }}">
    <!-- Admin Responsive CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/responsive.css') }}">
    <!-- Full HD Optimization CSS -->
    <link rel="stylesheet" href="{{ asset('admin/css/fullhd-optimization.css') }}">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #8b7d6b;
            --secondary-color: #2c3e50;
            --accent-color: #c19b76;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --success-color: #28a745;
            --info-color: #17a2b8;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
        }

        h1, h2, h3, h4, h5, h6, .navbar-brand {
            font-family: 'Montserrat', sans-serif;
            font-weight: 600;
        }

        .sidebar {
            min-height: 100vh;
            background: linear-gradient(to bottom, var(--secondary-color), #1a252f);
            color: #fff;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
            z-index: 100;
            padding-top: 0;
        }

        .sidebar .navbar-brand {
            color: var(--accent-color);
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-top: 1rem;
            padding-bottom: 1rem;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.7);
            padding: 12px 15px;
            margin: 4px 0;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            color: #fff;
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateX(5px);
        }

        .sidebar .nav-link.active {
            color: #fff;
            background: linear-gradient(to right, var(--accent-color), rgba(155, 104, 52, 0.7));
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .sidebar .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 8px;
        }

        .main-content {
            padding: 25px;
            transition: all 0.3s;
            margin-left: 16.66667%;
            margin-top: 60px;
            min-height: calc(100vh - 60px);
        }

        /* Full HD (1080x1920) Optimizations */
        @media (min-width: 1920px) {
            .main-content {
                margin-left: 320px;
                padding: 2rem 2.5rem;
                min-height: calc(100vh - 80px);
            }

            .sidebar {
                width: 320px;
            }

            .topbar {
                left: 320px;
                height: 80px;
            }

            .page-header {
                padding: 2rem 2.5rem;
                margin-bottom: 2rem;
                border-radius: 12px;
            }

            .page-header h1 {
                font-size: 2.5rem;
            }

            .card {
                margin-bottom: 2rem;
                border-radius: 12px;
            }

            .card-header {
                padding: 1.5rem 2rem;
            }

            .card-header h6 {
                font-size: 1.3rem;
            }

            .card-body {
                padding: 2rem 2.5rem;
            }

            .table th {
                font-size: 0.9rem;
                padding: 1rem 1.5rem;
            }

            .table td {
                padding: 1rem 1.5rem;
                font-size: 0.95rem;
            }

            .btn {
                padding: 0.75rem 1.5rem;
                font-size: 0.95rem;
                border-radius: 8px;
            }

            .form-control, .form-select {
                padding: 0.75rem 1rem;
                font-size: 0.95rem;
                border-radius: 8px;
            }

            .alert {
                padding: 1.5rem 2rem;
                border-radius: 8px;
                font-size: 0.95rem;
            }

            .badge {
                font-size: 0.8rem;
                padding: 0.4rem 0.8rem;
                border-radius: 15px;
            }
        }

        /* Ultra-wide screen optimization */
        @media (min-width: 2560px) {
            .main-content {
                margin-left: 350px;
                padding: 2.5rem 3rem;
            }

            .sidebar {
                width: 350px;
            }

            .topbar {
                left: 350px;
            }

            .page-header h1 {
                font-size: 3rem;
            }

            .card-body {
                padding: 2.5rem 3rem;
            }

            .table th,
            .table td {
                padding: 1.25rem 2rem;
                font-size: 1rem;
            }
        }

        .page-header {
            background: linear-gradient(to right, var(--secondary-color), var(--primary-color));
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .page-header::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 100%;
            background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.1));
            transform: skewX(-30deg);
        }

        .page-header h1 {
            font-weight: 700;
            margin: 0;
            font-size: 2rem;
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            margin-bottom: 25px;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(to right, var(--accent-color), rgba(193, 155, 118, 0.7));
            color: white;
            font-weight: 600;
            border: none;
            padding: 15px 20px;
        }

        .card-header h6 {
            margin: 0;
            font-size: 1.1rem;
        }

        .card-body {
            padding: 20px;
        }

        .btn {
            border-radius: 5px;
            padding: 8px 16px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-primary:hover {
            background-color: #a67c5b;
            border-color: #a67c5b;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table th {
            background-color: #f8f9fa;
            border-top: none;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table td, .table th {
            padding: 15px;
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        .badge {
            padding: 7px 12px;
            font-weight: 500;
            border-radius: 30px;
        }

        .form-control, .form-select {
            border-radius: 5px;
            padding: 10px 15px;
            border: 1px solid #ced4da;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(193, 155, 118, 0.25);
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .alert-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .alert-info {
            background-color: #d1ecf1;
            color: #0c5460;
        }

        /* Navbar styles */
        .topbar {
            position: fixed;
            top: 0;
            left: 16.66667%;
            right: 0;
            height: 60px;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            display: flex;
            align-items: center;
            padding: 0 20px;
        }

        .topbar-search {
            position: relative;
            width: 350px;
        }

        .topbar-search input {
            background-color: #f5f5f5;
            border: none;
            border-radius: 30px;
            padding: 8px 15px 8px 40px;
            width: 100%;
            transition: all 0.3s;
        }

        .topbar-search input:focus {
            background-color: #fff;
            box-shadow: 0 0 10px rgba(193, 155, 118, 0.2);
        }

        .topbar-search i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #777;
        }

        .topbar-divider {
            width: 1px;
            height: 30px;
            background-color: #e3e6f0;
            margin: 0 15px;
        }

        .topbar-menu {
            display: flex;
            align-items: center;
            margin-left: auto;
        }

        .topbar-item {
            position: relative;
            margin-left: 15px;
        }

        .topbar-icon {
            font-size: 1.2rem;
            color: #6c757d;
            cursor: pointer;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .topbar-icon:hover {
            background-color: #f8f9fa;
            color: var(--accent-color);
        }

        .topbar-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 0.65rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--danger-color);
            color: white;
        }

        .dropdown-menu {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 10px 0;
            min-width: 16rem;
        }

        .dropdown-item {
            padding: 10px 20px;
            color: #212529;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: #f8f9fa;
            color: var(--accent-color);
        }

        .dropdown-item i {
            width: 20px;
            text-align: center;
            margin-right: 10px;
            color: #777;
        }

        .dropdown-header {
            color: var(--accent-color);
            font-weight: 600;
            font-size: 0.7rem;
            padding: 10px 20px 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .dropdown-divider {
            margin: 5px 0;
            border-top: 1px solid #e9ecef;
        }

        .dropdown-item-message {
            display: flex;
            align-items: center;
            width: 300px;
        }

        .dropdown-item-message img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .dropdown-item-message-content {
            flex: 1;
        }

        .dropdown-item-message-title {
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 2px;
        }

        .dropdown-item-message-text {
            font-size: 0.8rem;
            color: #6c757d;
            margin-bottom: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 210px;
        }

        .dropdown-item-message-time {
            font-size: 0.7rem;
            color: #999;
        }

        /* Notification dropdown improvements */
        .dropdown-menu {
            border: none;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 0;
            min-width: 16rem;
        }

        .notification-item {
            transition: background-color 0.2s ease;
            border-radius: 0;
        }

        .notification-item:hover {
            background-color: #f8f9fa;
        }

        .notification-item:last-child {
            border-bottom: none !important;
        }

        .icon-circle {
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .dropdown-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            font-weight: 600;
        }

        .dropdown-header h6 {
            color: var(--accent-color);
            font-size: 0.9rem;
        }

        .dropdown-divider {
            margin: 0;
            border-top: 1px solid #e9ecef;
        }

        .text-truncate {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .user-profile {
            display: flex;
            align-items: center;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 30px;
            transition: all 0.3s;
        }

        .user-profile:hover {
            background-color: #f8f9fa;
        }

        .user-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 10px;
        }

        .user-profile-info {
            line-height: 1.2;
        }

        .user-profile-name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #333;
        }

        .user-profile-role {
            font-size: 0.7rem;
            color: #777;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease forwards;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--accent-color);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a67c5b;
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 14.28571%;
            }
            
            .topbar {
                left: 14.28571%;
            }
            
            .sidebar {
                width: 14.28571%;
            }
        }

        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 280px;
                z-index: 1050;
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }

            .topbar {
                left: 0;
                padding: 0 15px;
            }

            .topbar-toggle {
                display: block !important;
            }

            .topbar-search {
                width: 250px;
            }

            .page-header {
                padding: 15px;
                margin-bottom: 20px;
            }

            .page-header h1 {
                font-size: 1.5rem;
            }

            .card {
                margin-bottom: 20px;
            }

            .card-body {
                padding: 15px;
            }

            .table-responsive {
                font-size: 0.875rem;
            }

            .btn-group-sm .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }

            .dropdown-menu {
                min-width: 14rem;
            }

            .dropdown-item-message {
                width: 250px;
            }

            .dropdown-item-message-text {
                max-width: 160px;
            }
        }

        @media (max-width: 768px) {
            .main-content {
                padding: 10px;
                margin-top: 50px;
            }

            .topbar {
                height: 50px;
                padding: 0 10px;
            }

            .topbar-search {
                width: 200px;
            }

            .topbar-search input {
                padding: 6px 12px 6px 35px;
                font-size: 0.875rem;
            }

            .topbar-icon {
                width: 35px;
                height: 35px;
                font-size: 1rem;
            }

            .topbar-badge {
                width: 16px;
                height: 16px;
                font-size: 0.6rem;
            }

            .page-header {
                padding: 12px;
                margin-bottom: 15px;
            }

            .page-header h1 {
                font-size: 1.25rem;
            }

            .card-header {
                padding: 10px 12px;
            }

            .card-body {
                padding: 10px;
            }

            .table th,
            .table td {
                padding: 8px 6px;
                font-size: 0.8rem;
            }

            .btn {
                padding: 6px 12px;
                font-size: 0.875rem;
            }

            .btn-sm {
                padding: 4px 8px;
                font-size: 0.8rem;
            }

            .form-control,
            .form-select {
                padding: 6px 10px;
                font-size: 0.8rem;
            }

            .dropdown-menu {
                min-width: 12rem;
                max-width: 90vw;
            }

            .dropdown-item {
                padding: 8px 15px;
                font-size: 0.8rem;
            }

            .dropdown-item-message {
                width: 200px;
            }

            .dropdown-item-message img {
                width: 32px;
                height: 32px;
                margin-right: 10px;
            }

            .dropdown-item-message-text {
                max-width: 120px;
                font-size: 0.75rem;
            }

            .user-profile {
                padding: 3px 8px;
            }

            .user-profile img {
                width: 28px;
                height: 28px;
                margin-right: 8px;
            }

            .user-profile-name {
                font-size: 0.8rem;
            }

            .user-profile-role {
                font-size: 0.65rem;
            }

            /* Hide user info on very small screens */
            .user-profile-info {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .main-content {
                padding: 8px;
                margin-top: 45px;
            }

            .topbar {
                height: 45px;
                padding: 0 8px;
            }

            .topbar-search {
                width: 150px;
            }

            .topbar-search input {
                padding: 5px 10px 5px 30px;
                font-size: 0.8rem;
            }

            .topbar-icon {
                width: 32px;
                height: 32px;
                font-size: 0.9rem;
            }

            .topbar-badge {
                width: 14px;
                height: 14px;
                font-size: 0.55rem;
            }

            .page-header {
                padding: 10px;
                margin-bottom: 12px;
            }

            .page-header h1 {
                font-size: 1.1rem;
            }

            .card-header {
                padding: 10px 12px;
            }

            .card-body {
                padding: 10px;
            }

            .table th,
            .table td {
                padding: 6px 4px;
                font-size: 0.75rem;
            }

            .btn {
                padding: 5px 10px;
                font-size: 0.8rem;
            }

            .btn-sm {
                padding: 3px 6px;
                font-size: 0.75rem;
            }

            .form-control,
            .form-select {
                padding: 6px 10px;
                font-size: 0.8rem;
            }

            .dropdown-menu {
                min-width: 10rem;
                max-width: 95vw;
            }

            .dropdown-item {
                padding: 6px 12px;
                font-size: 0.75rem;
            }

            .dropdown-item-message {
                width: 180px;
            }

            .dropdown-item-message img {
                width: 28px;
                height: 28px;
                margin-right: 8px;
            }

            .dropdown-item-message-text {
                max-width: 100px;
                font-size: 0.7rem;
            }

            .user-profile {
                padding: 2px 6px;
            }

            .user-profile img {
                width: 24px;
                height: 24px;
                margin-right: 6px;
            }

            .user-profile-name {
                font-size: 0.75rem;
            }

            .user-profile-role {
                font-size: 0.6rem;
            }

            /* Stack buttons vertically on very small screens */
            .btn-group {
                flex-direction: column;
            }

            .btn-group .btn {
                border-radius: 4px !important;
                margin-bottom: 2px;
            }

            /* Adjust table for mobile */
            .table-responsive {
                font-size: 0.75rem;
            }

            .table th,
            .table td {
                white-space: nowrap;
            }

            /* Hide less important columns on mobile */
            .table th:nth-child(4),
            .table td:nth-child(4),
            .table th:nth-child(5),
            .table td:nth-child(5) {
                display: none;
            }
        }

        @media (max-width: 480px) {
            .topbar-search {
                display: none;
            }

            .topbar-divider {
                display: none;
            }

            .topbar-menu {
                margin-left: 0;
            }

            .topbar-item {
                margin-left: 8px;
            }

            .page-header h1 {
                font-size: 1rem;
            }

            .card-header h6 {
                font-size: 0.9rem;
            }

            .table th,
            .table td {
                font-size: 0.7rem;
            }

            /* Hide more columns on very small screens */
            .table th:nth-child(3),
            .table td:nth-child(3),
            .table th:nth-child(6),
            .table td:nth-child(6) {
                display: none;
            }
        }

        /* Print styles */
        @media print {
            .sidebar,
            .topbar,
            .btn,
            .dropdown-menu {
                display: none !important;
            }

            .main-content {
                margin-left: 0 !important;
                margin-top: 0 !important;
                padding: 0 !important;
            }

            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }

            .page-header {
                background: none !important;
                color: #000 !important;
                box-shadow: none !important;
            }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Top Navbar -->
    <nav class="topbar">
        <button class="btn topbar-toggle d-lg-none mr-3" id="sidebarToggle" style="display: none;">
            <i class="fas fa-bars"></i>
        </button>

        <div class="topbar-search">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control" placeholder="Tìm kiếm...">
        </div>

        <div class="topbar-menu">
            <div class="topbar-divider"></div>

            <!-- Notifications Dropdown -->
            <div class="topbar-item dropdown">
                <a class="topbar-icon" href="#" id="notificationsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-bell"></i>
                    <span class="topbar-badge" id="notificationBadge">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end animate slideIn" aria-labelledby="notificationsDropdown" style="max-height: 450px; overflow-y: auto; width: 380px;">
                    <div class="dropdown-header d-flex justify-content-between align-items-center py-2">
                        <h6 class="m-0">
                            <i class="fas fa-bell me-2"></i>Thông báo
                        </h6>
                        <button class="btn btn-sm btn-outline-secondary" id="markAllReadBtn" style="font-size: 0.7rem; padding: 0.25rem 0.5rem;">
                            <i class="fas fa-check-double"></i>
                        </button>
                    </div>
                    <div id="notificationsList">
                        <div class="dropdown-item text-center small text-gray-500 py-3">
                            <i class="fas fa-spinner fa-spin"></i> Đang tải...
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-center small text-gray-500 py-2" href="{{ route('admin.notifications.index') }}">
                        <i class="fas fa-list me-1"></i>Xem tất cả thông báo
                    </a>
                </div>
            </div>

            <!-- Messages Dropdown -->
            <div class="topbar-item dropdown">
                <a class="topbar-icon" href="#" id="messagesDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-envelope"></i>
                    <span class="topbar-badge">
                        {{ \App\Models\SupportTicket::with('messages')->whereHas('messages')->count() }}
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end animate slideIn" aria-labelledby="messagesDropdown">
                    <h6 class="dropdown-header">Tin nhắn hỗ trợ</h6>
                    @php
                        $tickets = \App\Models\SupportTicket::with(['user','messages' => function($q){ $q->latest(); }])
                            ->whereHas('messages')
                            ->latest('updated_at')
                            ->take(5)->get();
                    @endphp
                    @forelse($tickets as $ticket)
                        <a class="dropdown-item" href="{{ route('admin.support.showTicket', $ticket->id) }}">
                            <div class="dropdown-item-message">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($ticket->user->name ?? 'Khach') }}&background=random" alt="{{ $ticket->user->name ?? 'Khách' }}">
                                <div class="dropdown-item-message-content">
                                    <div class="dropdown-item-message-title">{{ $ticket->user->name ?? 'Khách' }}</div>
                                    <p class="dropdown-item-message-text">{{ optional($ticket->messages->first())->message ?? '...' }}</p>
                                    <div class="dropdown-item-message-time">{{ optional($ticket->messages->first())->created_at ? optional($ticket->messages->first())->created_at->diffForHumans() : '' }}</div>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="dropdown-item text-center small text-gray-500">Không có tin nhắn mới</div>
                    @endforelse
                    <a class="dropdown-item text-center small text-gray-500" href="{{ route('admin.support.index') }}">Xem tất cả tin nhắn</a>
                </div>
            </div>

            <div class="topbar-divider"></div>

            <!-- User Information Dropdown -->
            <div class="topbar-item dropdown">
                <a class="user-profile" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="navbar-brand text-center py-4">
                        <i class="fas fa-hotel me-2"></i> Marron Hotel
                    </div>
                    <hr class="mx-3 opacity-25">
                    <ul class="nav flex-column px-3 mt-4">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.bookings.*') ? 'active' : '' }}" href="{{ route('admin.bookings.index') }}">
                                <i class="fas fa-calendar-check"></i> Đặt phòng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.rooms.*') ? 'active' : '' }}" href="{{ route('admin.rooms.index')}}">
                                <i class="fas fa-bed"></i> Phòng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users"></i> Người dùng
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.room-type-reviews.*') ? 'active' : '' }}" href="{{ route('admin.room-type-reviews.index') }}">
                                <i class="fas fa-star"></i> Đánh giá
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.bookings.report') ? 'active' : '' }}" href="{{ route('admin.bookings.report') }}">
                                <i class="fas fa-chart-bar"></i> Báo cáo
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}" href="{{ route('admin.notifications.index') }}">
                                <i class="fas fa-bell"></i> Thông báo
                                <span class="badge bg-danger ms-2" id="sidebarNotificationBadge" style="display: none;">0</span>
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
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="page-header fade-in">
                    <h1>@yield('header', 'Dashboard')</h1>
                </div>

                <!-- Hiển thị thông báo -->
                <div class="alert-container">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                </div>

                <div class="content-wrapper fade-in">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <!-- Toast notifications will be inserted here -->
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // CSRF Token cho AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Hệ thống thông báo thời gian thực
        class NotificationManager {
            constructor() {
                this.badge = $('#notificationBadge');
                this.list = $('#notificationsList');
                this.markAllBtn = $('#markAllReadBtn');
                this.init();
            }

            init() {
                this.loadNotifications();
                this.bindEvents();
                this.startPolling();
            }

            bindEvents() {
                this.markAllBtn.on('click', (e) => {
                    e.preventDefault();
                    this.markAllAsRead();
                });

                // Đánh dấu đã đọc khi click vào thông báo
                this.list.on('click', '.notification-item', (e) => {
                    const notificationId = $(e.currentTarget).data('id');
                    this.markAsRead(notificationId);
                });
            }

            loadNotifications() {
                $.get('/admin/api/notifications/unread')
                    .done((response) => {
                        if (response.success) {
                            this.updateBadge(response.count);
                            this.renderNotifications(response.notifications);
                        }
                    })
                    .fail(() => {
                        this.renderError();
                    });
            }

            updateBadge(count) {
                this.badge.text(count);
                this.badge.toggle(count > 0);
                
                // Cập nhật badge trong sidebar
                const sidebarBadge = $('#sidebarNotificationBadge');
                sidebarBadge.text(count);
                sidebarBadge.toggle(count > 0);
            }

            renderNotifications(notifications) {
                if (notifications.length === 0) {
                    this.list.html(`
                        <div class="dropdown-item text-center small text-gray-500 py-3">
                            <i class="fas fa-check-circle text-success me-2"></i> Không có thông báo mới
                        </div>
                    `);
                    return;
                }

                const html = notifications.map(notification => `
                    <div class="dropdown-item notification-item" data-id="${notification.id}" style="cursor: pointer; padding: 0.75rem 1rem; border-bottom: 1px solid #f0f0f0;">
                        <div class="d-flex align-items-start">
                            <div class="icon-circle bg-${notification.color} me-3" style="width: 32px; height: 32px; min-width: 32px;">
                                <i class="${notification.display_icon} text-white" style="font-size: 0.8rem;"></i>
                            </div>
                            <div class="flex-grow-1" style="min-width: 0;">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="fw-bold small text-truncate" style="max-width: 200px;" title="${notification.title}">
                                        ${notification.title}
                                    </div>
                                    <div class="d-flex align-items-center gap-1">
                                        ${notification.priority === 'urgent' ? '<span class="badge bg-danger" style="font-size: 0.6rem;">Khẩn</span>' : ''}
                                        ${notification.priority === 'high' ? '<span class="badge bg-warning" style="font-size: 0.6rem;">Cao</span>' : ''}
                                    </div>
                                </div>
                                <div class="small text-muted text-truncate mb-1" style="max-width: 250px;" title="${notification.message}">
                                    ${notification.message}
                                </div>
                                <div class="small text-gray-500">
                                    <i class="fas fa-clock me-1"></i>${notification.time_ago}
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');

                this.list.html(html);
            }

            renderError() {
                this.list.html(`
                    <div class="dropdown-item text-center small text-gray-500 py-3">
                        <i class="fas fa-exclamation-triangle text-warning me-2"></i> Lỗi tải thông báo
                    </div>
                `);
            }

            markAsRead(notificationId) {
                $.post('/admin/api/notifications/mark-read', { notification_id: notificationId })
                    .done((response) => {
                        if (response.success) {
                            this.updateBadge(response.count);
                            this.loadNotifications();
                        }
                    });
            }

            markAllAsRead() {
                $.post('/admin/api/notifications/mark-all-read')
                    .done((response) => {
                        if (response.success) {
                            this.updateBadge(0);
                            this.loadNotifications();
                            this.showToast('Đã đánh dấu tất cả thông báo đã đọc', 'success');
                        }
                    });
            }

            startPolling() {
                // Cập nhật thông báo mỗi 30 giây
                setInterval(() => {
                    this.loadNotifications();
                }, 30000);
            }

            showToast(message, type = 'info') {
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
        }

        // Khởi tạo hệ thống thông báo khi trang đã load
        $(document).ready(function() {
            new NotificationManager();
        });
    </script>
    <script>
        // Animation for cards
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 100 * index);
            });

            // Hover effect for buttons
            const buttons = document.querySelectorAll('.btn');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
                });

                button.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });

            // Toggle sidebar on mobile
            const sidebarToggle = document.getElementById('sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    document.querySelector('.sidebar').classList.toggle('show');
                });
            }

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(e) {
                const sidebar = document.querySelector('.sidebar');
                const sidebarToggle = document.getElementById('sidebarToggle');
                
                if (window.innerWidth <= 992 && sidebar.classList.contains('show')) {
                    if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                        sidebar.classList.remove('show');
                    }
                }
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                const sidebar = document.querySelector('.sidebar');
                if (window.innerWidth > 992) {
                    sidebar.classList.remove('show');
                }
            });

            // Improve dropdown positioning on mobile
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                const menu = dropdown.querySelector('.dropdown-menu');
                if (menu) {
                    // Check if dropdown would go off screen
                    dropdown.addEventListener('show.bs.dropdown', function() {
                        const rect = menu.getBoundingClientRect();
                        const viewportWidth = window.innerWidth;
                        
                        if (rect.right > viewportWidth) {
                            menu.style.left = 'auto';
                            menu.style.right = '0';
                        }
                    });
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                const dropdowns = document.querySelectorAll('.dropdown-menu.show');
                dropdowns.forEach(dropdown => {
                    if (!dropdown.contains(e.target) && !dropdown.previousElementSibling.contains(e.target)) {
                        dropdown.classList.remove('show');
                    }
                });
            });
        });
    </script>
    <!-- Full HD Optimization JavaScript -->
    <script src="{{ asset('admin/js/fullhd-optimization.js') }}"></script>
    @yield('scripts')
</body>
</html>
