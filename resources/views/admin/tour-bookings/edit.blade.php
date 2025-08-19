@extends('admin.layouts.admin-master')

@section('title', 'Chỉnh sửa Tour Booking')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">Chỉnh sửa Tour Booking</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tour-bookings.index') }}">Tour Bookings</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tour-bookings.show', $tourBooking->id) }}">Chi tiết</a></li>
                        <li class="breadcrumb-item active">Chỉnh sửa</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.tour-bookings.show', $tourBooking->id) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit text-warning me-2"></i>
                        Chỉnh sửa thông tin Tour Booking
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tour-bookings.update', $tourBooking->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tour_name" class="form-label">Tên Tour <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('tour_name') is-invalid @enderror" 
                                           id="tour_name" name="tour_name" value="{{ old('tour_name', $tourBooking->tour_name) }}" required>
                                    @error('tour_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="pending" {{ old('status', $tourBooking->status) === 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                                        <option value="confirmed" {{ old('status', $tourBooking->status) === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                        <option value="cancelled" {{ old('status', $tourBooking->status) === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                                        <option value="completed" {{ old('status', $tourBooking->status) === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_in_date" class="form-label">Ngày Check-in <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('check_in_date') is-invalid @enderror" 
                                           id="check_in_date" name="check_in_date" 
                                           value="{{ old('check_in_date', $tourBooking->check_in_date->format('Y-m-d')) }}" required>
                                    @error('check_in_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_out_date" class="form-label">Ngày Check-out <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('check_out_date') is-invalid @enderror" 
                                           id="check_out_date" name="check_out_date" 
                                           value="{{ old('check_out_date', $tourBooking->check_out_date->format('Y-m-d')) }}" required>
                                    @error('check_out_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="total_guests" class="form-label">Tổng số khách <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('total_guests') is-invalid @enderror" 
                                           id="total_guests" name="total_guests" 
                                           value="{{ old('total_guests', $tourBooking->total_guests) }}" min="1" required>
                                    @error('total_guests')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tổng số phòng</label>
                                    <input type="text" class="form-control" value="{{ $tourBooking->total_rooms }} phòng" readonly>
                                    <small class="text-muted">Số phòng được tính tự động dựa trên room selections</small>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin thanh toán -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">Trạng thái thanh toán</label>
                                    <select class="form-select @error('payment_status') is-invalid @enderror" id="payment_status" name="payment_status">
                                        <option value="">-- Chọn trạng thái thanh toán --</option>
                                        <option value="completed" {{ old('payment_status', $tourBooking->payment_status) === 'completed' ? 'selected' : '' }}>Hoàn tất thanh toán</option>
                                        <option value="partial" {{ old('payment_status', $tourBooking->payment_status) === 'partial' ? 'selected' : '' }}>Thanh toán một phần</option>
                                        <option value="pending" {{ old('payment_status', $tourBooking->payment_status) === 'pending' ? 'selected' : '' }}>Chưa thanh toán</option>
                                        <option value="overdue" {{ old('payment_status', $tourBooking->payment_status) === 'overdue' ? 'selected' : '' }}>Quá hạn thanh toán</option>
                                    </select>
                                    <small class="text-muted">Trạng thái tổng quan của việc thanh toán</small>
                                    @error('payment_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="preferred_payment_method" class="form-label">Phương thức thanh toán ưu tiên</label>
                                    <select class="form-select @error('preferred_payment_method') is-invalid @enderror" id="preferred_payment_method" name="preferred_payment_method">
                                        <option value="">-- Chọn phương thức --</option>
                                        <option value="credit_card" {{ old('preferred_payment_method', $tourBooking->preferred_payment_method ?? '') === 'credit_card' ? 'selected' : '' }}>Thẻ tín dụng</option>
                                        <option value="bank_transfer" {{ old('preferred_payment_method', $tourBooking->preferred_payment_method ?? '') === 'bank_transfer' ? 'selected' : '' }}>Chuyển khoản ngân hàng</option>
                                        <option value="cash" {{ old('preferred_payment_method', $tourBooking->preferred_payment_method ?? '') === 'cash' ? 'selected' : '' }}>Tiền mặt</option>
                                        <option value="online_payment" {{ old('preferred_payment_method', $tourBooking->preferred_payment_method ?? '') === 'online_payment' ? 'selected' : '' }}>Thanh toán trực tuyến</option>
                                    </select>
                                    <small class="text-muted">Phương thức thanh toán được khách hàng ưu tiên</small>
                                    @error('preferred_payment_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Yêu cầu đặc biệt</label>
                            <textarea class="form-control @error('special_requests') is-invalid @enderror" 
                                      id="special_requests" name="special_requests" rows="3" 
                                      placeholder="Nhập yêu cầu đặc biệt nếu có...">{{ old('special_requests', $tourBooking->special_requests) }}</textarea>
                            @error('special_requests')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.tour-bookings.show', $tourBooking->id) }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Cập nhật
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Thông tin hiện tại -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Thông tin hiện tại
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Mã Booking:</label>
                        <div><span class="badge bg-info">{{ $tourBooking->booking_id }}</span></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Khách hàng:</label>
                        <div>{{ $tourBooking->user->name }}</div>
                        <small class="text-muted">{{ $tourBooking->user->email }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tổng tiền:</label>
                        <div class="text-success fs-5">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Ngày tạo:</label>
                        <div>{{ $tourBooking->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cập nhật lần cuối:</label>
                        <div>{{ $tourBooking->updated_at->format('d/m/Y H:i') }}</div>
                    </div>
                    
                    @if($tourBooking->payment_status)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Trạng thái thanh toán hiện tại:</label>
                            <div>
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
                            </div>
                        </div>
                    @endif
                    
                    @if($tourBooking->preferred_payment_method)
                        <div class="mb-3">
                            <label class="form-label fw-bold">Phương thức ưu tiên hiện tại:</label>
                            <div>
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
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Trạng thái thanh toán -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-credit-card text-warning me-2"></i>
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
                            <span class="badge bg-success fs-6 px-3 py-2">
                                <i class="fas fa-check-circle me-2"></i>Hoàn tất
                            </span>
                        @elseif($completedAmount > 0)
                            <span class="badge bg-warning fs-6 px-3 py-2">
                                <i class="fas fa-clock me-2"></i>Một phần
                            </span>
                        @else
                            <span class="badge bg-light text-dark fs-6 px-3 py-2">
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
                        <div class="text-center mb-3">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                {{ $tourBooking->payments->count() }} giao dịch thanh toán
                            </small>
                        </div>
                        
                        <!-- Danh sách giao dịch thanh toán -->
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless">
                                <thead>
                                    <tr class="text-muted small">
                                        <th>Phương thức</th>
                                        <th>Số tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tourBooking->payments->take(3) as $payment)
                                        <tr class="small">
                                            <td>
                                                @switch($payment->payment_method)
                                                    @case('credit_card')
                                                        <i class="fas fa-credit-card text-primary"></i>
                                                        @break
                                                    @case('bank_transfer')
                                                        <i class="fas fa-university text-success"></i>
                                                        @break
                                                    @default
                                                        <i class="fas fa-money-bill text-secondary"></i>
                                                @endswitch
                                            </td>
                                            <td>{{ number_format($payment->amount, 0, ',', '.') }}</td>
                                            <td>
                                                @switch($payment->status)
                                                    @case('completed')
                                                        <span class="badge bg-success bg-sm">Hoàn thành</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="badge bg-warning bg-sm">Chờ xử lý</span>
                                                        @break
                                                    @case('failed')
                                                        <span class="badge bg-danger bg-sm">Thất bại</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary bg-sm">{{ $payment->status }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if($tourBooking->payments->count() > 3)
                            <div class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-ellipsis-h"></i>
                                    Và {{ $tourBooking->payments->count() - 3 }} giao dịch khác
                                </small>
                            </div>
                        @endif
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.tour-bookings.show', $tourBooking->id) }}" 
                               class="btn btn-outline-info btn-sm">
                                <i class="fas fa-eye me-1"></i>Xem chi tiết thanh toán
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Chi tiết phòng hiện tại -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bed text-success me-2"></i>
                        Chi tiết phòng hiện tại
                    </h5>
                </div>
                <div class="card-body">
                    @foreach($tourBooking->tourBookingRooms as $tourBookingRoom)
                        <div class="border-bottom pb-2 mb-2">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong>{{ $tourBookingRoom->roomType->name }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $tourBookingRoom->quantity }} phòng × {{ $tourBookingRoom->guests_per_room }} khách
                                    </small>
                                </div>
                                <div class="text-end">
                                    <div class="text-success fw-bold">
                                        {{ number_format($tourBookingRoom->total_price, 0, ',', '.') }} VNĐ
                                    </div>
                                    <small class="text-muted">
                                        {{ number_format($tourBookingRoom->price_per_room, 0, ',', '.') }} VNĐ/phòng
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Chi tiết phòng không thể chỉnh sửa từ form này
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Validate check-out date must be after check-in date
document.getElementById('check_out_date').addEventListener('change', function() {
    const checkInDate = document.getElementById('check_in_date').value;
    const checkOutDate = this.value;
    
    if (checkInDate && checkOutDate && checkOutDate <= checkInDate) {
        this.setCustomValidity('Ngày check-out phải sau ngày check-in');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});

// Validate check-in date must be before check-out date
document.getElementById('check_in_date').addEventListener('change', function() {
    const checkInDate = this.value;
    const checkOutDate = document.getElementById('check_out_date').value;
    
    if (checkInDate && checkOutDate && checkOutDate <= checkInDate) {
        document.getElementById('check_out_date').setCustomValidity('Ngày check-out phải sau ngày check-in');
        document.getElementById('check_out_date').classList.add('is-invalid');
    } else {
        document.getElementById('check_out_date').setCustomValidity('');
        document.getElementById('check_out_date').classList.remove('is-invalid');
    }
});

// Auto-calculate total guests when room selections change
document.getElementById('total_guests').addEventListener('input', function() {
    const totalGuests = parseInt(this.value) || 0;
    if (totalGuests < 1) {
        this.setCustomValidity('Tổng số khách phải lớn hơn 0');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
    }
});
</script>
@endsection
