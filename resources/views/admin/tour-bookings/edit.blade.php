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
        <!-- Cột trái - Form chỉnh sửa -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-edit"></i> Chỉnh sửa thông tin Tour Booking
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tour-bookings.update', $tourBooking->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <!-- Thông tin cơ bản -->
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
                                    <select class="form-control @error('status') is-invalid @enderror" id="status" name="status" required>
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

                        <!-- Mã Tour Booking -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Mã Tour Booking</label>
                                    <input type="text" class="form-control bg-light" value="{{ $tourBooking->booking_code }}" readonly>
                                    <small class="text-muted">Mã này được tạo tự động và không thể thay đổi</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Số đêm</label>
                                    <input type="text" class="form-control bg-light" value="{{ $tourBooking->nights }} đêm" readonly>
                                    <small class="text-muted">Số đêm được tính tự động từ ngày check-in và check-out</small>
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
                                    <input type="text" class="form-control bg-light" value="{{ $tourBooking->total_rooms }} phòng" readonly>
                                    <small class="text-muted">Số phòng được tính tự động dựa trên room selections</small>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin thanh toán -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="payment_status" class="form-label">Trạng thái thanh toán</label>
                                    <select class="form-control @error('payment_status') is-invalid @enderror" id="payment_status" name="payment_status">
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
                                    <select class="form-control @error('preferred_payment_method') is-invalid @enderror" id="preferred_payment_method" name="preferred_payment_method">
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

            <!-- Chi tiết phòng hiện tại -->
            <div class="card mt-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-bed"></i> Chi tiết phòng hiện tại</h6>
                </div>
                <div class="card-body">
                    @if($tourBooking->tourBookingRooms->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Loại phòng</th>
                                        <th>Số lượng</th>
                                        <th>Số khách</th>
                                        <th>Giá/phòng</th>
                                        <th>Tổng tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tourBooking->tourBookingRooms as $room)
                                        <tr>
                                            <td>
                                                <strong class="text-dark">{{ $room->roomType->name }}</strong><br>
                                                <small class="text-muted">{{ $room->roomType->description }}</small>
                                            </td>
                                            <td class="text-center">
                                                <span class="bg-primary text-white px-2 py-1 rounded">{{ $room->quantity }} phòng</span>
                                            </td>
                                            <td class="text-center text-dark">{{ $room->quantity * 2 }} khách</td>
                                            <td class="text-right text-dark">{{ number_format($room->price_per_night, 0, ',', '.') }} VNĐ</td>
                                            <td class="text-right font-weight-bold text-dark">{{ number_format($room->total_price, 0, ',', '.') }} VNĐ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-info">
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Tổng tiền phòng:</strong></td>
                                        <td class="text-right font-weight-bold">{{ number_format($tourBooking->total_rooms_amount, 0, ',', '.') }} VNĐ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="alert alert-info mt-2">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Lưu ý:</strong> Chi tiết phòng không thể chỉnh sửa từ form này để đảm bảo dữ liệu khớp với bên chi tiết. 
                            Để thay đổi phòng, vui lòng sử dụng chức năng quản lý phòng riêng biệt.
                        </div>
                    @else
                        <p class="text-muted text-center my-3">Không có phòng nào được đặt</p>
                    @endif
                </div>
            </div>

            <!-- Dịch vụ hiện tại -->
            <div class="card mt-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-concierge-bell"></i> Dịch vụ hiện tại</h6>
                </div>
                <div class="card-body">
                    @if($tourBooking->tourBookingServices->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Dịch vụ</th>
                                        <th>Loại</th>
                                        <th>Đơn giá</th>
                                        <th>SL</th>
                                        <th>Thành tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tourBooking->tourBookingServices as $service)
                                        <tr>
                                            <td><strong class="text-dark">{{ $service->service_name }}</strong></td>
                                            <td><span class="bg-info text-white px-2 py-1 rounded">{{ $service->service_type }}</span></td>
                                            <td class="text-right text-dark">{{ number_format($service->price_per_unit, 0, ',', '.') }} VNĐ</td>
                                            <td class="text-center text-dark">{{ $service->quantity }}</td>
                                            <td class="text-right font-weight-bold text-dark">{{ number_format($service->total_price, 0, ',', '.') }} VNĐ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-info">
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Tổng tiền dịch vụ:</strong></td>
                                        <td class="text-right font-weight-bold">{{ number_format($tourBooking->total_services_amount, 0, ',', '.') }} VNĐ</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center my-3">Không có dịch vụ nào được chọn</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cột phải - Thông tin hiện tại -->
        <div class="col-lg-4">
            <!-- Thông tin hiện tại -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin hiện tại</h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Mã Booking:</label>
                        <div><span class="text-primary font-weight-bold">{{ $tourBooking->booking_code }}</span></div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Khách hàng:</label>
                        <div>{{ $tourBooking->user->name ?? 'N/A' }}</div>
                        <small class="text-muted">{{ $tourBooking->user->email ?? 'N/A' }}</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Tổng tiền:</label>
                        <div class="text-success font-weight-bold">{{ number_format($tourBooking->final_amount, 0, ',', '.') }} VNĐ</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Ngày tạo:</label>
                        <div>{{ $tourBooking->created_at ? $tourBooking->created_at->format('d/m/Y H:i') : 'N/A' }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Cập nhật lần cuối:</label>
                        <div>{{ $tourBooking->updated_at ? $tourBooking->updated_at->format('d/m/Y H:i') : 'N/A' }}</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Trạng thái thanh toán hiện tại:</label>
                        <div>
                            <span class="bg-{{ $tourBooking->isFullyPaid() ? 'success' : ($tourBooking->total_paid > 0 ? 'warning' : 'secondary') }} text-white px-2 py-1 rounded">
                                {{ $tourBooking->payment_status_text }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label font-weight-bold">Phương thức ưu tiên hiện tại:</label>
                        <div>
                            @switch($tourBooking->preferred_payment_method)
                                @case('credit_card')
                                    <i class="fas fa-credit-card text-primary"></i> Thẻ tín dụng
                                    @break
                                @case('bank_transfer')
                                    <i class="fas fa-university text-success"></i> Chuyển khoản ngân hàng
                                    @break
                                @case('cash')
                                    <i class="fas fa-money-bill text-warning"></i> Tiền mặt
                                    @break
                                @case('online_payment')
                                    <i class="fas fa-globe text-info"></i> Thanh toán trực tuyến
                                    @break
                                @default
                                    <span class="text-muted">Chưa chọn</span>
                            @endswitch
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tóm tắt thanh toán -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-credit-card"></i> Tóm tắt thanh toán</h6>
                </div>
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-6">Tổng tiền:</div>
                        <div class="col-6 text-right font-weight-bold">{{ number_format($tourBooking->final_amount, 0, ',', '.') }} VNĐ</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-6">Đã thanh toán:</div>
                        <div class="col-6 text-right text-success">{{ number_format($tourBooking->total_paid, 0, ',', '.') }} VNĐ</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">Còn lại:</div>
                        <div class="col-6 text-right font-weight-bold {{ $tourBooking->outstanding_amount > 0 ? 'text-danger' : 'text-success' }}">
                            {{ number_format($tourBooking->outstanding_amount, 0, ',', '.') }} VNĐ
                        </div>
                    </div>
                    
                    <div class="text-center mb-2">
                        <small class="text-muted">{{ $tourBooking->payments->count() }} giao dịch thanh toán</small>
                    </div>
                    
                    @if($tourBooking->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Phương thức</th>
                                        <th>Số tiền</th>
                                        <th>Trạng thái</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tourBooking->payments->take(3) as $payment)
                                        <tr>
                                            <td class="text-dark">
                                                @switch($payment->method)
                                                    @case('credit_card')
                                                        <i class="fas fa-credit-card text-primary"></i> Thẻ tín dụng
                                                        @break
                                                    @case('bank_transfer')
                                                        <i class="fas fa-university text-success"></i> Chuyển khoản
                                                        @break
                                                    @case('cash')
                                                        <i class="fas fa-money-bill-wave text-warning"></i> Tiền mặt
                                                        @break
                                                    @default
                                                        <i class="fas fa-question-circle text-secondary"></i> {{ $payment->method }}
                                                @endswitch
                                            </td>
                                            <td class="text-right text-dark">{{ number_format($payment->amount, 0, ',', '.') }}</td>
                                            <td class="text-center">
                                                @if($payment->status === 'completed')
                                                    <span class="bg-success text-white px-2 py-1 rounded">Hoàn thành</span>
                                                @elseif($payment->status === 'pending')
                                                    <span class="bg-warning text-white px-2 py-1 rounded">Chờ xử lý</span>
                                                @else
                                                    <span class="bg-danger text-white px-2 py-1 rounded">{{ $payment->status }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Thống kê nhanh</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <h4 class="text-primary mb-1">{{ $tourBooking->total_guests }}</h4>
                            <small class="text-muted">Tổng khách</small>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1">{{ $tourBooking->total_rooms }}</h4>
                            <small class="text-muted">Tổng phòng</small>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="text-center">
                        <h4 class="text-warning mb-1">{{ $tourBooking->nights }}</h4>
                        <small class="text-muted">Số đêm</small>
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
