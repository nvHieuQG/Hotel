@extends('client.layouts.master')

@section('title', 'Chi tiết Tour Booking')

@section('content')
<section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate pb-5 text-center">
                <h1 class="mb-3 bread">Chi tiết Tour Booking</h1>
                <p class="breadcrumbs">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ <i class="ion-ios-arrow-forward"></i></a></span>
                    <span class="mr-2"><a href="{{ route('tour-booking.index') }}">Tour Bookings <i class="ion-ios-arrow-forward"></i></a></span>
                    <span>Chi tiết</span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Thông tin chính -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4>{{ $tourBooking->tour_name }}</h4>
                        <span class="badge badge-{{ $tourBooking->status === 'pending' ? 'warning' : ($tourBooking->status === 'confirmed' ? 'success' : ($tourBooking->status === 'cancelled' ? 'danger' : 'info')) }} badge-lg">
                            {{ $tourBooking->status_text }}
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mã đặt phòng:</strong> {{ $tourBooking->booking_id }}</p>
                                <p><strong>Tổng số khách:</strong> {{ $tourBooking->total_guests }} người</p>
                                <p><strong>Tổng số phòng:</strong> {{ $tourBooking->total_rooms }} phòng</p>
                                <p><strong>Số đêm:</strong> {{ $tourBooking->total_nights }} đêm</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Check-in:</strong> {{ $tourBooking->check_in_date->format('d/m/Y') }}</p>
                                <p><strong>Check-out:</strong> {{ $tourBooking->check_out_date->format('d/m/Y') }}</p>
                                <p><strong>Tổng tiền:</strong> <span class="text-primary font-weight-bold">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</span></p>
                                @if($tourBooking->promotion_discount > 0)
                                    <p><strong>Giảm giá:</strong> <span class="text-success">-{{ number_format($tourBooking->promotion_discount, 0, ',', '.') }} VNĐ</span></p>
                                    <p><strong>Giá cuối:</strong> <span class="text-danger font-weight-bold">{{ number_format($tourBooking->final_price, 0, ',', '.') }} VNĐ</span></p>
                                @endif
                                <p><strong>Ngày đặt:</strong> {{ $tourBooking->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                        
                        @if($tourBooking->special_requests)
                            <div class="mt-3 pt-3 border-top">
                                <strong>Yêu cầu đặc biệt:</strong>
                                <p class="text-muted mb-0">{{ $tourBooking->special_requests }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Chi tiết phòng -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Chi tiết phòng đã đặt</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Loại phòng</th>
                                        <th>Số lượng</th>
                                        <th>Số khách/phòng</th>
                                        <th>Phòng đã gán</th>
                                        <th>Giá/phòng</th>
                                        <th>Tổng tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tourBooking->tourBookingRooms as $tourBookingRoom)
                                        <tr>
                                            <td>
                                                <strong>{{ $tourBookingRoom->roomType->name }}</strong>
                                                <br><small class="text-muted">{{ $tourBookingRoom->roomType->description }}</small>
                                            </td>
                                            <td><span class="badge badge-primary">{{ $tourBookingRoom->quantity }} phòng</span></td>
                                            <td><span class="badge badge-info">{{ $tourBookingRoom->guests_per_room }} người</span></td>
                                            <td>
                                                @if($tourBookingRoom->assigned_rooms->count() > 0)
                                                    @foreach($tourBookingRoom->assigned_rooms as $room)
                                                        <span class="badge badge-success mr-1 mb-1">{{ $room->room_number }}</span>
                                                    @endforeach
                                                @else
                                                    <span class="text-muted">Chưa gán phòng</span>
                                                @endif
                                            </td>
                                            <td>{{ number_format($tourBookingRoom->price_per_room, 0, ',', '.') }} VNĐ</td>
                                            <td>{{ number_format($tourBookingRoom->total_price, 0, ',', '.') }} VNĐ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-info">
                                    <tr>
                                        <td colspan="5" class="text-right"><strong>Tổng tiền phòng:</strong></td>
                                        <td><strong>{{ number_format($tourBooking->total_rooms_amount ?? $tourBooking->total_price, 0, ',', '.') }} VNĐ</strong></td>
                                    </tr>
                                    @if($tourBooking->total_services_amount > 0)
                                        <tr>
                                            <td colspan="5" class="text-right"><strong>Tổng tiền dịch vụ:</strong></td>
                                            <td><strong>{{ number_format($tourBooking->total_services_amount, 0, ',', '.') }} VNĐ</strong></td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <td colspan="5" class="text-right"><strong>Tổng cộng:</strong></td>
                                        <td><strong>{{ number_format($tourBooking->total_amount_before_discount ?? $tourBooking->total_price, 0, ',', '.') }} VNĐ</strong></td>
                                    </tr>
                                    @if($tourBooking->promotion_discount > 0)
                                        <tr class="table-success">
                                            <td colspan="5" class="text-right"><strong>Giảm giá:</strong></td>
                                            <td><strong class="text-success">-{{ number_format($tourBooking->promotion_discount, 0, ',', '.') }} VNĐ</strong></td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td colspan="5" class="text-right"><strong>Giá cuối:</strong></td>
                                            <td><strong class="text-danger">{{ number_format($tourBooking->final_price ?? ($tourBooking->total_price - $tourBooking->promotion_discount), 0, ',', '.') }} VNĐ</strong></td>
                                        </tr>
                                    @endif
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Chi tiết dịch vụ (nếu có) -->
                @if($tourBooking->tourBookingServices && $tourBooking->tourBookingServices->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Chi tiết dịch vụ đã đặt</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Tên dịch vụ</th>
                                            <th>Mô tả</th>
                                            <th>Số lượng</th>
                                            <th>Đơn giá</th>
                                            <th>Tổng tiền</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tourBooking->tourBookingServices as $service)
                                            <tr>
                                                <td>
                                                    <strong>{{ $service->service_name }}</strong>
                                                </td>
                                                <td>
                                                    <small class="text-muted">{{ $service->description ?? 'Không có mô tả' }}</small>
                                                </td>
                                                <td><span class="badge badge-info">{{ $service->quantity }}</span></td>
                                                <td>{{ number_format($service->unit_price, 0, ',', '.') }} VNĐ</td>
                                                <td>{{ number_format($service->total_price, 0, ',', '.') }} VNĐ</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Tóm tắt thanh toán -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Tóm tắt thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card border-primary">
                                    <div class="card-header bg-primary text-white">
                                        <h6 class="mb-0">Chi tiết giá</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-6">
                                                <strong>Tiền phòng:</strong><br>
                                                <strong>Tiền dịch vụ:</strong><br>
                                                <strong>Tổng cộng:</strong><br>
                                                <strong>Giảm giá:</strong><br>
                                                <strong>Giá cuối:</strong>
                                            </div>
                                            <div class="col-6 text-right">
                                                <span class="text-primary">{{ number_format($tourBooking->total_rooms_amount ?? $tourBooking->total_price, 0, ',', '.') }} VNĐ</span><br>
                                                <span class="text-primary">{{ number_format($tourBooking->total_services_amount ?? 0, 0, ',', '.') }} VNĐ</span><br>
                                                <span class="text-warning">{{ number_format($tourBooking->total_amount_before_discount ?? $tourBooking->total_price, 0, ',', '.') }} VNĐ</span><br>
                                                <span class="text-success">-{{ number_format($tourBooking->promotion_discount ?? 0, 0, ',', '.') }} VNĐ</span><br>
                                                <span class="text-danger">{{ number_format($tourBooking->final_price ?? $tourBooking->total_price, 0, ',', '.') }} VNĐ</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card border-info">
                                    <div class="card-header bg-info text-white">
                                        <h6 class="mb-0">Trạng thái thanh toán</h6>
                                    </div>
                                    <div class="card-body text-center">
                                        @php
                                            $totalAmount = $tourBooking->final_price ?? ($tourBooking->total_price - ($tourBooking->promotion_discount ?? 0));
                                            $totalPaid = $tourBooking->payments->whereIn('status', ['completed', 'paid'])->sum('amount');
                                            $remaining = $totalAmount - $totalPaid;
                                            $percentage = $totalAmount > 0 ? ($totalPaid / $totalAmount) * 100 : 0;
                                        @endphp
                                        
                                        @if($remaining <= 0)
                                            <div class="alert alert-success mb-3">
                                                <i class="fas fa-check-circle fa-2x"></i>
                                                <h5 class="mt-2">Đã thanh toán đủ</h5>
                                            </div>
                                        @else
                                            <div class="alert alert-warning mb-3">
                                                <i class="fas fa-exclamation-triangle fa-2x"></i>
                                                <h5 class="mt-2">Còn {{ number_format($remaining, 0, ',', '.') }} VNĐ</h5>
                                            </div>
                                        @endif
                                        
                                        <!-- Progress bar -->
                                        <div class="mt-3">
                                            <label>Tỷ lệ hoàn thành thanh toán</label>
                                            <div class="progress" style="height: 25px;">
                                                <div class="progress-bar bg-{{ $percentage >= 100 ? 'success' : ($percentage >= 50 ? 'warning' : 'danger') }}"
                                                     role="progressbar"
                                                     style="width: {{ min(100, $percentage) }}%"
                                                     aria-valuenow="{{ $percentage }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                    {{ number_format($percentage, 1) }}%
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="mt-3">
                                            <strong>Đã thanh toán:</strong> {{ number_format($totalPaid, 0, ',', '.') }} VNĐ<br>
                                            <strong>Còn lại:</strong> {{ number_format($remaining, 0, ',', '.') }} VNĐ
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Lịch sử thanh toán -->
                @if($tourBooking->payments->count() > 0)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Lịch sử thanh toán</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Ngày thanh toán</th>
                                            <th>Phương thức</th>
                                            <th>Số tiền</th>
                                            <th>Trạng thái</th>
                                            <th>Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tourBooking->payments->unique('id') as $payment)
                                            <tr>
                                                <td>{{ $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                                <td>
                                                    @if($payment->method === 'credit_card')
                                                        <i class="fas fa-credit-card text-primary"></i> Thẻ tín dụng
                                                    @elseif($payment->method === 'bank_transfer')
                                                        <i class="fas fa-university text-success"></i> Chuyển khoản
                                                    @elseif($payment->method === 'cod')
                                                        <i class="fas fa-money-bill text-warning"></i> Tiền mặt
                                                    @else
                                                        {{ ucfirst($payment->method) }}
                                                    @endif
                                                </td>
                                                <td>{{ number_format($payment->amount, 0, ',', '.') }} VNĐ</td>
                                                <td>
                                                    <span class="badge badge-{{ $payment->status === 'completed' || $payment->status === 'paid' ? 'success' : ($payment->status === 'pending' ? 'warning' : 'danger') }}">
                                                        @if($payment->status === 'completed' || $payment->status === 'paid')
                                                            Hoàn thành
                                                        @elseif($payment->status === 'pending')
                                                            Đang xử lý
                                                        @elseif($payment->status === 'failed')
                                                            Thất bại
                                                        @else
                                                            {{ ucfirst($payment->status) }}
                                                        @endif
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($payment->gateway_response)
                                                        @php
                                                            $response = is_string($payment->gateway_response) ? json_decode($payment->gateway_response, true) : $payment->gateway_response;
                                                        @endphp
                                                        @if(isset($response['card_info']['last4']))
                                                            Thẻ ****{{ $response['card_info']['last4'] }}
                                                        @elseif(isset($response['customer_note']))
                                                            {{ $response['customer_note'] }}
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Thông tin VAT Invoice -->
                @if($tourBooking->need_vat_invoice || $tourBooking->vat_invoice_number)
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5>Hóa đơn VAT</h5>
                        </div>
                        <div class="card-body">
                            @if($tourBooking->vat_invoice_number)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Hóa đơn VAT đã được xuất!</strong><br>
                                    <strong>Mã hóa đơn:</strong> {{ $tourBooking->vat_invoice_number }}<br>
                                    <strong>Ngày xuất:</strong> {{ $tourBooking->vat_invoice_created_at ? $tourBooking->vat_invoice_created_at->format('d/m/Y H:i') : 'N/A' }}
                                </div>
                                <div class="text-center">
                                    <a href="{{ route('tour-booking.vat-invoice.download', $tourBooking->id) }}" 
                                       class="btn btn-success">
                                        <i class="fas fa-download"></i> Tải xuống hóa đơn VAT
                                    </a>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Bạn đã yêu cầu xuất hóa đơn VAT.</strong><br>
                                    <strong>Trạng thái:</strong> Đang xử lý
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <strong>Tên công ty:</strong> {{ $tourBooking->company_name }}<br>
                                        <strong>Mã số thuế:</strong> {{ $tourBooking->company_tax_code }}<br>
                                        <strong>Email:</strong> {{ $tourBooking->company_email }}
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Địa chỉ:</strong> {{ $tourBooking->company_address }}<br>
                                        <strong>Điện thoại:</strong> {{ $tourBooking->company_phone }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Hành động -->
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <a href="{{ route('tour-booking.index') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                                </a>
                            </div>
                            <div class="col-md-4">
                                @if($tourBooking->status === 'confirmed' || $tourBooking->status === 'pending')
                                    <a href="{{ route('tour-bookings.room-change.create', $tourBooking->id) }}" 
                                       class="btn btn-warning btn-block">
                                        <i class="fas fa-exchange-alt"></i> Đổi phòng
                                    </a>
                                @elseif(!$tourBooking->need_vat_invoice && !$tourBooking->vat_invoice_number)
                                    <a href="{{ route('tour-booking.vat-invoice', $tourBooking->id) }}" 
                                       class="btn btn-info btn-block">
                                        <i class="fas fa-file-invoice"></i> Yêu cầu VAT
                                    </a>
                                @endif
                            </div>
                            <div class="col-md-4">
                                @if($tourBooking->status === 'pending')
                                    <a href="{{ route('tour-booking.payment', $tourBooking->booking_id) }}" class="btn btn-success btn-block">
                                        <i class="fas fa-credit-card"></i> Thanh toán ngay
                                    </a>
                                @elseif($tourBooking->status === 'confirmed')
                                    <button class="btn btn-info btn-block" disabled>
                                        <i class="fas fa-check"></i> Đã xác nhận
                                    </button>
                                @elseif($tourBooking->status === 'completed')
                                    <button class="btn btn-success btn-block" disabled>
                                        <i class="fas fa-star"></i> Hoàn thành
                                    </button>
                                @else
                                    <button class="btn btn-danger btn-block" disabled>
                                        <i class="fas fa-times"></i> Đã hủy
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.badge-lg {
    font-size: 1em;
    padding: 0.5em 1em;
}

.table th {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection 