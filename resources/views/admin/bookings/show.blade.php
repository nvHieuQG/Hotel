@extends('admin.layouts.admin-master')

@section('header', 'Chi tiết đặt phòng')

@section('content')
<div class="container-fluid px-4">

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
                                    <hr>
                                    <p><strong>Tiền phòng:</strong> {{ number_format($booking->price) }} VNĐ</p>
                                    <p><strong>Tiền dịch vụ:</strong> 
                                        <span class="text-{{ $booking->total_services_price > 0 ? 'success' : 'muted' }}">
                                            {{ number_format($booking->total_services_price) }} VNĐ
                                        </span>
                                    </p>
                                    <p class="mb-0"><strong>Tổng cộng:</strong> 
                                        <span class="text-primary fw-bold">
                                            {{ number_format($booking->price + $booking->total_services_price) }} VNĐ
                                        </span>
                                    </p>
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
                                    @if($booking->status === 'pending_payment')
                                        @if($booking->hasSuccessfulPayment())
                                            <div class="alert alert-success">
                                                <i class="fas fa-check-circle"></i>
                                                <strong>Đã thanh toán:</strong> Khách hàng đã thanh toán đầy đủ. Có thể chuyển sang "Đã xác nhận".
                                            </div>
                                        @else
                                            <div class="alert alert-warning">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong>Chưa thanh toán:</strong> Khách hàng chưa thanh toán. Chỉ có thể hủy đặt phòng.
                                            </div>
                                        @endif
                                    @elseif(empty($validNextStatuses))
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Lưu ý:</strong> Booking đã ở trạng thái cuối cùng. Chỉ có thể chuyển sang "Đã hủy" hoặc "Khách không đến".
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Hướng dẫn:</strong> Chỉ được phép chuyển trạng thái theo thứ tự từng bước một: <strong>pending → pending_payment → confirmed → checked_in → checked_out → completed</strong>
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

                    <!-- Thông tin thanh toán -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-credit-card me-1"></i>
                                    Thông tin thanh toán
                                </div>
                                <div class="card-body">
                                    @if($booking->payments->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Phương thức</th>
                                                        <th>Số tiền</th>
                                                        <th>Trạng thái</th>
                                                        <th>Thời gian</th>
                                                        <th>Mã giao dịch</th>
                                                        <th>Ghi chú</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($booking->payments->sortByDesc('created_at') as $payment)
                                                        <tr>
                                                            <td>
                                                                <span class="badge bg-{{ 
                                                                    $payment->payment_method == 'bank_transfer' ? 'primary' : 
                                                                    ($payment->payment_method == 'cod' ? 'info' : 'secondary') 
                                                                }}">
                                                                    @if($payment->payment_method == 'bank_transfer')
                                                                        <i class="fas fa-university"></i> Chuyển khoản
                                                                    @elseif($payment->payment_method == 'cod')
                                                                        <i class="fas fa-money-bill-wave"></i> Thanh toán tại khách sạn
                                                                    @else
                                                                        <i class="fas fa-money-bill"></i> {{ ucfirst($payment->payment_method) }}
                                                                    @endif
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="text-success font-weight-bold">
                                                                    {{ number_format($payment->amount) }} VNĐ
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ 
                                                                    $payment->status == 'completed' ? 'success' : 
                                                                    ($payment->status == 'processing' ? 'info' : 
                                                                    ($payment->status == 'pending' ? 'warning' : 
                                                                    ($payment->status == 'failed' ? 'danger' : 'secondary'))) 
                                                                }}">
                                                                    <i class="fas fa-{{ 
                                                                        $payment->status == 'completed' ? 'check-circle' : 
                                                                        ($payment->status == 'processing' ? 'clock' : 
                                                                        ($payment->status == 'pending' ? 'hourglass-half' : 
                                                                        ($payment->status == 'failed' ? 'times-circle' : 'minus-circle'))) 
                                                                    }}"></i>
                                                                    {{ $payment->status_text }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                @if($payment->paid_at)
                                                                    {{ $payment->paid_at->format('d/m/Y H:i:s') }}
                                                                @else
                                                                    {{ $payment->created_at->format('d/m/Y H:i:s') }}
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <code>{{ $payment->transaction_id ?? 'N/A' }}</code>
                                                            </td>
                                                            <td>
                                                                @if($payment->gateway_message)
                                                                    <small class="text-muted">{{ $payment->gateway_message }}</small>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <!-- Tổng kết thanh toán -->
                                        <div class="row mt-3">
                                            <div class="col-md-4">
                                                <div class="alert alert-info">
                                                    <strong>Tổng số giao dịch:</strong> {{ $booking->payments->count() }}
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="alert alert-{{ $booking->hasSuccessfulPayment() ? 'success' : 'warning' }}">
                                                    <strong>Trạng thái thanh toán:</strong> 
                                                    @if($booking->hasSuccessfulPayment())
                                                        <i class="fas fa-check-circle"></i> Đã thanh toán đầy đủ
                                                    @else
                                                        <i class="fas fa-exclamation-triangle"></i> Chưa thanh toán đầy đủ
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                @if($booking->payments->where('status', 'processing')->where('payment_method', 'bank_transfer')->count() > 0)
                                                    <div class="alert alert-warning">
                                                        <strong>Chuyển khoản đang chờ xác nhận:</strong>
                                                        <br>
                                                        <button type="button" class="btn btn-success btn-sm mt-2" onclick="confirmBankTransfer()">
                                                            <i class="fas fa-check"></i> Xác nhận thanh toán
                                                        </button>
                                                    </div>
                                                @elseif($booking->payments->where('status', 'processing')->count() > 0)
                                                    <div class="alert alert-info">
                                                        <strong>Thanh toán đang xử lý:</strong>
                                                        <br>
                                                        <small>Có {{ $booking->payments->where('status', 'processing')->count() }} giao dịch đang xử lý (không phải chuyển khoản)</small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Chưa có giao dịch thanh toán nào.</strong>
                                            <br>
                                            <small class="text-muted">Khách hàng chưa thực hiện thanh toán cho đặt phòng này.</small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quản lý dịch vụ -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-0 shadow-sm">
                                <!-- Tiêu đề -->
                                <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 fs-6">
                                        <i class="fas fa-concierge-bell text-primary me-2"></i>
                                        Dịch vụ Booking
                                        <span class="badge bg-primary ms-2">{{ $bookingServices->count() }}</span>
                                    </h6>
                                    @if($bookingServices->count())
                                        <div class="text-end">
                                            <small class="text-muted">Tổng tiền:</small><br>
                                            <span class="text-success fw-semibold">{{ number_format($booking->total_services_price) }} VNĐ</span>
                                        </div>
                                    @endif
                                </div>

                                <div class="card-body row">
                                    <!-- Bảng dịch vụ -->
                                    <div class="col-lg-8">
                                        @if($bookingServices->count())
                                            <div class="table-responsive">
                                                <table class="table table-sm table-hover align-middle small">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Dịch vụ</th>
                                                            <th>Loại</th>
                                                            <th>Đơn giá</th>
                                                            <th>SL</th>
                                                            <th>Thành tiền</th>
                                                            <th></th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($bookingServices as $service)
                                                            <tr>
                                                                <td>
                                                                    <strong>{{ $service->service->name }}</strong><br>
                                                                    @if($service->notes)
                                                                        <small class="text-muted">{{ $service->notes }}</small>
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    <span class="badge bg-{{ $service->type === 'room_type' ? 'info' : ($service->type === 'additional' ? 'success' : 'warning') }}">
                                                                        {{ $service->type === 'room_type' ? 'Loại phòng' : ($service->type === 'additional' ? 'Bổ sung' : 'Tùy chỉnh') }}
                                                                    </span>
                                                                </td>
                                                                <td>{{ $service->formatted_unit_price }}</td>
                                                                <td class="text-center">
                                                                    <span class="badge bg-light text-dark">{{ $service->quantity }}</span>
                                                                </td>
                                                                <td class="text-success fw-semibold">{{ $service->formatted_total_price }}</td>
                                                                <td class="text-end">
                                                                    @if($service->type !== 'room_type')
                                                                        <form action="{{ route('admin.bookings.services.destroy', [$booking->id, $service->id]) }}" method="POST" onsubmit="return confirm('Xóa dịch vụ này?')" style="display:inline;">
                                                                            @csrf @method('DELETE')
                                                                            <button class="btn btn-sm btn-outline-danger" title="Xóa">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </form>
                                                                    @else
                                                                        <small class="text-muted">Tự động</small>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="table-light small">
                                                        <tr>
                                                            <td colspan="4" class="text-end fw-semibold">Tổng cộng:</td>
                                                            <td class="text-success fw-bold">{{ number_format($bookingServices->sum('total_price')) }} VNĐ</td>
                                                            <td></td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center text-muted py-4 small">
                                                <i class="fas fa-inbox fa-2x mb-2"></i>
                                                <div>Chưa có dịch vụ nào</div>
                                                <small>Sử dụng form bên phải để thêm</small>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Form thêm -->
                                    <div class="col-lg-4">
                                        <div class="card bg-light border-0 h-100">
                                            <div class="card-header bg-success text-white py-2">
                                                <h6 class="mb-0 fs-6"><i class="fas fa-plus me-1"></i> Thêm dịch vụ</h6>
                                            </div>
                                            <div class="card-body small">
                                                <form action="{{ route('admin.bookings.services.add', $booking->id) }}" method="POST">
                                                    @csrf
                                                    <div class="mb-2">
                                                        <label class="form-label mb-1">Tên dịch vụ <span class="text-danger">*</span></label>
                                                        <input type="text" name="service_name" value="{{ old('service_name') }}"
                                                               class="form-control form-control-sm @error('service_name') is-invalid @enderror" required>
                                                        @error('service_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label mb-1">Giá (VNĐ) <span class="text-danger">*</span></label>
                                                        <input type="number" name="service_price" value="{{ old('service_price') }}"
                                                               class="form-control form-control-sm @error('service_price') is-invalid @enderror"
                                                               required min="0" step="1000">
                                                        @error('service_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                    </div>

                                                    <div class="mb-2">
                                                        <label class="form-label mb-1">Số lượng <span class="text-danger">*</span></label>
                                                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                                                               class="form-control form-control-sm @error('quantity') is-invalid @enderror"
                                                               required min="1">
                                                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                    </div>

                                                    <div class="mb-3">
                                                        <label class="form-label mb-1">Ghi chú</label>
                                                        <textarea name="notes" rows="2" class="form-control form-control-sm">{{ old('notes') }}</textarea>
                                                    </div>

                                                    <button type="submit" class="btn btn-sm btn-success w-100">
                                                        <i class="fas fa-plus me-1"></i> Thêm dịch vụ
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- End form -->
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

@section('scripts')
<script>
    $(document).ready(function() {
        // Khởi tạo trang
    });

    function confirmBankTransfer() {
        if (confirm('Bạn có chắc chắn muốn xác nhận thanh toán chuyển khoản cho đặt phòng này?')) {
            // Disable button để tránh double click
            const button = event.target;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
            
            // Gửi request xác nhận thanh toán
            fetch('{{ route("admin.bookings.confirm-payment", $booking->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Đã xác nhận thanh toán thành công!');
                    // Thay vì reload, cập nhật UI
                    updatePaymentStatus();
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                    // Re-enable button
                    button.disabled = false;
                    button.innerHTML = '<i class="fas fa-check"></i> Xác nhận thanh toán';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xác nhận thanh toán!');
                // Re-enable button
                button.disabled = false;
                button.innerHTML = '<i class="fas fa-check"></i> Xác nhận thanh toán';
            });
        }
    }

    function updatePaymentStatus() {
        // Cập nhật trạng thái payment trong bảng
        const paymentRows = document.querySelectorAll('tbody tr');
        paymentRows.forEach(row => {
            const statusCell = row.querySelector('td:nth-child(3)');
            if (statusCell) {
                const badge = statusCell.querySelector('.badge');
                if (badge && badge.textContent.includes('Đang xử lý')) {
                    badge.className = 'badge bg-success';
                    badge.innerHTML = '<i class="fas fa-check-circle"></i> Đã thanh toán';
                }
            }
        });

        // Ẩn nút xác nhận và cập nhật thông báo
        const confirmButton = document.querySelector('.alert-warning button');
        if (confirmButton) {
            const alertDiv = confirmButton.closest('.alert-warning');
            alertDiv.className = 'alert alert-success';
            alertDiv.innerHTML = '<strong>Thanh toán đã được xác nhận:</strong><br><i class="fas fa-check-circle"></i> Đã xác nhận thành công';
        }

        // Cập nhật tổng kết thanh toán
        const summaryAlert = document.querySelector('.alert-warning strong');
        if (summaryAlert && summaryAlert.textContent.includes('Chưa thanh toán đầy đủ')) {
            const parentAlert = summaryAlert.closest('.alert');
            parentAlert.className = 'alert alert-success';
            parentAlert.innerHTML = '<strong>Trạng thái thanh toán:</strong> <i class="fas fa-check-circle"></i> Đã thanh toán đầy đủ';
        }

        // Cập nhật trạng thái booking (nếu đang pending)
        const bookingStatusBadge = document.querySelector('.card-body .badge');
        if (bookingStatusBadge && bookingStatusBadge.textContent.includes('Chờ xác nhận')) {
            bookingStatusBadge.className = 'badge bg-primary';
            bookingStatusBadge.innerHTML = 'Đã xác nhận';
        }

        // Cập nhật dropdown trạng thái booking
        const statusSelect = document.querySelector('select[name="status"]');
        if (statusSelect) {
            const currentOption = statusSelect.querySelector('option[selected]');
            if (currentOption && currentOption.textContent.includes('Chờ xác nhận')) {
                // Thay đổi option được chọn
                currentOption.removeAttribute('selected');
                const confirmedOption = statusSelect.querySelector('option[value="confirmed"]');
                if (confirmedOption) {
                    confirmedOption.setAttribute('selected', 'selected');
                    confirmedOption.textContent = 'Đã xác nhận (Hiện tại)';
                }
            }
        }

        // Hiển thị thông báo thành công
        if (typeof AdminUtils !== 'undefined' && AdminUtils.showToast) {
            AdminUtils.showToast('Đã xác nhận thanh toán và cập nhật trạng thái booking thành công!', 'success');
        } else {
            alert('Đã xác nhận thanh toán và cập nhật trạng thái booking thành công!');
        }
    }

    // JavaScript cho phần ghi chú
    function deleteNote(noteId) {
        if (confirm('Bạn có chắc chắn muốn xóa ghi chú này?')) {
            fetch(`/admin/booking-notes/${noteId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Xóa element khỏi DOM
                    const noteElement = document.querySelector(`[data-note-id="${noteId}"]`);
                    if (noteElement) {
                        noteElement.remove();
                    }
                    
                    // Cập nhật số lượng ghi chú
                    const badge = document.querySelector('.badge.bg-primary');
                    if (badge) {
                        const currentCount = parseInt(badge.textContent);
                        badge.textContent = currentCount - 1;
                    }
                    
                    if (typeof AdminUtils !== 'undefined' && AdminUtils.showToast) {
                        AdminUtils.showToast('Đã xóa ghi chú thành công!', 'success');
                    } else {
                        alert('Đã xóa ghi chú thành công!');
                    }
                } else {
                    alert('Có lỗi xảy ra: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Có lỗi xảy ra khi xóa ghi chú!');
            });
        }
    }

    // Tìm kiếm ghi chú
    $(document).ready(function() {
        $('#searchBtn').on('click', function() {
            const searchTerm = $('#noteSearch').val();
            searchNotes(searchTerm);
        });

        $('#noteSearch').on('keypress', function(e) {
            if (e.which === 13) {
                const searchTerm = $(this).val();
                searchNotes(searchTerm);
            }
        });
    });

    function searchNotes(searchTerm) {
        const noteItems = document.querySelectorAll('.note-item');
        noteItems.forEach(item => {
            const content = item.querySelector('.note-content').textContent.toLowerCase();
            const author = item.querySelector('strong').textContent.toLowerCase();
            
            if (content.includes(searchTerm.toLowerCase()) || author.includes(searchTerm.toLowerCase())) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
@endsection
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
    
    .registration-section .btn-group-vertical .btn {
        margin-bottom: 0.25rem;
    }
    
    .registration-section .btn-sm {
        font-size: 0.8rem;
        padding: 0.375rem 0.75rem;
    }
    
    
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