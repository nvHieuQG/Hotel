@extends('admin.layouts.admin-master')

@section('header', 'Chi tiết đặt phòng')

@section('content')
<div class="container-fluid px-4">
    <!-- Hiển thị thông báo -->
    {{-- @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif --}}

    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Quản lý đặt phòng</a></li>
        <li class="breadcrumb-item active">Chi tiết đặt phòng #{{ $booking->booking_id }}</li>
    </ol>

    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-info-circle me-1"></i>
                            Thông tin đặt phòng #{{ $booking->booking_id }}
                        </div>
                        <div>
                            <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i> Chỉnh sửa
                            </a>
                            <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đặt phòng này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i> Xóa
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <i class="fas fa-user me-1"></i>
                                    Thông tin khách hàng
                                </div>
                                <div class="card-body">
                                    <p><strong>Họ tên:</strong> {{ $booking->user->name }}</p>
                                    <p><strong>Email:</strong> {{ $booking->user->email }}</p>
                                    <p><strong>Số điện thoại:</strong> {{ $booking->user->phone ?? 'Không có' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <i class="fas fa-hotel me-1"></i>
                                    Thông tin phòng
                                </div>
                                <div class="card-body">
                                    <p><strong>Phòng:</strong> {{ $booking->room->name }}</p>
                                    <p><strong>Loại phòng:</strong> {{ $booking->room->roomType->name ?? 'Không có' }}</p>
                                    <p><strong>Giá phòng:</strong> {{ number_format($booking->room->price) }} VND/đêm</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <i class="fas fa-calendar-alt me-1"></i>
                                    Thông tin đặt phòng
                                </div>
                                <div class="card-body">
                                    <p><strong>Mã đặt phòng:</strong> {{ $booking->booking_id }}</p>
                                    <p><strong>Ngày đặt:</strong> {{ date('d/m/Y H:i', strtotime($booking->created_at)) }}</p>
                                    <p><strong>Check-in:</strong> {{ date('d/m/Y', strtotime($booking->check_in_date)) }}</p>
                                    <p><strong>Check-out:</strong> {{ date('d/m/Y', strtotime($booking->check_out_date)) }}</p>
                                    <p><strong>Số đêm:</strong> {{ date_diff(new DateTime($booking->check_in_date), new DateTime($booking->check_out_date))->days }}</p>
                                    <p><strong>Tổng tiền:</strong> {{ number_format($booking->price) }} VND</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header">
                                    <i class="fas fa-tasks me-1"></i>
                                    Trạng thái đặt phòng
                                </div>
                                <div class="card-body">
                                    <p><strong>Trạng thái hiện tại:</strong> 
                                        <span class="badge bg-{{ 
                                            $booking->status == 'pending' ? 'warning' : 
                                            ($booking->status == 'confirmed' ? 'primary' : 
                                            ($booking->status == 'checked_in' ? 'info' :
                                            ($booking->status == 'checked_out' ? 'secondary' :
                                            ($booking->status == 'completed' ? 'success' : 
                                            ($booking->status == 'no_show' ? 'dark' : 'danger'))))) 
                                        }}">
                                            {{ $booking->status_text }}
                                        </span>
                                    </p>
                                    <p><strong>Cập nhật trạng thái:</strong></p>
                                    <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <div class="input-group mb-3">
                                            <select name="status" class="form-select">
                                                <option value="{{ $booking->status }}" selected>{{ $booking->status_text }} (Hiện tại)</option>
                                                @foreach($validNextStatuses as $status => $label)
                                                    <option value="{{ $status }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button class="btn btn-primary" type="submit">Cập nhật</button>
                                        </div>
                                    </form>
                                    @if(empty($validNextStatuses))
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Lưu ý:</strong> Booking đã ở trạng thái cuối cùng. Chỉ có thể chuyển sang "Đã hủy" hoặc "Khách không đến".
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Hướng dẫn:</strong> Có thể chuyển sang bất kỳ trạng thái nào phía trước hoặc chuyển đặc biệt sang "Đã hủy"/"Khách không đến". 
                                            <br><small class="text-muted">Trạng thái hiện tại: <strong>{{ $booking->status_text }}</strong></small>
                                        </div>
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle"></i>
                                            <strong>Thông tin:</strong> Có <strong>{{ count($validNextStatuses) }}</strong> trạng thái có thể chuyển đổi từ trạng thái hiện tại.
                                            <br><small class="text-muted">Các trạng thái có thể chuyển: 
                                                @foreach($validNextStatuses as $status => $label)
                                                    <span class="badge bg-light text-dark me-1">{{ $label }}</span>
                                                @endforeach
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Ghi chú đặt phòng -->
                    @include('admin.bookings.partials.notes')
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4">
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>
</div>
@endsection 