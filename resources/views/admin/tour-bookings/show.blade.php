@extends('admin.layouts.admin-master')

@section('title', 'Chi tiết Tour Booking')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-0">Chi tiết Tour Booking</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.tour-bookings.index') }}">Tour Bookings</a></li>
                    <li class="breadcrumb-item active">Chi tiết</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('admin.tour-bookings.edit', $tourBooking->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Chỉnh sửa
            </a>
            <a href="{{ route('admin.tour-bookings.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Cột trái - Thông tin chính -->
        <div class="col-lg-8">
            <!-- Thông tin Tour Booking -->
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin Tour Booking</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="row mb-2">
                                <div class="col-4"><strong>ID:</strong></div>
                                <div class="col-8">{{ $tourBooking->id }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Mã Booking:</strong></div>
                                <div class="col-8">
                                    <span class="text-primary font-weight-bold">{{ $tourBooking->booking_code }}</span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Tên Tour:</strong></div>
                                <div class="col-8">{{ $tourBooking->tour_name }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Trạng thái:</strong></div>
                                <div class="col-8">
                                                                            <span class="text-{{ $tourBooking->status === 'confirmed' ? 'success' : ($tourBooking->status === 'pending' ? 'warning' : ($tourBooking->status === 'cancelled' ? 'danger' : 'secondary')) }} font-weight-bold">
                                            {{ $tourBooking->status_text }}
                                        </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row mb-2">
                                <div class="col-4"><strong>Check-in:</strong></div>
                                <div class="col-8">{{ $tourBooking->check_in_date }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Check-out:</strong></div>
                                <div class="col-8">{{ $tourBooking->check_out_date }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Số đêm:</strong></div>
                                <div class="col-8">{{ $tourBooking->nights }} đêm</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4"><strong>Trạng thái TT:</strong></div>
                                <div class="col-8">
                                                                            <span class="text-{{ $tourBooking->isFullyPaid() ? 'success' : ($tourBooking->total_paid > 0 ? 'warning' : 'secondary') }} font-weight-bold">
                                            <i class="{{ $tourBooking->payment_status_icon }}"></i>
                                            {{ $tourBooking->payment_status_text }}
                                        </span>
                                </div>
                            </div>
                            @if($totalDiscount > 0)
                                <div class="row mb-2">
                                    <div class="col-4"><strong>Mã giảm giá:</strong></div>
                                    <div class="col-8">
                                        <span class="text-success font-weight-bold">
                                            <i class="fas fa-tag"></i>
                                            {{ $tourBooking->promotion_code }} (-{{ number_format($totalDiscount, 0, ',', '.') }} VNĐ)
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chi tiết giá và dịch vụ -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-calculator"></i> Chi tiết giá và dịch vụ</h5>
                </div>
                <div class="card-body">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs" id="priceTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="rooms-tab" data-toggle="tab" href="#rooms" role="tab">
                                <i class="fas fa-bed"></i> Chi tiết phòng ({{ $tourBooking->tourBookingRooms->count() }})
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="services-tab" data-toggle="tab" href="#services" role="tab">
                                <i class="fas fa-concierge-bell"></i> Dịch vụ ({{ $tourBooking->tourBookingServices->count() }})
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content mt-3" id="priceTabsContent">
                        <!-- Tab Phòng -->
                        <div class="tab-pane fade show active" id="rooms" role="tabpanel">
                            @if($tourBooking->tourBookingRooms->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Loại phòng</th>
                                                <th>Số lượng</th>
                                                <th>Giá/phòng</th>
                                                <th>Tổng tiền</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tourBooking->tourBookingRooms as $room)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $room->roomType->name }}</strong><br>
                                                        <small class="text-muted">{{ $room->roomType->description }}</small>
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="bg-primary text-white px-2 py-1 rounded">{{ $room->quantity }} phòng</span>
                                                    </td>
                                                    <td class="text-right">{{ number_format($room->price_per_night, 0, ',', '.') }} VNĐ</td>
                                                    <td class="text-right font-weight-bold">{{ number_format($room->total_price, 0, ',', '.') }} VNĐ</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-info">
                                            <tr>
                                                <td colspan="3" class="text-right"><strong>Tổng tiền phòng:</strong></td>
                                                <td class="text-right font-weight-bold">{{ number_format($tourBooking->total_rooms_amount, 0, ',', '.') }} VNĐ</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center my-3">Không có phòng nào được đặt</p>
                            @endif
                        </div>

                        <!-- Tab Dịch vụ -->
                        <div class="tab-pane fade" id="services" role="tabpanel">
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
                                                    <td><strong>{{ $service->service_name }}</strong></td>
                                                    <td><span class="bg-info text-white px-2 py-1 rounded">{{ $service->service_type }}</span></td>
                                                    <td class="text-right">{{ number_format($service->price_per_unit, 0, ',', '.') }} VNĐ</td>
                                                    <td class="text-center">{{ $service->quantity }}</td>
                                                    <td class="text-right font-weight-bold">{{ number_format($service->total_price, 0, ',', '.') }} VNĐ</td>
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

                    <!-- Tóm tắt giá -->
                    <div class="mt-3">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-calculator"></i> Tóm tắt giá</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row mb-1">
                                            <div class="col-6 text-dark">Tiền phòng:</div>
                                            <div class="col-6 text-right text-dark">{{ number_format($totalRoomsAmount, 0, ',', '.') }} VNĐ</div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-6 text-dark">Tiền dịch vụ:</div>
                                            <div class="col-6 text-right text-dark">
                                                {{ number_format($totalServicesAmount, 0, ',', '.') }} VNĐ
                                                @if($totalServicesAmount > 0)
                                                    <br><small class="text-info">(Bao gồm dịch vụ bổ sung)</small>
                                                @endif
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row mb-1">
                                            <div class="col-6"><strong class="text-dark">Tổng cộng:</strong></div>
                                            <div class="col-6 text-right font-weight-bold text-warning">{{ number_format($totalAmountBeforeDiscount, 0, ',', '.') }} VNĐ</div>
                                        </div>
                                        @if($totalDiscount > 0)
                                            <div class="row mb-1">
                                                <div class="col-6 text-dark">Giảm giá:</div>
                                                <div class="col-6 text-right text-success">-{{ number_format($totalDiscount, 0, ',', '.') }} VNĐ</div>
                                            </div>
                                            @if($tourBooking->promotion_code)
                                                <div class="row mb-1">
                                                    <div class="col-6 text-dark">Mã giảm giá:</div>
                                                    <div class="col-6 text-right text-muted small">{{ $tourBooking->promotion_code }}</div>
                                                </div>
                                            @endif
                                            <hr class="my-2">
                                            <div class="row">
                                                <div class="col-6"><strong class="text-dark">Giá cuối:</strong></div>
                                                <div class="col-6 text-right font-weight-bold text-primary">{{ number_format($finalAmount, 0, ',', '.') }} VNĐ</div>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i>
                                                    <strong>Lưu ý:</strong> Giá cuối đã được áp dụng mã giảm giá. Số tiền thanh toán sẽ dựa trên giá cuối này.
                                                </small>
                                            </div>
                                        @else
                                            <div class="row">
                                                <div class="col-6"><strong class="text-dark">Giá cuối:</strong></div>
                                                <div class="col-6 text-right font-weight-bold text-primary">{{ number_format($finalAmount, 0, ',', '.') }} VNĐ</div>
                                            </div>
                                            @if($totalServicesAmount > 0)
                                                <div class="mt-2">
                                                    <small class="text-info">
                                                        <i class="fas fa-info-circle"></i>
                                                        <strong>Lưu ý:</strong> Giá cuối bao gồm cả dịch vụ bổ sung mới được thêm.
                                                    </small>
                                                </div>
                                            @endif
                                            
                                        @endif
                                        
                                        <!-- Thông tin VAT -->
                                        <hr class="my-2">
                                        <div class="row mb-1">
                                            <div class="col-6 text-dark">Giá trước VAT:</div>
                                            <div class="col-6 text-right text-muted">{{ number_format(round($finalAmount / 1.1), 0, ',', '.') }} VNĐ</div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-6 text-dark">Thuế VAT (10%):</div>
                                            <div class="col-6 text-right text-muted">{{ number_format($finalAmount - round($finalAmount / 1.1), 0, ',', '.') }} VNĐ</div>
                                        </div>
                                        <div class="row">
                                            <div class="col-6"><strong class="text-dark">Tổng cộng (đã bao gồm VAT):</strong></div>
                                            <div class="col-6 text-right font-weight-bold text-success">{{ number_format($finalAmount, 0, ',', '.') }} VNĐ</div>
                                        </div>
                                        <div class="mt-2">
                                            <small class="text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Lưu ý:</strong> Giá cuối đã bao gồm VAT 10%, không thu thêm phí. Hóa đơn VAT chỉ để khách kê khai thuế.
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-credit-card"></i> Tình trạng thanh toán</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        @php
                                            // Tính toán chính xác trạng thái thanh toán
                                            $totalCompletedPayments = $tourBooking->payments->where('status', 'completed')->sum('amount');
                                            $totalPendingPayments = $tourBooking->payments->where('status', 'pending')->sum('amount');
                                            $totalProcessingPayments = $tourBooking->payments->where('status', 'processing')->sum('amount');
                                            
                                            // Xác định trạng thái thanh toán
                                            if ($totalCompletedPayments >= $finalAmount) {
                                                $paymentStatus = 'paid';
                                                $paymentStatusText = 'Đã thanh toán đủ';
                                                $paymentStatusClass = 'success';
                                            } elseif ($totalCompletedPayments > 0) {
                                                $paymentStatus = 'partial';
                                                $paymentStatusText = 'Thanh toán một phần';
                                                $paymentStatusClass = 'warning';
                                            } else {
                                                $paymentStatus = 'unpaid';
                                                $paymentStatusText = 'Chưa thanh toán';
                                                $paymentStatusClass = 'danger';
                                            }
                                            
                                            // Tính số tiền còn lại (chỉ dựa trên completed payments)
                                            $remainingAmount = max(0, $finalAmount - $totalCompletedPayments);
                                            $completionPercentage = $finalAmount > 0 ? round(($totalCompletedPayments / $finalAmount) * 100, 1) : 0;
                                        @endphp
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <h6 class="text-{{ $paymentStatusClass }}">{{ $paymentStatusText }}</h6>
                                                <p class="mb-1"><strong>Tổng tiền:</strong> {{ number_format($finalAmount, 0, ',', '.') }} VNĐ</p>
                                                <p class="mb-1"><strong>Đã thanh toán:</strong> {{ number_format($totalCompletedPayments, 0, ',', '.') }} VNĐ</p>
                                                @if($totalPendingPayments > 0)
                                                    <p class="mb-1 text-warning"><strong>Đang chờ xác nhận:</strong> {{ number_format($totalPendingPayments, 0, ',', '.') }} VNĐ</p>
                                                @endif
                                                @if($totalProcessingPayments > 0)
                                                    <p class="mb-1 text-info"><strong>Đang xử lý:</strong> {{ number_format($totalProcessingPayments, 0, ',', '.') }} VNĐ</p>
                                                @endif
                                                <p class="mb-1"><strong>Còn lại:</strong> {{ number_format($remainingAmount, 0, ',', '.') }} VNĐ</p>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="progress mb-2" style="height: 25px;">
                                                    <div class="progress-bar bg-{{ $paymentStatusClass }}" role="progressbar" 
                                                         style="width: {{ $completionPercentage }}%" 
                                                         aria-valuenow="{{ $completionPercentage }}" 
                                                         aria-valuemin="0" aria-valuemax="100">
                                                        {{ $completionPercentage }}%
                                                    </div>
                                                </div>
                                                <small class="text-muted">Tỷ lệ hoàn thành: {{ $completionPercentage }}%</small>
                                            </div>
                                        </div>
                                        
                                        @if($paymentStatus === 'paid')
                                            <div class="alert alert-success mt-2 mb-0">
                                                <i class="fas fa-check-circle"></i>
                                                <strong>Hoàn thành thanh toán!</strong> Khách hàng đã thanh toán đủ tiền.
                                            </div>
                                        @elseif($paymentStatus === 'partial')
                                            <div class="alert alert-warning mt-2 mb-0">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong>Thanh toán một phần!</strong> Khách hàng còn thiếu {{ number_format($remainingAmount, 0, ',', '.') }} VNĐ.
                                            </div>
                                        @else
                                            <div class="alert alert-danger mt-2 mb-0">
                                                <i class="fas fa-times-circle"></i>
                                                <strong>Chưa thanh toán!</strong> Khách hàng cần thanh toán {{ number_format($finalAmount, 0, ',', '.') }} VNĐ.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Cột phải - Thông tin phụ -->
        <div class="col-lg-4">
            <!-- Thông tin khách hàng -->
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-user"></i> Thông tin khách hàng</h6>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-circle fa-3x text-primary"></i>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Tên:</strong></div>
                        <div class="col-8">{{ $tourBooking->user->name ?? 'Khách' }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-4"><strong>Email:</strong></div>
                        <div class="col-8">{{ $tourBooking->user->email ?? 'N/A' }}</div>
                    </div>
                    @if($tourBooking->user->phone)
                    <div class="row mb-2">
                        <div class="col-4"><strong>Điện thoại:</strong></div>
                        <div class="col-8">{{ $tourBooking->user->phone }}</div>
                    </div>
                    @endif
                    <div class="row mb-2">
                        <div class="col-4"><strong>Ngày đặt:</strong></div>
                        <div class="col-8">{{ $tourBooking->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-4"><strong>Mã tour:</strong></div>
                        <div class="col-8"><code>{{ $tourBooking->booking_code }}</code></div>
                    </div>
                    <div class="btn-group-vertical w-100">
                        <a href="mailto:{{ $tourBooking->user->email ?? '#' }}" class="btn btn-sm btn-outline-primary mb-1">
                            <i class="fas fa-envelope"></i> Gửi email
                        </a>
                        @if($tourBooking->user->phone)
                        <a href="tel:{{ $tourBooking->user->phone }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-phone"></i> Gọi điện
                        </a>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Trạng thái thanh toán -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-credit-card"></i> Trạng thái thanh toán</h6>
                </div>
                <div class="card-body">
                    @php
                        // Sử dụng logic đã tính toán ở trên
                        $paymentStatusDisplay = $paymentStatus ?? 'unpaid';
                        $paymentStatusTextDisplay = $paymentStatusText ?? 'Chưa thanh toán';
                        $paymentStatusClassDisplay = $paymentStatusClass ?? 'danger';
                        $remainingAmountDisplay = $remainingAmount ?? $finalAmount;
                        $completionPercentageDisplay = $completionPercentage ?? 0;
                    @endphp
                    
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-{{ $paymentStatusClassDisplay }}">{{ $paymentStatusTextDisplay }}</h6>
                            <p class="mb-1"><strong>Tổng tiền:</strong> {{ number_format($finalAmount, 0, ',', '.') }} VNĐ</p>
                            <p class="mb-1"><strong>Đã thanh toán:</strong> {{ number_format($totalCompletedPayments ?? 0, 0, ',', '.') }} VNĐ</p>
                            @if(($totalPendingPayments ?? 0) > 0)
                                <p class="mb-1 text-warning"><strong>Đang chờ xác nhận:</strong> {{ number_format($totalPendingPayments ?? 0, 0, ',', '.') }} VNĐ</p>
                            @endif
                            @if(($totalProcessingPayments ?? 0) > 0)
                                <p class="mb-1 text-info"><strong>Đang xử lý:</strong> {{ number_format($totalProcessingPayments ?? 0, 0, ',', '.') }} VNĐ</p>
                            @endif
                            <p class="mb-1"><strong>Còn lại:</strong> {{ number_format($remainingAmountDisplay, 0, ',', '.') }} VNĐ</p>
                        </div>
                        <div class="col-md-6">
                            <div class="progress mb-2" style="height: 25px;">
                                <div class="progress-bar bg-{{ $paymentStatusClassDisplay }}" role="progressbar" 
                                     style="width: {{ $completionPercentageDisplay }}%" 
                                     aria-valuenow="{{ $completionPercentageDisplay }}" 
                                     aria-valuemin="0" aria-valuemax="100">
                                    {{ $completionPercentageDisplay }}%
                                </div>
                            </div>
                            <small class="text-muted">Tỷ lệ hoàn thành: {{ $completionPercentageDisplay }}%</small>
                        </div>
                    </div>
                    
                    @if($paymentStatusDisplay === 'paid')
                        <div class="alert alert-success mt-2 mb-0">
                            <i class="fas fa-check-circle"></i>
                            <strong>Hoàn thành thanh toán!</strong> Khách hàng đã thanh toán đủ tiền.
                        </div>
                    @elseif($paymentStatusDisplay === 'partial')
                        <div class="alert alert-warning mt-2 mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Thanh toán một phần!</strong> Khách hàng còn thiếu {{ number_format($remainingAmountDisplay, 0, ',', '.') }} VNĐ.
                        </div>
                    @else
                        <div class="alert alert-danger mt-2 mb-0">
                            <i class="fas fa-times-circle"></i>
                            <strong>Chưa thanh toán!</strong> Khách hàng cần thanh toán {{ number_format($finalAmount, 0, ',', '.') }} VNĐ.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Xác nhận chuyển khoản -->
            @if($tourBooking->payments->where('method', 'bank_transfer')->where('status', 'pending')->count() > 0)
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-clock"></i> Xác nhận chuyển khoản</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <strong>Thông tin:</strong> Có {{ $tourBooking->payments->where('method', 'bank_transfer')->where('status', 'pending')->count() }} giao dịch chuyển khoản đang chờ xác nhận.
                        <br><small class="text-muted">Lưu ý: Khi admin thu tiền bổ sung, các giao dịch này sẽ được tự động xác nhận.</small>
                    </div>
                    
                    @foreach($tourBooking->payments->where('method', 'bank_transfer')->where('status', 'pending') as $payment)
                    <div class="border rounded p-2 mb-2">
                        <div class="row mb-1">
                            <div class="col-6"><small><strong>Mã GD:</strong></small></div>
                            <div class="col-6 text-right"><small class="text-muted">{{ $payment->transaction_id }}</small></div>
                        </div>
                        <div class="row mb-1">
                            <div class="col-6"><small><strong>Số tiền:</strong></small></div>
                            <div class="col-6 text-right"><small class="font-weight-bold">{{ number_format($payment->amount, 0, ',', '.') }} VNĐ</small></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small><strong>Ngày tạo:</strong></small></div>
                            <div class="col-6 text-right"><small class="text-muted">{{ $payment->created_at->format('d/m/Y H:i') }}</small></div>
                        </div>
                        
                        <div class="row">
                            <div class="col-6">
                                <form action="{{ route('admin.tour-bookings.confirm-bank-transfer', $tourBooking->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                    <input type="hidden" name="transaction_id" value="{{ $payment->transaction_id }}">
                                    <button type="submit" class="btn btn-success btn-sm btn-block" onclick="return confirm('Xác nhận giao dịch chuyển khoản này?')">
                                        <i class="fas fa-check"></i> Xác nhận
                                    </button>
                                </form>
                            </div>
                            <div class="col-6">
                                <button type="button" class="btn btn-danger btn-sm btn-block" onclick="showRejectionModal({{ $payment->id }}, '{{ $payment->transaction_id }}')">
                                    <i class="fas fa-times"></i> Từ chối
                                </button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Debug: Hiển thị tất cả payments -->
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin Payments (Debug)</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <strong>Tổng số payments:</strong> {{ $tourBooking->payments->count() }}
                        <br><strong>Payments chuyển khoản:</strong> {{ $tourBooking->payments->where('method', 'bank_transfer')->count() }}
                        <br><strong>Payments pending:</strong> {{ $tourBooking->payments->where('status', 'pending')->count() }}
                        <br><strong>Payments completed:</strong> {{ $tourBooking->payments->where('status', 'completed')->count() }}
                    </div>
                    
                    @if($tourBooking->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Transaction ID</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tourBooking->payments as $payment)
                                    <tr>
                                        <td>{{ $payment->id }}</td>
                                        <td>{{ $payment->method }}</td>
                                        <td>{{ number_format($payment->amount, 0, ',', '.') }} VNĐ</td>
                                        <td>
                                            <span class="badge badge-{{ $payment->status === 'completed' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'secondary') }}">
                                                {{ $payment->status }}
                                            </span>
                                        </td>
                                        <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                        <td>{{ $payment->transaction_id }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <strong>Chưa có payment nào!</strong> Khách hàng cần bấm nút "Báo đã thanh toán" để tạo payment record.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cập nhật trạng thái -->
            <div class="card mb-3">
                <div class="card-header bg-secondary text-white">
                    <h6 class="mb-0"><i class="fas fa-edit"></i> Cập nhật trạng thái</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tour-bookings.update-status', $tourBooking->id) }}" method="POST" id="updateStatusForm">
                        @csrf
                        @method('PATCH')
                        <div class="form-group">
                            <label class="small">Trạng thái hiện tại:</label>
                            <div class="mb-2">
                                <span class="badge bg-{{ $tourBooking->status === 'confirmed' ? 'success' : ($tourBooking->status === 'pending' ? 'warning' : 'secondary') }} text-white px-3 py-2">
                                    @switch($tourBooking->status)
                                        @case('pending')
                                            Chờ xác nhận
                                            @break
                                        @case('pending_payment')
                                            Chờ thanh toán
                                            @break
                                        @case('confirmed')
                                            Đã xác nhận
                                            @break
                                        @case('checked_in')
                                            Đã check-in
                                            @break
                                        @case('checked_out')
                                            Đã check-out
                                            @break
                                        @case('completed')
                                            Hoàn thành
                                            @break
                                        @case('cancelled')
                                            Đã hủy
                                            @break
                                        @case('no_show')
                                            Không đến
                                            @break
                                        @default
                                            {{ $tourBooking->status }}
                                    @endswitch
                                </span>
                            </div>
                            <label class="small">Chuyển sang trạng thái:</label>
                            <select name="status" class="form-control form-control-sm" required>
                                <option value="">-- Chọn trạng thái mới --</option>
                                @foreach($validNextStatuses as $status)
                                    <option value="{{ $status }}">
                                        @switch($status)
                                            @case('pending')
                                                Chờ xác nhận
                                                @break
                                            @case('pending_payment')
                                                Chờ thanh toán
                                                @break
                                            @case('confirmed')
                                                Đã xác nhận
                                                @break
                                            @case('checked_in')
                                                Đã check-in
                                                @break
                                            @case('checked_out')
                                                Đã check-out
                                                @break
                                            @case('completed')
                                                Hoàn thành
                                                @break
                                            @case('cancelled')
                                                Đã hủy
                                                @break
                                            @case('no_show')
                                                Không đến
                                                @break
                                            @default
                                                {{ $status }}
                                        @endswitch
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        
                        
                        <button type="submit" class="btn btn-primary btn-sm btn-block" id="updateStatusBtn">
                            <i class="fas fa-save"></i> Cập nhật trạng thái
                        </button>
                    </form>
                </div>
            </div>

            <!-- Thu tiền bổ sung -->
            @php
                $hasPendingBankTransfer = $tourBooking->payments->where('method', 'bank_transfer')->where('status', 'pending')->count() > 0;
                $hasCompletedPayment = $tourBooking->payments->where('status', 'completed')->count() > 0;
                $canShowCollectPayment = !$hasPendingBankTransfer || $hasCompletedPayment;
            @endphp
            
            @if($canShowCollectPayment)
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-money-bill-wave"></i> Thu tiền bổ sung</h6>
                </div>
                <div class="card-body">
                    @if($hasPendingBankTransfer && !$hasCompletedPayment)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong> Có giao dịch chuyển khoản đang chờ xác nhận. 
                            Bạn có thể xác nhận hoặc từ chối giao dịch đó trước khi thu tiền bổ sung.
                        </div>
                    @endif
                    
                    <form action="{{ route('admin.tour-bookings.collect-payment', $tourBooking->id) }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="amount">Số tiền thu (VNĐ):</label>
                            <input type="number" name="amount" id="amount" class="form-control" 
                                   value="{{ max(0, ($tourBooking->final_price ?? $tourBooking->total_price) - $tourBooking->payments->where('status', 'completed')->sum('amount')) }}" 
                                   min="0" step="1000" required>
                            <small class="form-text text-muted">
                                Số tiền tối đa có thể thu: {{ number_format(max(0, ($tourBooking->final_price ?? $tourBooking->total_price) - $tourBooking->payments->where('status', 'completed')->sum('amount')), 0, ',', '.') }} VNĐ
                            </small>
                        </div>
                        <button type="submit" class="btn btn-warning" onclick="return confirm('Xác nhận thu tiền bổ sung?')">
                            <i class="fas fa-money-bill-wave"></i> Thu tiền
                        </button>
                    </form>
                </div>
            </div>
            @endif
            
            <!-- Thông báo đã thanh toán đủ -->
            @if($outstandingAmount <= 0)
                <div class="card mb-3">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-check-circle"></i> Đã thanh toán đủ</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-success mb-0">
                            <strong>Trạng thái thanh toán:</strong>
                            <i class="fas fa-check-circle"></i> Đã thanh toán đủ tiền
                            <br><small class="text-success">Có thể xuất hóa đơn VAT</small>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- Lịch sử thanh toán -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Lịch sử thanh toán</h5>
                </div>
                <div class="card-body">
                    @if($tourBooking->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Phương thức</th>
                                        <th>Số tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tourBooking->payments as $payment)
                                        <tr>
                                            <td>
                                                @switch($payment->method)
                                                    @case('credit_card')
                                                        <i class="fas fa-credit-card text-primary text-black"></i> Thẻ tín dụng
                                                        @break
                                                    @case('bank_transfer')
                                                        <i class="fas fa-university text-success text-black"></i> Chuyển khoản
                                                        @break
                                                    @case('cash')
                                                        <i class="fas fa-money-bill-wave text-warning text-black"></i> Tiền mặt
                                                        @break
                                                    @default
                                                        <i class="fas fa-question-circle text-secondary text-black"></i> {{ $payment->method }}
                                                @endswitch
                                            </td>
                                            <td class="text-right font-weight-bold">{{ number_format($payment->amount, 0, ',', '.') }} VNĐ</td>
                                            <td class="text-center">
                                                @if($payment->status === 'completed')
                                                    <span class="bg-success text-white px-2 py-1 rounded">Hoàn thành</span>
                                                @elseif($payment->status === 'pending')
                                                    <span class="bg-warning text-white px-2 py-1 rounded">Chờ xử lý</span>
                                                @else
                                                    <span class="bg-danger text-white px-2 py-1 rounded">{{ $payment->status }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                            <td>{{ $payment->notes ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted text-center my-3">Chưa có giao dịch thanh toán nào</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Notes và VAT Invoice -->
    <div class="row mt-3">
        <!-- Notes -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Ghi chú ({{ $tourBooking->tourBookingNotes->count() }})</h6>
                </div>
                <div class="card-body">
                    @if($tourBooking->tourBookingNotes->count() > 0)
                        @foreach($tourBooking->tourBookingNotes->take(3) as $note)
                            <div class="border-bottom pb-2 mb-2">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">{{ $note->created_at ? $note->created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                                                                                    <span class="bg-{{ $note->type === 'admin' ? 'danger' : ($note->type === 'staff' ? 'warning' : 'info') }} text-white px-2 py-1 rounded">
                                                    {{ ucfirst($note->type) }}
                                                </span>
                                </div>
                                <p class="mb-1">{{ $note->content }}</p>
                                <small class="text-muted">Bởi: {{ $note->user->name ?? 'N/A' }}</small>
                            </div>
                        @endforeach
                        @if($tourBooking->tourBookingNotes->count() > 3)
                            <div class="text-center">
                                <small class="text-muted">Hiển thị 3 ghi chú gần nhất trong tổng số {{ $tourBooking->tourBookingNotes->count() }} ghi chú</small>
                            </div>
                        @endif
                    @else
                        <p class="text-muted text-center my-3">Chưa có ghi chú nào</p>
                    @endif
                </div>
            </div>
        </div>

                <!-- VAT Invoice -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-file-invoice-dollar me-1"></i>
                    Hóa đơn VAT
                    @if($tourBooking->vat_invoice_number)
                        <small class="text-muted">(Số HĐ: {{ $tourBooking->vat_invoice_number }})</small>
                    @endif
                </div>
                <div class="card-body">
                    @if($tourBooking->need_vat_invoice)
                        <!-- Thông tin công ty -->
                        <div class="row small">
                            <div class="col-md-4"><strong>Công ty:</strong> {{ $tourBooking->company_name ?? 'N/A' }}</div>
                            <div class="col-md-4"><strong>MST:</strong> {{ $tourBooking->company_tax_code ?? 'N/A' }}</div>
                            <div class="col-md-4"><strong>Email nhận HĐ:</strong> {{ $tourBooking->company_email ?? 'N/A' }}</div>
                            <div class="col-12 mt-1"><strong>Địa chỉ:</strong> {{ $tourBooking->company_address ?? 'N/A' }}</div>
                            @if($tourBooking->company_phone)
                                <div class="col-12 mt-1"><strong>Điện thoại:</strong> {{ $tourBooking->company_phone }}</div>
                            @endif
                        </div>
                        
                        <!-- Các nút chức năng VAT -->
                        <div class="mt-3">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-cogs"></i> Chức năng hóa đơn VAT
                            </h6>
                            <div class="d-flex gap-2 flex-wrap">
                            @if(!$tourBooking->vat_invoice_file_path)
                                <form action="{{ route('admin.tour-vat-invoices.generate', $tourBooking->id) }}" method="POST" class="me-2 mb-1">
                                    @csrf
                                    <button class="btn btn-outline-primary btn-sm" 
                                            title="Tạo hóa đơn VAT PDF">
                                        <i class="fas fa-file-pdf"></i> Tạo hóa đơn PDF
                                    </button>
                                    <small class="text-info d-block mt-1">Có thể tạo hóa đơn VAT ngay cả khi chưa thanh toán đủ tiền</small>
                                </form>
                            @endif
                            
                            @if($tourBooking->vat_invoice_file_path)
                                <!-- Debug info cho VAT file path -->
                                <div class="w-100 mb-2 p-2 bg-warning rounded">
                                    <small class="text-dark">
                                        <strong>DEBUG VAT:</strong><br>
                                        - File Path: "{{ $tourBooking->vat_invoice_file_path }}"<br>
                                        - Empty Check: {{ empty($tourBooking->vat_invoice_file_path) ? 'YES' : 'NO' }}<br>
                                        - Length: {{ strlen($tourBooking->vat_invoice_file_path) }}<br>
                                        - Condition: {{ $tourBooking->vat_invoice_file_path ? 'TRUE' : 'FALSE' }}<br>
                                        - VAT Number: {{ $tourBooking->vat_invoice_number ?? 'N/A' }}<br>
                                        - File Exists: {{ file_exists(public_path('storage/' . str_replace('public/', '', $tourBooking->vat_invoice_file_path))) ? 'YES' : 'NO' }}
                                    </small>
                                </div>
                                
                                <!-- Nút Xem hóa đơn -->
                                <a class="btn btn-info btn-sm me-2 mb-1" href="{{ route('admin.tour-vat-invoices.preview', $tourBooking->id) }}" target="_blank"
                                   title="Xem trước hóa đơn VAT">
                                    <i class="fas fa-eye"></i> Xem hóa đơn
                                </a>
                                
                                <!-- Nút Tải xuống -->
                                <a class="btn btn-secondary btn-sm me-2 mb-1" href="{{ route('admin.tour-vat-invoices.download', $tourBooking->id) }}"
                                   title="Tải xuống file PDF hóa đơn VAT">
                                    <i class="fas fa-download"></i> Tải xuống
                                </a>
                                
                                <!-- Nút Sửa hóa đơn (Tạo lại) -->
                                <form action="{{ route('admin.tour-vat-invoices.regenerate', $tourBooking->id) }}" method="POST" class="d-inline me-2 mb-1">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm" 
                                            onclick="return confirm('Bạn có chắc muốn tạo lại file hóa đơn VAT? File cũ sẽ bị xóa.')"
                                            title="Tạo lại hóa đơn VAT">
                                        <i class="fas fa-edit"></i> Sửa hóa đơn
                                    </button>
                                    <small class="text-info d-block mt-1">Có thể tạo lại hóa đơn VAT</small>
                                </form>
                                
                                <!-- Thông tin file đã tạo -->
                                <div class="w-100 mt-2">
                                    <small class="text-success">
                                        <i class="fas fa-check-circle"></i> 
                                        <strong>Hóa đơn đã được tạo:</strong> 
                                        {{ $tourBooking->vat_invoice_number ?? 'N/A' }}
                                        <br>
                                        <span class="text-muted">
                                            <i class="fas fa-clock"></i> 
                                            {{ $tourBooking->vat_invoice_generated_at ? $tourBooking->vat_invoice_generated_at->format('d/m/Y H:i') : 'N/A' }}
                                        </span>
                                        @if($tourBooking->vat_invoice_sent_at)
                                            <br>
                                            <span class="text-info">
                                                <i class="fas fa-envelope-open"></i> 
                                                Đã gửi email: {{ $tourBooking->vat_invoice_sent_at->format('d/m/Y H:i') }}
                                            </span>
                                        @endif
                                    </small>
                                </div>
                            @endif
                            
                            <!-- Nút Gửi email hóa đơn -->
                            <form action="{{ route('admin.tour-vat-invoices.send', $tourBooking->id) }}" method="POST" class="mb-1">
                                @csrf
                                <button class="btn btn-primary btn-sm" 
                                        {{ empty($tourBooking->vat_invoice_file_path) ? 'disabled' : '' }}
                                        title="{{ empty($tourBooking->vat_invoice_file_path) ? 'Cần tạo hóa đơn PDF trước' : 'Gửi email hóa đơn VAT' }}">
                                    <i class="fas fa-envelope"></i> Gửi email hóa đơn
                                </button>
                                @if(empty($tourBooking->vat_invoice_file_path))
                                    <small class="text-muted d-block mt-1">Cần tạo hóa đơn PDF trước</small>
                                @else
                                    <small class="text-info d-block mt-1">Có thể gửi email hóa đơn VAT</small>
                                @endif
                            </form>
                            </div>
                        </div>
                        
                        <!-- Thông tin VAT -->
                        <div class="alert alert-light mt-3 mb-0">
                            <i class="fas fa-info-circle"></i>
                            <strong>Thông tin VAT:</strong> Giá cuối đã bao gồm VAT 10%, không thu thêm phí. Hóa đơn chỉ để khách kê khai thuế.
                            <br><small class="text-muted">Sử dụng logic tính toán mới nhất quán với regular booking</small>
                            <br><small class="text-muted">Điều kiện: Có thể tạo và gửi hóa đơn VAT ngay cả khi chưa thanh toán đủ tiền</small>
                            <br><small class="text-muted">Hệ thống hoạt động giống hệt như regular booking</small>
                        </div>
                        
                        <!-- Thông tin trạng thái thanh toán -->
                        <div class="alert alert-info mt-3 mb-0">
                            <strong>Trạng thái thanh toán:</strong>
                            @if($outstandingAmount <= 0)
                                <i class="fas fa-check-circle"></i> Đã thanh toán đủ tiền
                                <br><small class="text-success">Có thể xuất hóa đơn VAT</small>
                            @else
                                <i class="fas fa-info-circle"></i> Chưa thanh toán đủ tiền
                                <br><small class="text-info">Còn thiếu: {{ number_format($outstandingAmount) }} VNĐ</small>
                                <br><small class="text-info">Tuy nhiên, vẫn có thể tạo và gửi hóa đơn VAT</small>
                            @endif
                        </div>
                        
                        <!-- Cảnh báo hóa đơn lớn -->
                        @if($paymentInfo['isHighValue'])
                            <div class="alert alert-warning mt-3 mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Lưu ý quan trọng:</strong> Hóa đơn từ {{ number_format(5000000) }}₫ ({{ number_format($paymentInfo['totalDue']) }} VNĐ) theo quy định pháp luật nên thanh toán bằng thẻ/tài khoản công ty hoặc chuyển khoản công ty.
                                <br><small class="text-muted">Tuy nhiên, vẫn có thể tạo hóa đơn VAT nếu khách đã thanh toán đủ tiền.</small>
                                <br><small class="text-muted">Giá hiển thị đã bao gồm VAT 10%, không thu thêm phí.</small>
                            </div>
                        @endif
                        
                        <!-- Form từ chối yêu cầu -->
                        @if(!$tourBooking->vat_invoice_file_path)
                            <hr class="my-3">
                            <form action="{{ route('admin.tour-vat-invoices.reject', $tourBooking->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label class="small">Lý do từ chối:</label>
                                    <textarea name="rejection_reason" class="form-control form-control-sm" rows="2" 
                                              placeholder="Nhập lý do từ chối..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger btn-sm btn-block">
                                    <i class="fas fa-times"></i> Từ chối yêu cầu
                                </button>
                            </form>
                        @endif
                        
                    @else
                        <p class="text-muted text-center my-3">Không yêu cầu hóa đơn VAT</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Thêm dịch vụ -->
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h6 class="mb-0"><i class="fas fa-plus"></i> Thêm dịch vụ</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tour-bookings.services.store', $tourBooking->id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">Loại dịch vụ</label>
                                    <select name="service_type" class="form-control form-control-sm" required>
                                        <option value="">Chọn dịch vụ</option>
                                        <option value="transport">Vận chuyển</option>
                                        <option value="guide">Hướng dẫn viên</option>
                                        <option value="meal">Bữa ăn</option>
                                        <option value="entertainment">Giải trí</option>
                                        <option value="other">Khác</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="small">Tên dịch vụ</label>
                                    <input type="text" name="service_name" class="form-control form-control-sm" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="small">Đơn giá (VNĐ)</label>
                                    <input type="number" name="unit_price" class="form-control form-control-sm" min="0" step="1000" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="small">Số lượng</label>
                                    <input type="number" name="quantity" class="form-control form-control-sm" min="1" value="1" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="small">&nbsp;</label>
                                    <button type="submit" class="btn btn-success btn-sm btn-block">
                                        <i class="fas fa-plus"></i> Thêm
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
$(document).ready(function() {
    // Khởi tạo tabs
    $('#priceTabs a').on('click', function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    // Xử lý form cập nhật trạng thái
    $('#updateStatusForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#updateStatusBtn');
        const originalText = submitBtn.html();
        
        // Debug info
        console.log('Form submitted:', {
            action: form.attr('action'),
            method: 'PATCH',
            data: form.serialize(),
            csrf: $('meta[name="csrf-token"]').attr('content')
        });
        
        // Disable button và hiển thị loading
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Đang cập nhật...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'PATCH',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Hiển thị thông báo thành công
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công!',
                        text: response.message,
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        // Reload trang để cập nhật trạng thái
                        location.reload();
                    });
                } else {
                    // Hiển thị thông báo lỗi
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi!',
                        text: response.message || 'Có lỗi xảy ra khi cập nhật trạng thái'
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Có lỗi xảy ra khi cập nhật trạng thái';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi!',
                    text: errorMessage
                });
            },
            complete: function() {
                // Restore button
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
{{-- <style>
    .badge {
        color: black !important;
    }
</style> --}}
@endsection

@section('scripts')
<script>
    function showRejectionModal(paymentId, transactionId) {
        document.getElementById('reject_payment_id').value = paymentId;
        document.getElementById('reject_transaction_id').value = transactionId;
        $('#rejectionModal').modal('show');
    }
</script>
@endsection
