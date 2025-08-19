@extends('admin.layouts.admin-master')

@section('title', 'Quản lý Tour Bookings')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">Quản lý Tour Bookings</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Tour Bookings</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.tour-bookings.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tạo Tour Booking
                </a>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.tour-bookings.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                        <option value="confirmed" {{ $status === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="cancelled" {{ $status === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                        <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="payment_status" class="form-label">Trạng thái thanh toán</label>
                    <select name="payment_status" id="payment_status" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="paid" {{ $paymentStatus === 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                        <option value="unpaid" {{ $paymentStatus === 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Lọc
                        </button>
                        <a href="{{ route('admin.tour-bookings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Xóa lọc
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tour Bookings Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">Danh sách Tour Bookings</h5>
        </div>
        <div class="card-body">
            @if($tourBookings->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr class="text-center">
                                <th class="text-dark">ID</th>
                                <th class="text-dark">Mã Booking</th>
                                <th class="text-dark">Khách hàng</th>
                                <th class="text-dark">Tour</th>
                                <th class="text-dark">Check-in</th>
                                <th class="text-dark">Check-out</th>
                                <th class="text-dark">Số khách</th>
                                <th class="text-dark">Số phòng</th>
                                <th class="text-dark">Tổng tiền</th>
                                <th class="text-dark">Trạng thái</th>
                                <th class="text-dark">Thanh toán</th>
                                <th class="text-dark">Ngày tạo</th>
                                <th class="text-dark">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tourBookings as $tourBooking)
                                <tr>
                                    <td>{{ $tourBooking->id }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $tourBooking->booking_id }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <i class="fas fa-user-circle fa-2x text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $tourBooking->user->name }}</div>
                                                <small class="text-muted">{{ $tourBooking->user->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <strong>{{ $tourBooking->tour_name }}</strong>
                                    </td>
                                    <td>{{ $tourBooking->check_in_date->format('d/m/Y') }}</td>
                                    <td>{{ $tourBooking->check_out_date->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-primary">{{ $tourBooking->total_guests }} người</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $tourBooking->total_rooms }} phòng</span>
                                    </td>
                                    <td>
                                        <strong class="text-success">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</strong>
                                    </td>
                                    <td>
                                        @switch($tourBooking->status)
                                            @case('pending')
                                                <span class="badge bg-warning">Chờ xác nhận</span>
                                                @break
                                            @case('confirmed')
                                                <span class="badge bg-success">Đã xác nhận</span>
                                                @break
                                            @case('cancelled')
                                                <span class="badge bg-danger">Đã hủy</span>
                                                @break
                                            @case('completed')
                                                <span class="badge bg-info">Hoàn thành</span>
                                                @break
                                            @default
                                                <span class="badge bg-secondary">{{ $tourBooking->status }}</span>
                                        @endswitch
                                    </td>
                                    <td>
                                        @php
                                            $completedPayments = $tourBooking->payments->where('status', 'completed');
                                            $pendingPayments = $tourBooking->payments->where('status', 'pending');
                                            $failedPayments = $tourBooking->payments->where('status', 'failed');
                                            $hasAnyPayment = $tourBooking->payments->count() > 0;
                                        @endphp
                                        
                                        @if($tourBooking->payment_status)
                                            @switch($tourBooking->payment_status)
                                                @case('completed')
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check"></i> Hoàn tất
                                                    </span>
                                                    @break
                                                @case('partial')
                                                    <span class="badge bg-warning">
                                                        <i class="fas fa-clock"></i> Một phần
                                                    </span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge bg-light text-dark">
                                                        <i class="fas fa-minus"></i> Chưa thanh toán
                                                    </span>
                                                    @break
                                                @case('overdue')
                                                    <span class="badge bg-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> Quá hạn
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">{{ $tourBooking->payment_status }}</span>
                                            @endswitch
                                            @if($tourBooking->preferred_payment_method)
                                                <br>
                                                <small class="text-muted">
                                                    @switch($tourBooking->preferred_payment_method)
                                                        @case('credit_card')
                                                            <i class="fas fa-credit-card"></i> Thẻ tín dụng
                                                            @break
                                                        @case('bank_transfer')
                                                            <i class="fas fa-university"></i> Chuyển khoản
                                                            @break
                                                        @case('cash')
                                                            <i class="fas fa-money-bill"></i> Tiền mặt
                                                            @break
                                                        @case('online_payment')
                                                            <i class="fas fa-globe"></i> Trực tuyến
                                                            @break
                                                        @default
                                                            {{ $tourBooking->preferred_payment_method }}
                                                    @endswitch
                                                </small>
                                            @endif
                                        @else
                                            @if($completedPayments->count() > 0)
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check"></i> Đã thanh toán
                                                </span>
                                            @elseif($pendingPayments->count() > 0)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock"></i> Một phần
                                                </span>
                                            @else
                                                <span class="badge bg-light text-dark">
                                                    <i class="fas fa-minus"></i> Chưa thanh toán
                                                </span>
                                            @endif
                                        @endif
                                    </td>
                                    <td>{{ $tourBooking->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.tour-bookings.show', $tourBooking->id) }}" 
                                               class="btn btn-sm btn-outline-primary" title="Xem chi tiết">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.tour-bookings.edit', $tourBooking->id) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteTourBooking({{ $tourBooking->id }})" title="Xóa">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $tourBookings->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Không có tour booking nào</h5>
                    <p class="text-muted">Hãy tạo tour booking đầu tiên hoặc thay đổi bộ lọc.</p>
                    <a href="{{ route('admin.tour-bookings.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo Tour Booking
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Xác nhận xóa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Bạn có chắc chắn muốn xóa tour booking này? Hành động này không thể hoàn tác.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Xóa</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function deleteTourBooking(id) {
    if (confirm('Bạn có chắc chắn muốn xóa tour booking này?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/tour-bookings/${id}`;
        
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        
        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'DELETE';
        
        form.appendChild(csrfToken);
        form.appendChild(methodField);
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-submit form when filters change
document.getElementById('status').addEventListener('change', function() {
    this.form.submit();
});

document.getElementById('payment_status').addEventListener('change', function() {
    this.form.submit();
});
</script>
@endsection
