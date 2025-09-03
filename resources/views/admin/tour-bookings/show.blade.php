@extends('admin.layouts.admin-master')

@section('title', 'Chi tiết Tour Booking')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
@php
    // Tính toán các biến cần thiết cho toàn bộ view
    $totalRoomsAmount = $tourBooking->tourBookingRooms->sum('total_price');
    $totalServicesAmount = $tourBooking->tourBookingServices->sum('total_price');
    $finalPriceWithServices = $totalRoomsAmount + $totalServicesAmount;
    
    // Số tiền giảm giá và tổng trước giảm
    $totalDiscount = $tourBooking->promotion_discount ?? 0;
    $totalAmountBeforeDiscount = $finalPriceWithServices;
    
    // Giá cuối sau giảm giá (chuẩn)
    $finalAmount = max(0, $totalAmountBeforeDiscount - $totalDiscount);
    
    // Đồng bộ biến finalPrice để các phần dưới dùng thống nhất
    $finalPrice = $finalAmount;
    
    // Tính toán các biến thanh toán
    $totalCompletedPayments = $tourBooking->payments->where('status', 'completed')->sum('amount');
    $totalPendingPayments = $tourBooking->payments->where('status', 'pending')->sum('amount');
    $totalProcessingPayments = $tourBooking->payments->where('status', 'processing')->sum('amount');
    $remainingAmount = max(0, $finalAmount - $totalCompletedPayments);
    $completionPercentage = $finalAmount > 0 ? round(($totalCompletedPayments / $finalAmount) * 100, 1) : 0;
@endphp
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
            @php
                $pendingRoomChanges = \App\Models\TourRoomChange::where('tour_booking_id', $tourBooking->id)
                    ->where('status', 'pending')
                    ->count();
            @endphp
            
            @if($pendingRoomChanges > 0)
                <a href="{{ route('staff.admin.tour-bookings.room-changes.index', $tourBooking->id) }}" class="btn btn-warning mr-2">
                    <i class="fas fa-exchange-alt"></i> Yêu cầu đổi phòng ({{ $pendingRoomChanges }})
                </a>
            @endif
            
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
                                    <span class="text-primary font-weight-bold">{{ $tourBooking->booking_id }}</span>
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
                                <div class="col-8">{{ $tourBooking->total_nights }} đêm</div>
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
                                                <th>Phòng đã gán</th>
                                                <th>Giá/phòng</th>
                                                <th>Tổng tiền</th>
                                                <th>Thao tác</th>
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
                                                        <span class="px-2 py-1">{{ $room->quantity }} phòng</span>
                                                    </td>
                                                    <td>
                                                        @if(!empty($room->assigned_room_ids))
                                                            @foreach($room->assigned_room_ids as $roomId)
                                                                @php $assignedRoom = \App\Models\Room::find($roomId); @endphp
                                                                @if($assignedRoom)
                                                                    <span class="badge badge-primary mr-1">{{ $assignedRoom->room_number }}</span>
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            <span class="text-muted">Chưa gán phòng</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-right">{{ number_format($room->price_per_room, 0, ',', '.') }} VNĐ</td>
                                                    <td class="text-right font-weight-bold">{{ number_format($room->total_price, 0, ',', '.') }} VNĐ</td>
                                                    <td>
                                                        <a href="{{ route('staff.admin.tour-bookings.room-changes.index', $tourBooking->id) }}" 
                                                           class="btn btn-sm btn-info" title="Xem yêu cầu đổi phòng">
                                                            <i class="fas fa-exchange-alt"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-info">
                                            <tr>
                                                <td colspan="4" class="text-right"><strong>Tổng tiền phòng:</strong></td>
                                                <td class="text-right font-weight-bold">{{ number_format($tourBooking->total_rooms_amount, 0, ',', '.') }} VNĐ</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                
                                <!-- Thông báo yêu cầu đổi phòng -->
                                @php
                                    $pendingRoomChanges = \App\Models\TourRoomChange::where('tour_booking_id', $tourBooking->id)
                                        ->where('status', 'pending')
                                        ->count();
                                @endphp
                                
                                @if($pendingRoomChanges > 0)
                                    <div class="alert alert-warning alert-dismissible fade show mt-3" role="alert">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Có {{ $pendingRoomChanges }} yêu cầu đổi phòng đang chờ duyệt!</strong>
                                        <a href="{{ route('staff.admin.tour-bookings.room-changes.index', $tourBooking->id) }}" class="btn btn-warning btn-sm ml-2">
                                            <i class="fas fa-eye"></i> Xem chi tiết
                                        </a>
                                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                @endif
                                @php
                                    $pendingChanges = \App\Models\TourRoomChange::where('tour_booking_id', $tourBooking->id)->where('status','pending')->count();
                                @endphp
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
                                            // Xác định trạng thái thanh toán
                                            // Chỉ hiển thị "Đã thanh toán đủ" khi KHÔNG có payment pending
                                            if ($totalCompletedPayments >= $finalAmount && $totalPendingPayments == 0) {
                                                $paymentStatus = 'paid';
                                                $paymentStatusText = 'Đã thanh toán đủ';
                                                $paymentStatusClass = 'success';
                                            } elseif ($totalCompletedPayments > 0 || $totalPendingPayments > 0) {
                                                $paymentStatus = 'partial';
                                                if ($totalPendingPayments > 0) {
                                                    $paymentStatusText = 'Có giao dịch chờ xác nhận';
                                                    $paymentStatusClass = 'warning';
                                                } else {
                                                    $paymentStatusText = 'Thanh toán một phần';
                                                    $paymentStatusClass = 'warning';
                                                }
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
                                            @if($totalPendingPayments > 0)
                                                <div class="alert alert-warning mt-2 mb-0">
                                                    <i class="fas fa-clock"></i>
                                                    <strong>Có giao dịch chờ xác nhận!</strong> 
                                                    Có {{ number_format($totalPendingPayments, 0, ',', '.') }} VNĐ đang chờ admin xác nhận.
                                                    <br><small class="text-muted">Vui lòng xác nhận hoặc từ chối giao dịch chuyển khoản trước.</small>
                                                </div>
                                            @else
                                                <div class="alert alert-warning mt-2 mb-0">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <strong>Thanh toán một phần!</strong> Khách hàng còn thiếu {{ number_format($remainingAmount, 0, ',', '.') }} VNĐ.
                                                </div>
                                            @endif
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
                        <div class="col-8"><code>{{ $tourBooking->booking_id }}</code></div>
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
                        @if(($totalPendingPayments ?? 0) > 0)
                            <div class="alert alert-warning mt-2 mb-0">
                                <i class="fas fa-clock"></i>
                                <strong>Có giao dịch chờ xác nhận!</strong>
                                Có {{ number_format($totalPendingPayments ?? 0, 0, ',', '.') }} VNĐ đang chờ admin xác nhận.
                                <br><small class="text-muted">Vui lòng xác nhận hoặc từ chối giao dịch thanh toán trước.</small>
                            </div>
                        @else
                            <div class="alert alert-warning mt-2 mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Thanh toán một phần!</strong> Khách hàng còn thiếu {{ number_format($remainingAmountDisplay, 0, ',', '.') }} VNĐ.
                            </div>
                        @endif
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
                                <form action="{{ route('admin.tour-bookings.reject-bank-transfer', $tourBooking->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                    <input type="hidden" name="transaction_id" value="{{ $payment->transaction_id }}">
                                    <button type="submit" class="btn btn-danger btn-sm btn-block" 
                                            onclick="return confirm('Bạn có chắc chắn muốn từ chối giao dịch chuyển khoản này?')">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Xác nhận thanh toán bằng thẻ tín dụng -->
            @if($tourBooking->payments->where('method', 'credit_card')->where('status', 'pending')->count() > 0)
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0"><i class="fas fa-credit-card"></i> Xác nhận thanh toán bằng thẻ tín dụng</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <strong>Thông tin:</strong> Có {{ $tourBooking->payments->where('method', 'credit_card')->where('status', 'pending')->count() }} giao dịch thanh toán bằng thẻ đang chờ xác nhận.
                        <br><small class="text-muted">Lưu ý: Khi admin thu tiền bổ sung, các giao dịch này sẽ được tự động xác nhận.</small>
                    </div>
                    
                    @foreach($tourBooking->payments->where('method', 'credit_card')->where('status', 'pending') as $payment)
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
                                <form action="{{ route('admin.tour-bookings.confirm-credit-card', $tourBooking->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                    <button type="submit" class="btn btn-success btn-sm btn-block" onclick="return confirm('Xác nhận giao dịch thanh toán bằng thẻ này?')">
                                        <i class="fas fa-check"></i> Xác nhận
                                    </button>
                                </form>
                            </div>
                            <div class="col-6">
                                <form action="{{ route('admin.tour-bookings.reject-credit-card', $tourBooking->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="payment_id" value="{{ $payment->id }}">
                                    <input type="hidden" name="transaction_id" value="{{ $payment->transaction_id }}">
                                    <button type="submit" class="btn btn-danger btn-sm btn-block" 
                                            onclick="return confirm('Bạn có chắc chắn muốn từ chối giao dịch thanh toán bằng thẻ này?')">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif



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
                        <br>
                        <button type="submit" class="btn btn-primary btn-sm btn-block" id="updateStatusBtn">
                            <i class="fas fa-save"></i> Cập nhật trạng thái
                        </button>
                    </form>
                </div>
            </div>

            <!-- Thu tiền bổ sung -->
            @php
                $hasPendingBankTransfer = $tourBooking->payments->where('method', 'bank_transfer')->where('status', 'pending')->count() > 0;
                $hasPendingCreditCard = $tourBooking->payments->where('method', 'credit_card')->where('status', 'pending')->count() > 0;
                $hasAnyPendingPayment = $hasPendingBankTransfer || $hasPendingCreditCard;
                $hasCompletedPayment = $tourBooking->payments->where('status', 'completed')->count() > 0;
                $canShowCollectPayment = !$hasAnyPendingPayment || $hasCompletedPayment;
                
                // Sử dụng biến đã tính toán ở đầu file
                $remainingAmountForCollection = $remainingAmount;
            @endphp
            
            @if($canShowCollectPayment)
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-money-bill-wave"></i> Thu tiền bổ sung</h6>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 pl-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    
                    @if($hasAnyPendingPayment && !$hasCompletedPayment)
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Lưu ý:</strong> Có giao dịch thanh toán đang chờ xác nhận. 
                            Bạn có thể xác nhận hoặc từ chối giao dịch đó trước khi thu tiền bổ sung.
                            @if($hasPendingBankTransfer)
                                <br>• Giao dịch chuyển khoản: {{ $tourBooking->payments->where('method', 'bank_transfer')->where('status', 'pending')->count() }} giao dịch
                            @endif
                            @if($hasPendingCreditCard)
                                <br>• Giao dịch thẻ tín dụng: {{ $tourBooking->payments->where('method', 'credit_card')->where('status', 'pending')->count() }} giao dịch
                            @endif
                        </div>
                    @endif
                    
                    <form action="{{ route('admin.tour-bookings.collect-payment', $tourBooking->id) }}" method="POST" onsubmit="return confirm('Xác nhận thu tiền bổ sung?')">
                        @csrf
                        <div class="form-group">
                            <label for="amount">Số tiền thu (VNĐ):</label>
                            <input type="number" name="amount" id="amount" class="form-control" 
                                   value="{{ $remainingAmountForCollection }}" 
                                   min="0" step="1000" required>
                            <small class="form-text text-muted">
                                Số tiền tối đa có thể thu: {{ number_format($remainingAmountForCollection, 0, ',', '.') }} VNĐ
                            </small>
                        </div>
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-money-bill-wave"></i> Thu tiền
                        </button>
                    </form>
                </div>
            </div>
            @endif
            
            <!-- Thông báo đã thanh toán đủ -->
            @if($totalCompletedPayments >= $finalAmount && $totalPendingPayments == 0)
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
                                                @elseif($payment->status === 'processing')
                                                    <div class="d-flex flex-column align-items-center gap-1">
                                                        <span class="bg-warning text-white px-2 py-1 rounded mb-1">Đang xác nhận</span>
                                                        @if($payment->method === 'bank_transfer')
                                                            <form action="{{ route('staff.tour-bookings.payments.update-status', [$tourBooking->id, $payment->id]) }}" method="POST" onsubmit="return confirm('Xác nhận thanh toán chuyển khoản này?')">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="status" value="completed">
                                                                <button type="submit" class="btn btn-sm btn-success">
                                                                    <i class="fas fa-check"></i> Xác nhận thanh toán
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
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
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-gradient-primary text-white py-3">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-file-invoice-dollar me-2 fs-4"></i>
                        <h6 class="mb-0 fw-bold">Hóa đơn VAT</h6>
                        @if($tourBooking->vat_invoice_number)
                            <span class="badge bg-light text-dark ms-auto fs-6">{{ $tourBooking->vat_invoice_number }}</span>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    @if($tourBooking->need_vat_invoice)
                        <!-- Thông tin công ty -->
                        <div class="bg-light rounded p-3 mb-4">
                            <h6 class="text-primary fw-bold mb-3">
                                <i class="fas fa-building me-2"></i>Thông tin công ty
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-building text-muted me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Tên công ty</small>
                                            <strong>{{ $tourBooking->company_name ?? 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-id-card text-muted me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Mã số thuế</small>
                                            <strong>{{ $tourBooking->company_tax_code ?? 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-envelope text-muted me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Email nhận HĐ</small>
                                            <strong>{{ $tourBooking->company_email ?? 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                        <div>
                                            <small class="text-muted d-block">Địa chỉ</small>
                                            <strong>{{ $tourBooking->company_address ?? 'N/A' }}</strong>
                                        </div>
                                    </div>
                                </div>
                                @if($tourBooking->company_phone)
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-phone text-muted me-2"></i>
                                            <div>
                                                <small class="text-muted d-block">Điện thoại</small>
                                                <strong>{{ $tourBooking->company_phone }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Trạng thái VAT -->
                        @if($tourBooking->vat_invoice_number)
                            <div class="bg-white border rounded p-3 mb-4">
                                <h6 class="text-success fw-bold mb-3">
                                    <i class="fas fa-info-circle me-2"></i>Trạng thái hóa đơn
                                </h6>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center">
                                            @if($tourBooking->vat_invoice_status === 'sent')
                                                <span class="badge bg-success rounded-pill me-2">
                                                    <i class="fas fa-check"></i>
                                                </span>
                                                <div>
                                                    <small class="text-muted d-block">Trạng thái</small>
                                                    <strong class="text-success">Đã hoàn thành</strong>
                                                </div>
                                            @elseif($tourBooking->vat_invoice_status === 'generated')
                                                <span class="badge bg-info rounded-pill me-2">
                                                    <i class="fas fa-file-pdf"></i>
                                                </span>
                                                <div>
                                                    <small class="text-muted d-block">Trạng thái</small>
                                                    <strong class="text-info">Đã tạo file</strong>
                                                </div>
                                            @else
                                                <span class="badge bg-warning rounded-pill me-2">
                                                    <i class="fas fa-clock"></i>
                                                </span>
                                                <div>
                                                    <small class="text-muted d-block">Trạng thái</small>
                                                    <strong class="text-warning">{{ $tourBooking->vat_invoice_status ?? 'pending' }}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    
                                    <!-- Thông tin file -->
                                    @if($tourBooking->vat_invoice_file_path)
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-success rounded-pill me-2">
                                                    <i class="fas fa-check-circle"></i>
                                                </span>
                                                <div>
                                                    <small class="text-muted d-block">File PDF</small>
                                                    <strong class="text-success">Đã tạo</strong>
                                                    @if($tourBooking->vat_invoice_generated_at)
                                                        <br><small class="text-muted">{{ $tourBooking->vat_invoice_generated_at->format('d/m/Y H:i') }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <!-- Thông tin email -->
                                    @if($tourBooking->vat_invoice_sent_at)
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center">
                                                <span class="badge bg-success rounded-pill me-2">
                                                    <i class="fas fa-envelope"></i>
                                                </span>
                                                <div>
                                                    <small class="text-muted d-block">Email</small>
                                                    <strong class="text-success">Đã gửi</strong>
                                                    <br><small class="text-muted">{{ $tourBooking->vat_invoice_sent_at->format('d/m/Y H:i') }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                        
                        <!-- Các nút chức năng VAT -->
                        <div class="bg-light rounded p-3">
                            <h6 class="text-primary fw-bold mb-3">
                                <i class="fas fa-cogs me-2"></i>Chức năng hóa đơn VAT
                            </h6>
                            <div class="d-grid gap-2">
                                @if(!$tourBooking->vat_invoice_file_path)
                                    <form action="{{ route('admin.tour-vat-invoices.generate', $tourBooking->id) }}" method="POST">
                                        @csrf
                                        <button class="btn btn-primary btn-lg w-100" 
                                                title="Tạo hóa đơn VAT PDF">
                                            <i class="fas fa-file-pdf me-2"></i>Tạo hóa đơn PDF
                                        </button>
                                        <small class="text-info d-block text-center mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Có thể tạo hóa đơn VAT ngay cả khi chưa thanh toán đủ tiền
                                        </small>
                                    </form>
                                @endif
                                
                                @if($tourBooking->vat_invoice_file_path)
                                    <div class="row g-2 mb-3">
                                        <!-- Nút Xem hóa đơn -->
                                        <div class="col-md-4">
                                            <a class="btn btn-info w-100" href="{{ route('admin.tour-vat-invoices.preview', $tourBooking->id) }}" target="_blank"
                                               title="Xem trước hóa đơn VAT">
                                                <i class="fas fa-eye me-2"></i>Xem
                                            </a>
                                        </div>
                                        
                                        <!-- Nút Tải xuống -->
                                        <div class="col-md-4">
                                            <a class="btn btn-secondary w-100" href="{{ route('admin.tour-vat-invoices.download', $tourBooking->id) }}"
                                               title="Tải xuống file PDF hóa đơn VAT">
                                                <i class="fas fa-download me-2"></i>Tải xuống
                                            </a>
                                        </div>
                                        
                                        <!-- Nút Sửa hóa đơn (Tạo lại) -->
                                        <div class="col-md-4">
                                            <form action="{{ route('admin.tour-vat-invoices.regenerate', $tourBooking->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-warning w-100" 
                                                        onclick="return confirm('Bạn có chắc muốn tạo lại file hóa đơn VAT? File cũ sẽ bị xóa.')"
                                                        title="Tạo lại hóa đơn VAT">
                                                    <i class="fas fa-edit me-2"></i>Sửa
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    
                                    <!-- Thông tin file đã tạo -->
                                    <div class="alert alert-success border-0 rounded-3 mb-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-check-circle text-success me-2 fs-4"></i>
                                            <div>
                                                <strong>Hóa đơn đã được tạo:</strong> 
                                                <span class="text-primary fw-bold">{{ $tourBooking->vat_invoice_number ?? 'N/A' }}</span>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i> 
                                                    {{ $tourBooking->vat_invoice_generated_at ? $tourBooking->vat_invoice_generated_at->format('d/m/Y H:i') : 'N/A' }}
                                                </small>
                                                @if($tourBooking->vat_invoice_status === 'sent')
                                                    <br>
                                                    <span class="text-info">
                                                        <i class="fas fa-envelope-open me-1"></i> 
                                                        Đã gửi email: {{ $tourBooking->vat_invoice_sent_at->format('d/m/Y H:i') }}
                                                    </span>
                                                @elseif($tourBooking->vat_invoice_status === 'generated')
                                                    <br>
                                                    <span class="text-warning">
                                                        <i class="fas fa-envelope me-1"></i> 
                                                        Chưa gửi email
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                
                                <!-- Nút Gửi email hóa đơn -->
                                <form action="{{ route('admin.tour-vat-invoices.send', $tourBooking->id) }}" method="POST">
                                    @csrf
                                    <button class="btn btn-success btn-lg w-100" 
                                            {{ empty($tourBooking->vat_invoice_file_path) ? 'disabled' : '' }}
                                            title="{{ empty($tourBooking->vat_invoice_file_path) ? 'Cần tạo hóa đơn PDF trước' : 'Gửi email hóa đơn VAT' }}">
                                        <i class="fas fa-envelope me-2"></i>Gửi email hóa đơn
                                    </button>
                                    @if(empty($tourBooking->vat_invoice_file_path))
                                        <small class="text-muted d-block text-center mt-2">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Cần tạo hóa đơn PDF trước
                                        </small>
                                    @else
                                        <small class="text-info d-block text-center mt-2">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Có thể gửi email hóa đơn VAT
                                        </small>
                                    @endif
                                </form>
                            </div>
                        </div>
                        
                        <!-- Thông tin VAT -->
                        <div class="alert alert-info border-0 rounded-3 mt-4 mb-0">
                            <div class="d-flex">
                                <i class="fas fa-info-circle text-info me-2 fs-4"></i>
                                <div>
                                    <strong>Thông tin VAT:</strong> Giá cuối đã bao gồm VAT 10%, không thu thêm phí. Hóa đơn chỉ để khách kê khai thuế.
                                    <br><small class="text-muted">Sử dụng logic tính toán mới nhất quán với regular booking</small>
                                    <br><small class="text-muted">Điều kiện: Có thể tạo và gửi hóa đơn VAT ngay cả khi chưa thanh toán đủ tiền</small>
                                    <br><small class="text-muted">Hệ thống hoạt động giống hệt như regular booking</small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Thông tin trạng thái thanh toán -->
                        <div class="alert alert-{{ $totalCompletedPayments >= $finalAmount && $totalPendingPayments == 0 ? 'success' : 'warning' }} border-0 rounded-3 mt-3 mb-0">
                            <div class="d-flex">
                                <i class="fas fa-{{ $totalCompletedPayments >= $finalAmount && $totalPendingPayments == 0 ? 'check-circle' : 'exclamation-triangle' }} text-{{ $totalCompletedPayments >= $finalAmount && $totalPendingPayments == 0 ? 'success' : 'warning' }} me-2 fs-4"></i>
                                <div>
                                    <strong>Trạng thái thanh toán:</strong>
                                    @if($totalCompletedPayments >= $finalAmount && $totalPendingPayments == 0)
                                        <span class="text-success">✅ Đã thanh toán đủ tiền</span>
                                        <br><small class="text-success">Có thể xuất hóa đơn VAT</small>
                                    @else
                                        <span class="text-warning">⚠️ Chưa thanh toán đủ tiền</span>
                                        @if($totalPendingPayments > 0)
                                            <br><small class="text-warning">Có {{ number_format($totalPendingPayments, 0, ',', '.') }} VNĐ đang chờ xác nhận</small>
                                        @endif
                                        <br><small class="text-info">Còn thiếu: {{ number_format($remainingAmount, 0, ',', '.') }} VNĐ</small>
                                        <br><small class="text-info">Tuy nhiên, vẫn có thể tạo và gửi hóa đơn VAT</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <!-- Cảnh báo hóa đơn lớn -->
                        @if($paymentInfo['isHighValue'])
                            <div class="alert alert-warning border-0 rounded-3 mt-3 mb-0">
                                <div class="d-flex">
                                    <i class="fas fa-exclamation-triangle text-warning me-2 fs-4"></i>
                                    <div>
                                        <strong>Lưu ý quan trọng:</strong> Hóa đơn từ {{ number_format(5000000) }}₫ ({{ number_format($paymentInfo['totalDue']) }} VNĐ) theo quy định pháp luật nên thanh toán bằng thẻ/tài khoản công ty hoặc chuyển khoản công ty.
                                        <br><small class="text-muted">Tuy nhiên, vẫn có thể tạo hóa đơn VAT nếu khách đã thanh toán đủ tiền.</small>
                                        <br><small class="text-muted">Giá hiển thị đã bao gồm VAT 10%, không thu thêm phí.</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <!-- Form từ chối yêu cầu -->
                        @if(!$tourBooking->vat_invoice_file_path)
                            <hr class="my-4">
                            <div class="bg-light rounded p-3">
                                <h6 class="text-danger fw-bold mb-3">
                                    <i class="fas fa-times-circle me-2"></i>Từ chối yêu cầu
                                </h6>
                                <form action="{{ route('admin.tour-vat-invoices.reject', $tourBooking->id) }}" method="POST">
                                    @csrf
                                    <div class="form-group mb-3">
                                        <label class="form-label fw-bold">Lý do từ chối:</label>
                                        <textarea name="rejection_reason" class="form-control" rows="3" 
                                                  placeholder="Nhập lý do từ chối..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-times me-2"></i>Từ chối yêu cầu
                                    </button>
                                </form>
                            </div>
                        @endif
                        
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-info-circle text-muted fs-1 mb-3"></i>
                            <p class="text-muted mb-0">Không yêu cầu hóa đơn VAT</p>
                        </div>
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
