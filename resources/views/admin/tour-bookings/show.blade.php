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
                                            <div class="col-6 text-right text-dark">{{ number_format($tourBooking->total_rooms_amount, 0, ',', '.') }} VNĐ</div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-6 text-dark">Tiền dịch vụ:</div>
                                            <div class="col-6 text-right text-dark">{{ number_format($tourBooking->total_services_amount, 0, ',', '.') }} VNĐ</div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row mb-1">
                                            <div class="col-6"><strong class="text-dark">Tổng cộng:</strong></div>
                                            <div class="col-6 text-right font-weight-bold text-warning">{{ number_format($tourBooking->total_amount_before_discount, 0, ',', '.') }} VNĐ</div>
                                        </div>
                                        @if($tourBooking->has_discount)
                                            <div class="row mb-1">
                                                <div class="col-6 text-dark">Giảm giá ({{ $tourBooking->discount_percentage }}%):</div>
                                                <div class="col-6 text-right text-success">-{{ number_format($tourBooking->total_discount, 0, ',', '.') }} VNĐ</div>
                                            </div>
                                            <hr class="my-2">
                                            <div class="row">
                                                <div class="col-6"><strong class="text-dark">Giá cuối:</strong></div>
                                                <div class="col-6 text-right font-weight-bold text-primary">{{ number_format($tourBooking->final_amount, 0, ',', '.') }} VNĐ</div>
                                            </div>
                                        @else
                                            <div class="row">
                                                <div class="col-6"><strong class="text-dark">Giá cuối:</strong></div>
                                                <div class="col-6 text-right font-weight-bold text-primary">{{ number_format($tourBooking->final_amount, 0, ',', '.') }} VNĐ</div>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0"><i class="fas fa-credit-card"></i> Tình trạng thanh toán</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row mb-1">
                                            <div class="col-6 text-dark">Tổng tiền cần TT:</div>
                                            <div class="col-6 text-right font-weight-bold text-dark">{{ number_format($tourBooking->final_amount, 0, ',', '.') }} VNĐ</div>
                                        </div>
                                        <div class="row mb-1">
                                            <div class="col-6 text-dark">Đã thanh toán:</div>
                                            <div class="col-6 text-right text-success">{{ number_format($tourBooking->total_paid, 0, ',', '.') }} VNĐ</div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-6 text-dark">Còn lại:</div>
                                            <div class="col-6 text-right font-weight-bold {{ $tourBooking->outstanding_amount > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($tourBooking->outstanding_amount, 0, ',', '.') }} VNĐ
                                            </div>
                                        </div>
                                        <div class="progress mb-2" style="height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $tourBooking->isFullyPaid() ? 100 : ($tourBooking->total_paid / $tourBooking->final_amount * 100) }}%">
                                            </div>
                                        </div>
                                        <small class="text-muted">
                                            Tỷ lệ hoàn thành: {{ $tourBooking->isFullyPaid() ? 100 : round($tourBooking->total_paid / $tourBooking->final_amount * 100, 1) }}%
                                        </small>
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
                <div class="card-body text-center">
                    <div class="mb-3">
                        <i class="fas fa-user-circle fa-3x text-primary"></i>
                    </div>
                    <h6 class="mb-1">{{ $tourBooking->user->name ?? 'Khách' }}</h6>
                    <p class="text-muted mb-2">{{ $tourBooking->user->email ?? 'N/A' }}</p>
                    <div class="btn-group-vertical w-100">
                        <a href="mailto:{{ $tourBooking->user->email ?? '#' }}" class="btn btn-sm btn-outline-primary mb-1">
                            <i class="fas fa-envelope"></i> Gửi email
                        </a>
                        <a href="tel:{{ $tourBooking->user->phone ?? '#' }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-phone"></i> Gọi điện
                        </a>
                    </div>
                </div>
            </div>

            <!-- Trạng thái thanh toán -->
            <div class="card mb-3">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fas fa-credit-card"></i> Trạng thái thanh toán</h6>
                </div>
                <div class="card-body text-center">
                    <div class="mb-2">
                        <span class="bg-{{ $tourBooking->isFullyPaid() ? 'success' : ($tourBooking->total_paid > 0 ? 'warning' : 'secondary') }} text-white px-3 py-2 rounded">
                            <i class="{{ $tourBooking->payment_status_icon }}"></i>
                            {{ $tourBooking->payment_status_text }}
                        </span>
                    </div>
                    <div class="text-muted">
                        <small>Có {{ $tourBooking->payments->count() }} giao dịch thanh toán</small>
                    </div>
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
                                <span class="badge {{ $tourBooking->status_badge_class }} text-white px-3 py-2">
                                    {{ $tourBooking->status_text }}
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
                        
                        <!-- Debug info -->
                        @if(config('app.debug'))
                            <div class="alert alert-info small">
                                <strong>Debug:</strong><br>
                                Trạng thái hiện tại: {{ $tourBooking->status }}<br>
                                Trạng thái hợp lệ tiếp theo: {{ implode(', ', $validNextStatuses) }}<br>
                                Số lượng trạng thái: {{ count($validNextStatuses) }}
                            </div>
                        @endif
                        
                        <button type="submit" class="btn btn-primary btn-sm btn-block" id="updateStatusBtn">
                            <i class="fas fa-save"></i> Cập nhật trạng thái
                        </button>
                    </form>
                </div>
            </div>

            <!-- Thu tiền bổ sung -->
            @if($tourBooking->outstanding_amount > 0)
                <div class="card mb-3">
                    <div class="card-header bg-danger text-white">
                        <h6 class="mb-0"><i class="fas fa-money-bill-wave"></i> Thu tiền bổ sung</h6>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.tour-bookings.collect-payment', $tourBooking->id) }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label class="small">Số tiền cần thu:</label>
                                <input type="number" name="amount" class="form-control form-control-sm" 
                                       value="{{ $tourBooking->outstanding_amount }}" 
                                       max="{{ $tourBooking->outstanding_amount }}" required>
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm btn-block">Thu tiền</button>
                        </form>
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
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fas fa-file-invoice"></i> Hóa đơn VAT</h6>
                </div>
                <div class="card-body">
                    @if($tourBooking->need_vat_invoice)
                        <div class="row mb-2">
                            <div class="col-6"><strong>Tên công ty:</strong></div>
                            <div class="col-6">{{ $tourBooking->company_name ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Mã số thuế:</strong></div>
                            <div class="col-6">{{ $tourBooking->company_tax_code ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Email:</div>
                            <div class="col-6">{{ $tourBooking->company_email ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Địa chỉ:</strong></div>
                            <div class="col-6">{{ $tourBooking->company_address ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><strong>Điện thoại:</strong></div>
                            <div class="col-6">{{ $tourBooking->company_phone ?? 'N/A' }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-6"><strong>Trạng thái:</strong></div>
                            <div class="col-6">
                                @if($tourBooking->vat_invoice_number)
                                    <span class="bg-success text-white px-2 py-1 rounded">Đã xuất</span>
                                @else
                                    <span class="bg-warning text-white px-2 py-1 rounded">Chờ xử lý</span>
                                @endif
                            </div>
                        </div>
                        
                        @if($tourBooking->vat_invoice_number)
                            <div class="text-center mb-3">
                                <small class="text-muted">Số HĐ: {{ $tourBooking->vat_invoice_number }}</small><br>
                                <small class="text-muted">{{ $tourBooking->vat_invoice_created_at ? $tourBooking->vat_invoice_created_at->format('d/m/Y H:i') : 'N/A' }}</small>
                            </div>
                            
                                                            <div class="d-grid gap-2">
                                    <a href="{{ route('admin.tour-vat-invoices.preview', $tourBooking->id) }}" 
                                        class="btn btn-info btn-sm mb-2" target="_blank">
                                        <i class="fas fa-eye"></i> Xem trước
                                    </a>
                                    
                                    <a href="{{ route('admin.tour-vat-invoices.download', $tourBooking->id) }}" 
                                        class="btn btn-success btn-sm mb-2">
                                        <i class="fas fa-download"></i> Tải hóa đơn
                                    </a>
                                    
                                    <form action="{{ route('admin.tour-vat-invoices.send', $tourBooking->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-primary btn-sm btn-block" 
                                                onclick="return confirm('Bạn có chắc muốn gửi email hóa đơn VAT cho khách hàng?')">
                                            <i class="fas fa-envelope"></i> Gửi email
                                        </button>
                                    </form>
                                    
                                    <form action="{{ route('admin.tour-vat-invoices.regenerate', $tourBooking->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-sm btn-block" 
                                                onclick="return confirm('Bạn có chắc muốn tạo lại file hóa đơn VAT? File cũ sẽ bị xóa.')">
                                            <i class="fas fa-sync-alt"></i> Tạo lại file
                                        </button>
                                    </form>
                                </div>
                        @else
                            <!-- Form tạo hóa đơn VAT -->
                            <form action="{{ route('admin.tour-vat-invoices.generate', $tourBooking->id) }}" method="POST" class="mb-2">
                                @csrf
                                <div class="form-group">
                                    <label class="small">Mã hóa đơn VAT:</label>
                                    <input type="text" name="vat_invoice_number" class="form-control form-control-sm" 
                                           placeholder="Nhập mã hóa đơn..." required>
                                </div>
                                <div class="form-group">
                                    <label class="small">Ghi chú:</label>
                                    <textarea name="notes" class="form-control form-control-sm" rows="2" 
                                              placeholder="Ghi chú về hóa đơn..."></textarea>
                                </div>
                                <br>
                                <button type="submit" class="btn btn-success btn-sm btn-block">
                                    <i class="fas fa-file-invoice"></i> Tạo hóa đơn
                                </button>
                            </form>
                            
                            <!-- Form từ chối yêu cầu -->
                             <br>
                            <form action="{{ route('admin.tour-vat-invoices.reject', $tourBooking->id) }}" method="POST">
                                @csrf
                                <div class="form-group">
                                    <label class="small">Lý do từ chối:</label>
                                    <textarea name="rejection_reason" class="form-control form-control-sm" rows="2" 
                                              placeholder="Nhập lý do từ chối..." required></textarea>
                                </div>
                                <br>
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

    <!-- Debug Information (chỉ hiển thị cho admin) -->
    {{-- @if(auth()->user()->hasRole('admin'))
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h6 class="mb-0"><i class="fas fa-bug"></i> Debug Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <small>
                                    <strong>Database values:</strong><br>
                                    total_price: {{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ<br>
                                    final_price: {{ number_format($tourBooking->final_price ?? 0, 0, ',', '.') }} VNĐ<br>
                                    promotion_discount: {{ number_format($tourBooking->promotion_discount ?? 0, 0, ',', '.') }} VNĐ
                                </small>
                            </div>
                            <div class="col-md-6">
                                <small>
                                    <strong>Calculated values:</strong><br>
                                    total_rooms_amount: {{ number_format($tourBooking->total_rooms_amount, 0, ',', '.') }} VNĐ<br>
                                    total_services_amount: {{ number_format($tourBooking->total_services_amount, 0, ',', '.') }} VNĐ<br>
                                    total_amount_before_discount: {{ number_format($tourBooking->total_amount_before_discount, 0, ',', '.') }} VNĐ<br>
                                    final_amount: {{ number_format($tourBooking->final_amount, 0, ',', '.') }} VNĐ
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif --}}
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
            method: 'POST',
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
