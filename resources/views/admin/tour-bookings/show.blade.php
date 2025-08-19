@extends('admin.layouts.admin-master')

@section('title', 'Chi tiết Tour Booking')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">Chi tiết Tour Booking</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tour-bookings.index') }}">Tour Bookings</a></li>
                        <li class="breadcrumb-item active">Chi tiết</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.tour-bookings.edit', $tourBooking->id) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Chỉnh sửa
                </a>
                <a href="{{ route('admin.tour-bookings.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Thông tin chính -->
        <div class="col-lg-8">
            <!-- Thông tin Tour Booking -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        Thông tin Tour Booking
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">ID:</td>
                                    <td>{{ $tourBooking->id }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Mã Booking:</td>
                                    <td><span class="badge bg-info fs-6">{{ $tourBooking->booking_id }}</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tên Tour:</td>
                                    <td><strong>{{ $tourBooking->tour_name }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Trạng thái:</td>
                                    <td>
                                        @switch($tourBooking->status)
                                            @case('pending')
                                                <span class="badge bg-warning fs-6">Chờ xác nhận</span>
                                                @break
                                            @case('confirmed')
                                                <span class="badge bg-success fs-6">Đã xác nhận</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger fs-6">Đã hủy</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-info fs-6">Hoàn thành</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary fs-6">{{ $tourBooking->status }}</span>
                                        @endswitch
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-bold">Check-in:</td>
                                    <td>{{ $tourBooking->check_in_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Check-out:</td>
                                    <td>{{ $tourBooking->check_out_date->format('d/m/Y') }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Số đêm:</td>
                                    <td>{{ $tourBooking->total_nights }} đêm</td>
                                </tr>
                                <tr>
                                    <td class="fw-bold">Tổng tiền:</td>
                                    <td><strong class="text-success fs-5">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</strong></td>
                                </tr>
                                @if($tourBooking->payment_status)
                                    <tr>
                                        <td class="fw-bold">Trạng thái thanh toán:</td>
                                        <td>
                                            @switch($tourBooking->payment_status)
                                                @case('completed')
                                                    <span class="badge bg-success">Hoàn tất thanh toán</span>
                                                    @break
                                                @case('partial')
                                                    <span class="badge bg-warning">Thanh toán một phần</span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge bg-light text-dark">Chưa thanh toán</span>
                                                    @break
                                                @case('overdue')
                                                    <span class="badge bg-danger">Quá hạn thanh toán</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $tourBooking->payment_status }}</span>
                                            @endswitch
                                        </td>
                                    </tr>
                                @endif
                                @if($tourBooking->preferred_payment_method)
                                    <tr>
                                        <td class="fw-bold">Phương thức ưu tiên:</td>
                                        <td>
                                            @switch($tourBooking->preferred_payment_method)
                                                @case('credit_card')
                                                    <i class="fas fa-credit-card text-primary me-2"></i>Thẻ tín dụng
                                                    @break
                                                @case('bank_transfer')
                                                    <i class="fas fa-university text-success me-2"></i>Chuyển khoản ngân hàng
                                                    @break
                                                @case('cash')
                                                    <i class="fas fa-money-bill text-warning me-2"></i>Tiền mặt
                                                    @break
                                                @case('online_payment')
                                                    <i class="fas fa-globe text-info me-2"></i>Thanh toán trực tuyến
                                                    @break
                                                @default
                                                    {{ $tourBooking->preferred_payment_method }}
                                            @endswitch
                                        </td>
                                    </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    @if($tourBooking->special_requests)
                        <div class="mt-3">
                            <h6 class="fw-bold">Yêu cầu đặc biệt:</h6>
                            <p class="text-muted">{{ $tourBooking->special_requests }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Chi tiết phòng -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bed text-success me-2"></i>
                        Chi tiết phòng đã đặt
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead class="table-dark">
                                <tr>
                                <th class="text-dark">Loại phòng</th>
                                    <th class="text-dark">Số lượng</th>
                                    <th class="text-dark">Số khách/phòng</th>
                                    <th class="text-dark">Giá/phòng</th>
                                    <th class="text-dark">Tổng tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($tourBooking->tourBookingRooms as $tourBookingRoom)
                                    <tr>
                                        <td>
                                            <strong>{{ $tourBookingRoom->roomType->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $tourBookingRoom->roomType->description }}</small>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ $tourBookingRoom->quantity }} phòng</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $tourBookingRoom->guests_per_room }} người</span>
                                        </td>
                                        <td>{{ number_format($tourBookingRoom->price_per_room, 0, ',', '.') }} VNĐ</td>
                                        <td>
                                            <strong class="text-success">{{ number_format($tourBookingRoom->total_price, 0, ',', '.') }} VNĐ</strong>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-info">
                                <tr>
                                    <td colspan="4" class="text-end fw-bold">Tổng tiền:</td>
                                    <td class="fw-bold fs-5">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Lịch sử thanh toán -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-credit-card text-warning me-2"></i>
                        Lịch sử thanh toán
                    </h5>
                </div>
                <div class="card-body">
                    @if($tourBooking->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Phương thức</th>
                                        <th>Số tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tourBooking->payments as $payment)
                                        <tr>
                                            <td>
                                                @switch($payment->payment_method)
                                                    @case('credit_card')
                                                        <i class="fas fa-credit-card text-primary me-2"></i>
                                                        Thẻ tín dụng
                                                        @break
                                                    @case('bank_transfer')
                                                        <i class="fas fa-university text-success me-2"></i>
                                                        Chuyển khoản
                                                        @break
                                                    @default
                                                        <i class="fas fa-money-bill text-secondary me-2"></i>
                                                        {{ $payment->payment_method }}
                                                @endswitch
                                            </td>
                                            <td>
                                                <strong>{{ number_format($payment->amount, 0, ',', '.') }} VNĐ</strong>
                                            </td>
                                            <td>
                                                @switch($payment->status)
                                                    @case('pending')
                                                        <span class="badge bg-warning">Chờ xử lý</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge bg-success">Hoàn thành</span>
                                                        @break
                                                    @case('failed')
                                                        <span class="badge bg-danger">Thất bại</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $payment->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    @if($payment->status === 'pending')
                                                        <form action="{{ route('admin.tour-bookings.payments.update-status', [$tourBooking->id, $payment->id]) }}" method="POST" class="me-1">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="completed">
                                                            <button type="submit" class="btn btn-success">
                                                                <i class="fas fa-check"></i> Xác nhận
                                                            </button>
                                                        </form>
                                                        <form action="{{ route('admin.tour-bookings.payments.update-status', [$tourBooking->id, $payment->id]) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <input type="hidden" name="status" value="failed">
                                                            <button type="submit" class="btn btn-danger">
                                                                <i class="fas fa-times"></i> Thất bại
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                            <h6 class="text-muted">Chưa có lịch sử thanh toán</h6>
                            <p class="text-muted mb-0">Tour booking này chưa có bất kỳ giao dịch thanh toán nào.</p>
                        </div>
                    @endif
                    
                    <!-- Tóm tắt thanh toán -->
                    <div class="mt-3">
                        <div class="row text-center">
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h6 class="text-muted mb-1">Tổng tiền cần thanh toán</h6>
                                    <h5 class="text-primary">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h6 class="text-muted mb-1">Đã thanh toán</h6>
                                    <h5 class="text-success">
                                        {{ number_format($tourBooking->payments->where('status', 'completed')->sum('amount'), 0, ',', '.') }} VNĐ
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border-end">
                                    <h6 class="text-muted mb-1">Còn lại</h6>
                                    <h5 class="text-warning">
                                        {{ number_format($tourBooking->total_price - $tourBooking->payments->where('status', 'completed')->sum('amount'), 0, ',', '.') }} VNĐ
                                    </h5>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted mb-1">Trạng thái thanh toán</h6>
                                @php
                                    $completedAmount = $tourBooking->payments->where('status', 'completed')->sum('amount');
                                    $totalAmount = $tourBooking->total_price;
                                @endphp
                                @if($completedAmount >= $totalAmount)
                                    <span class="badge bg-success fs-6">Hoàn tất</span>
                                @elseif($completedAmount > 0)
                                    <span class="badge bg-warning fs-6">Thanh toán một phần</span>
                                @else
                                    <span class="badge bg-light text-dark fs-6">Chưa thanh toán</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Thông tin khách hàng -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user text-primary me-2"></i>
                        Thông tin khách hàng
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-4x text-primary"></i>
                    </div>
                    <h6 class="fw-bold text-center">{{ $tourBooking->user->name }}</h6>
                    <p class="text-muted text-center mb-3">{{ $tourBooking->user->email }}</p>
                    
                    <div class="d-grid gap-2">
                        <a href="mailto:{{ $tourBooking->user->email }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-envelope me-2"></i>Gửi email
                        </a>
                        @if($tourBooking->user->phone)
                            <a href="tel:{{ $tourBooking->user->phone }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-phone me-2"></i>Gọi điện
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Trạng thái thanh toán -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-credit-card text-info me-2"></i>
                        Trạng thái thanh toán
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $completedAmount = $tourBooking->payments->where('status', 'completed')->sum('amount');
                        $totalAmount = $tourBooking->total_price;
                        $remainingAmount = $totalAmount - $completedAmount;
                    @endphp
                    
                    <div class="text-center mb-3">
                        @if($completedAmount >= $totalAmount)
                            <span class="badge bg-success fs-5 px-3 py-2">
                                <i class="fas fa-check-circle me-2"></i>Thanh toán hoàn tất
                            </span>
                        @elseif($completedAmount > 0)
                            <span class="badge bg-warning fs-5 px-3 py-2">
                                <i class="fas fa-clock me-2"></i>Thanh toán một phần
                            </span>
                        @else
                            <span class="badge bg-light text-dark fs-5 px-3 py-2">
                                <i class="fas fa-minus me-2"></i>Chưa thanh toán
                            </span>
                        @endif
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Tổng tiền:</span>
                            <strong>{{ number_format($totalAmount, 0, ',', '.') }} VNĐ</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Đã thanh toán:</span>
                            <strong class="text-success">{{ number_format($completedAmount, 0, ',', '.') }} VNĐ</strong>
                        </div>
                    </div>
                    
                    @if($remainingAmount > 0)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted">Còn lại:</span>
                                <strong class="text-warning">{{ number_format($remainingAmount, 0, ',', '.') }} VNĐ</strong>
                            </div>
                        </div>
                    @endif
                    
                    @if($tourBooking->payments->count() > 0)
                        <hr>
                        <div class="text-center">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Có {{ $tourBooking->payments->count() }} giao dịch thanh toán
                            </small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cập nhật trạng thái -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog text-warning me-2"></i>
                        Cập nhật trạng thái
                    </h5>
                </div>
                <div class="card-body">
                    <form id="statusUpdateForm">
                        @csrf
                        <div class="mb-3">
                            <label for="status" class="form-label">Trạng thái mới</label>
                            <select class="form-select" id="status" name="status">
                                @foreach($validNextStatuses as $nextStatus)
                                    <option value="{{ $nextStatus }}">
                                        @switch($nextStatus)
                                            @case('confirmed')
                                                Đã xác nhận
                                                @break
                                            @case('cancelled')
                                                Đã hủy
                                                @break
                                            @case('completed')
                                                Hoàn thành
                                                @break
                                            @default
                                                {{ $nextStatus }}
                                        @endswitch
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100" id="updateStatusBtn">
                            <i class="fas fa-save me-2"></i>Cập nhật
                        </button>
                    </form>
                    
                    @if(empty($validNextStatuses))
                        <div class="alert alert-info text-center mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Không thể thay đổi trạng thái từ trạng thái hiện tại
                        </div>
                    @endif
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar text-info me-2"></i>
                        Thống kê nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-1">{{ $tourBooking->total_guests }}</h4>
                                <small class="text-muted">Tổng khách</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1">{{ $tourBooking->total_rooms }}</h4>
                            <small class="text-muted">Tổng phòng</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h4 class="text-warning mb-1">{{ $tourBooking->total_nights }}</h4>
                        <small class="text-muted">Số đêm</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Details Modal -->
<div class="modal fade" id="paymentDetailsModal" tabindex="-1" aria-labelledby="paymentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentDetailsModalLabel">Chi tiết thanh toán</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="paymentDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
// Cập nhật trạng thái
document.getElementById('statusUpdateForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const status = document.getElementById('status').value;
    const updateBtn = document.getElementById('updateStatusBtn');
    
    updateBtn.disabled = true;
    updateBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Đang cập nhật...';
    
    fetch(`/admin/tour-bookings/{{ $tourBooking->id }}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Reload page to show updated status
            location.reload();
        } else {
            alert('Có lỗi xảy ra: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Có lỗi xảy ra khi cập nhật trạng thái');
    })
    .finally(() => {
        updateBtn.disabled = false;
        updateBtn.innerHTML = '<i class="fas fa-save me-2"></i>Cập nhật';
    });
});

// Hiển thị chi tiết thanh toán
function showPaymentDetails(paymentId) {
    // In a real application, you would fetch payment details via AJAX
    // For now, we'll show a simple message
    document.getElementById('paymentDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
            <h6 class="text-muted">Chi tiết thanh toán</h6>
            <p class="text-muted">Chức năng này sẽ được phát triển trong phiên bản tiếp theo.</p>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('paymentDetailsModal')).show();
}
</script>
@endsection
