@extends('admin.layouts.admin-master')

@section('header', 'Chi tiết đặt phòng')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Quản lý đặt phòng</a></li>
        <li class="breadcrumb-item active">Chi tiết đặt phòng #{{ $booking->booking_id }}</li>
    </ol>

    <!-- Header với thông tin cơ bản -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="fas fa-info-circle me-1"></i>
                                Đặt phòng #{{ $booking->booking_id }}
                            </h5>
                            <small class="text-muted">
                                {{ $booking->user->name }} - {{ $booking->room->name }} - 
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
                            </small>
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
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Khách hàng</small>
                            <p class="mb-0"><strong>{{ $booking->user->name }}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Phòng</small>
                            <p class="mb-0"><strong>{{ $booking->room->name }}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Check-in</small>
                            <p class="mb-0"><strong>{{ date('d/m/Y', strtotime($booking->check_in_date)) }}</strong></p>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Tổng tiền</small>
                            <p class="mb-0"><strong>{{ number_format($booking->price) }} VND</strong></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- Thông tin chi tiết bổ sung -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Thông tin chi tiết
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Thông tin đặt phòng</h6>
                        <div class="row">
                            <div class="col-6">
                                <small><strong>Mã đặt phòng:</strong><br>{{ $booking->booking_id }}</small>
                            </div>
                            <div class="col-6">
                                <small><strong>Ngày đặt:</strong><br>{{ date('d/m/Y H:i', strtotime($booking->created_at)) }}</small>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-6">
                                <small><strong>Check-in:</strong><br>{{ date('d/m/Y', strtotime($booking->check_in_date)) }}</small>
                            </div>
                            <div class="col-6">
                                <small><strong>Check-out:</strong><br>{{ date('d/m/Y', strtotime($booking->check_out_date)) }}</small>
                            </div>
                        </div>
                        <div class="row mt-1">
                            <div class="col-6">
                                <small><strong>Số đêm:</strong><br>{{ date_diff(new DateTime($booking->check_in_date), new DateTime($booking->check_out_date))->days }} đêm</small>
                            </div>
                            <div class="col-6">
                                <small><strong>Tổng tiền:</strong><br>{{ number_format($booking->price) }} VND</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Cập nhật trạng thái</h6>
                        <form action="{{ route('admin.bookings.update-status', $booking->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="input-group">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="{{ $booking->status }}" selected>{{ $booking->status_text }} (Hiện tại)</option>
                                    @foreach($validNextStatuses as $status => $label)
                                        <option value="{{ $status }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-primary btn-sm" type="submit">Cập nhật</button>
                            </div>
                        </form>
                        @if(empty($validNextStatuses))
                            <small class="text-muted mt-1">
                                <i class="fas fa-exclamation-triangle"></i>
                                Booking đã ở trạng thái cuối cùng
                            </small>
                        @else
                            <small class="text-muted mt-1">
                                <i class="fas fa-info-circle"></i>
                                Có {{ count($validNextStatuses) }} trạng thái có thể chuyển đổi
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    <!-- Layout chính: Notes bên trái, Giấy tạm trú bên phải -->
    <div class="row">
        <!-- Cột trái: Ghi chú (1/2 trang) -->
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">
                        <i class="fas fa-sticky-note me-1"></i>
                        Ghi chú đặt phòng
                        @php
                            $bookingNoteService = app(\App\Interfaces\Services\BookingServiceInterface::class);
                            $totalNotes = $bookingNoteService->getPaginatedNotes($booking->id, 1)->total();
                        @endphp
                        <span class="badge bg-primary ms-2">{{ $totalNotes }}</span>
                    </h6>
                    <a href="{{ route('booking-notes.create', $booking->id) }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i>Thêm ghi chú
                    </a>
                </div>
                <div class="card-body p-0">
                    @include('admin.bookings.partials.notes')
                </div>
            </div>
        </div>

        <!-- Cột phải: Giấy tạm trú tạm vắng (1/2 trang) -->
        <div class="col-lg-6">
            @if($booking->hasCompleteIdentityInfo())
                <div class="card h-100 registration-section">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-file-alt me-1"></i>
                            Giấy đăng ký tạm chú tạm vắng
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- Thông tin trạng thái -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span><strong>Trạng thái:</strong></span>
                                    <span class="badge bg-{{ 
                                        $booking->registration_status === 'pending' ? 'warning' : 
                                        ($booking->registration_status === 'generated' ? 'info' : 'success') 
                                    }}">
                                        {{ $booking->registration_status_text }}
                                    </span>
                                </div>
                                @if($booking->registration_generated_at)
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Tạo lúc: {{ $booking->registration_generated_at->format('d/m/Y H:i') }}
                                    </small>
                                @endif
                                @if($booking->registration_sent_at)
                                    <br><small class="text-muted">
                                        <i class="fas fa-envelope me-1"></i>
                                        Gửi lúc: {{ $booking->registration_sent_at->format('d/m/Y H:i') }}
                                    </small>
                                @endif
                            </div>
                        </div>

                        <!-- Thông tin khách lưu trú (tóm tắt) -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <h6 class="text-muted mb-2">Thông tin khách lưu trú:</h6>
                                <div class="row">
                                    <div class="col-6">
                                        <small><strong>Họ tên:</strong><br>{{ $booking->guest_full_name }}</small>
                                    </div>
                                    <div class="col-6">
                                        <small><strong>CMND:</strong><br>{{ $booking->guest_id_number }}</small>
                                    </div>
                                </div>
                                <div class="row mt-1">
                                    <div class="col-6">
                                        <small><strong>Ngày sinh:</strong><br>{{ $booking->guest_birth_date ? $booking->guest_birth_date->format('d/m/Y') : 'N/A' }}</small>
                                    </div>
                                    <div class="col-6">
                                        <small><strong>Quốc tịch:</strong><br>{{ $booking->guest_nationality ?? 'N/A' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Các nút thao tác -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted mb-2">Thao tác:</h6>
                                
                                <!-- Xem trước -->
                                <div class="mb-3">
                                    <a href="{{ route('admin.bookings.registration.preview', $booking->id) }}" target="_blank" class="btn btn-secondary btn-sm w-100">
                                        <i class="fas fa-eye"></i> Xem trước giấy đăng ký
                                    </a>
                                </div>
                                
                                <!-- Tạo file -->
                                <div class="btn-group-vertical w-100 mb-3" role="group">
                                    <form action="{{ route('admin.bookings.generate-pdf', $booking->id) }}" method="POST" class="d-inline mb-1">
                                        @csrf
                                        <button type="submit" class="btn btn-info btn-sm w-100">
                                            <i class="fas fa-file-pdf"></i> Tạo PDF
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('admin.bookings.send-email', $booking->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm w-100">
                                            <i class="fas fa-envelope"></i> Gửi Email
                                        </button>
                                    </form>
                                </div>

                                <!-- Xem và tải xuống -->
                                @if($booking->registration_status === 'generated')
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="btn-group-vertical w-100" role="group">
                                                <a href="{{ route('admin.bookings.view-word', $booking->id) }}" target="_blank" class="btn btn-outline-info btn-sm">
                                                    <i class="fas fa-eye"></i> Xem PDF
                                                </a>
                                                <a href="{{ route('admin.bookings.download-word', $booking->id) }}" class="btn btn-info btn-sm">
                                                    <i class="fas fa-download"></i> Tải PDF
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info alert-sm">
                                        <i class="fas fa-info-circle"></i>
                                        Vui lòng tạo file trước khi xem/tải xuống
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="card h-100">
                    <div class="card-header bg-warning text-dark">
                        <h6 class="mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Thông tin căn cước chưa đầy đủ
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Lưu ý:</strong> Thông tin căn cước của khách chưa đầy đủ. Không thể tạo giấy đăng ký tạm chú tạm vắng.
                        </div>
                        
                        <h6 class="text-muted mb-2">Thông tin cần bổ sung:</h6>
                        <ul class="list-unstyled">
                            @if(!$booking->guest_full_name)
                                <li><i class="fas fa-times text-danger me-1"></i> Họ tên khách lưu trú</li>
                            @endif
                            @if(!$booking->guest_id_number)
                                <li><i class="fas fa-times text-danger me-1"></i> Số căn cước/CMND</li>
                            @endif
                            @if(!$booking->guest_birth_date)
                                <li><i class="fas fa-times text-danger me-1"></i> Ngày sinh</li>
                            @endif
                            @if(!$booking->guest_gender)
                                <li><i class="fas fa-times text-danger me-1"></i> Giới tính</li>
                            @endif
                            @if(!$booking->guest_nationality)
                                <li><i class="fas fa-times text-danger me-1"></i> Quốc tịch</li>
                            @endif
                            @if(!$booking->guest_permanent_address)
                                <li><i class="fas fa-times text-danger me-1"></i> Địa chỉ thường trú</li>
                            @endif
                        </ul>
                        
                        <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Cập nhật thông tin
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    

    <div class="mt-4">
        <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Quay lại danh sách
        </a>
    </div>
</div>

@push('scripts')
<script>
    
</script>
@endpush
@push('styles')
<style>
    .booking-notes-section .card-body {
        padding: 1rem;
    }
    
    .note-item {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff !important;
    }
    
    .note-item .badge {
        font-size: 0.7rem;
    }
    
    .note-content {
        line-height: 1.5;
    }
    
    .note-meta {
        border-top: 1px solid #dee2e6;
        padding-top: 0.5rem;
    }
    
    /* Cải thiện layout cho phần giấy tạm trú */
    .registration-section .btn-group-vertical .btn {
        margin-bottom: 0.25rem;
    }
    
    .registration-section .btn-sm {
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
    }
    
    /* Responsive cho layout 2 cột */
    @media (max-width: 991.98px) {
        .col-lg-6 {
            margin-bottom: 1rem;
        }
    }
    
    /* Style cho phần ghi chú */
    .booking-notes-section .text-center {
        padding: 1rem;
    }
    
    .booking-notes-section .btn-outline-primary {
        border-radius: 20px;
        font-size: 0.8rem;
    }
    
    .booking-notes-section .text-muted {
        font-size: 0.85rem;
    }
</style>
@endpush
@endsection 