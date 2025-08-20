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
                                    <p class="mb-2">Người lớn: {{ (int)($booking->adults_count ?? 0) }} - Trẻ em: {{ (int)($booking->children_count ?? 0) }} - Em bé: {{ (int)($booking->infants_count ?? 0) }}</p>
                                    @php 
                                        /** @var \Carbon\Carbon $ci */
                                        /** @var \Carbon\Carbon $co */
                                        $ci = $booking->check_in_date instanceof \Carbon\Carbon ? $booking->check_in_date : \Carbon\Carbon::parse($booking->check_in_date);
                                        $co = $booking->check_out_date instanceof \Carbon\Carbon ? $booking->check_out_date : \Carbon\Carbon::parse($booking->check_out_date);
                                        // Tính số đêm theo ngày (bỏ phần giờ phút) để tránh số thập phân
                                        $ciDate = $ci ? $ci->copy()->startOfDay() : null;
                                        $coDate = $co ? $co->copy()->startOfDay() : null;
                                        $nights = ($ciDate && $coDate) ? (int) $ciDate->diffInDays($coDate) : 0;
                                        // Lấy giá/đêm ưu tiên roomType->price, fallback room->price
                                        $nightly = (int)($booking->room->roomType->price ?? $booking->room->price ?? 0);
                                        $roomCost = max(0, $nights) * $nightly;
                                        $surcharge = (float)($booking->surcharge ?? 0);
                                        // Phụ thu đổi phòng: cộng chênh lệch đã duyệt/hoàn tất
                                        $roomChangeSurcharge = (float) $booking->roomChanges()
                                            ->whereIn('status', ['approved', 'completed'])
                                            ->sum('price_difference');
                                        // Phụ phí người lớn/trẻ em = surcharge tổng trừ phụ thu đổi phòng (không âm)
                                        $guestSurcharge = max(0, $surcharge - $roomChangeSurcharge);
                                    @endphp
                                    <p><strong>Số đêm:</strong> {{ $nights }}</p>
                                    <hr>
                                    <p><strong>Tiền phòng ({{ number_format($nightly) }} VNĐ/đêm × {{ (int)$nights }} đêm):</strong> {{ number_format($roomCost) }} VNĐ</p>
                                    <p><strong>Phụ phí (người lớn/trẻ em):</strong> {{ number_format($guestSurcharge) }} VNĐ</p>
                                    <p><strong>Phụ thu đổi phòng:</strong> {{ number_format($roomChangeSurcharge) }} VNĐ</p>
                                    @php 
                                        // Tính tổng dịch vụ: cộng cả dịch vụ khách chọn (extra_services_total) và dịch vụ admin thêm (bookingServices)
                                        $svcFromAdmin = (float)($booking->total_services_price ?? 0);
                                        $svcFromClient = (float)($booking->extra_services_total ?? 0);
                                        $svcTotal = $svcFromAdmin + $svcFromClient; 
                                    @endphp
                                    <p class="mb-1"><strong>Tiền dịch vụ (khách chọn)</strong></p>
                                    @php 
                                        $extraSvcs = $booking->extra_services ?? [];
                                        if (is_string($extraSvcs)) {
                                            $decoded = json_decode($extraSvcs, true);
                                            $extraSvcs = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
                                        }
                                        $svcNames = [];
                                        try {
                                            $ids = collect($extraSvcs)->pluck('id')->filter()->unique()->values()->all();
                                            if (!empty($ids)) {
                                                $svcNames = \App\Models\ExtraService::whereIn('id', $ids)->pluck('name', 'id')->toArray();
                                            }
                                        } catch (\Throwable $e) { $svcNames = []; }
                                        $chargeTypeLabel = function($t) {
                                            return match($t) {
                                                'per_person' => 'Theo người',
                                                'per_day' => 'Theo ngày',
                                                'per_service' => 'Theo dịch vụ',
                                                'per_hour' => 'Theo giờ',
                                                'per_night' => 'Theo đêm',
                                                default => ucfirst((string)$t)
                                            };
                                        };
                                    @endphp
                                    @if(!empty($extraSvcs) && is_array($extraSvcs))
                                        <div class="mb-2">
                                            <ul class="small mb-0 ps-3">
                                                @foreach($extraSvcs as $es)
                                                    @php 
                                                        $sid = $es['id'] ?? null;
                                                        $type = $es['charge_type'] ?? '';
                                                        $adultsSel = (int)($es['adults_used'] ?? 0);
                                                        $childrenSel = (int)($es['children_used'] ?? 0);
                                                        $days = (int)($es['days'] ?? 0);
                                                        $qty = (int)($es['quantity'] ?? 1);
                                                        $name = $sid && isset($svcNames[$sid]) ? $svcNames[$sid] : ('Dịch vụ #'.($sid ?? ''));
                                                        // Tính thành tiền mỗi dịch vụ (ưu tiên dùng subtotal từ JSON)
                                                        $lineTotal = null;
                                                        if (isset($es['subtotal'])) {
                                                            $lineTotal = (float) $es['subtotal'];
                                                        } else {
                                                            $pa = (float)($es['price_adult'] ?? 0);
                                                            $pc = (float)($es['price_child'] ?? 0);
                                                            if ($type === 'per_person') {
                                                                $multDays = $days > 0 ? $days : max(1, (int)($nights ?? 1));
                                                                $lineTotal = ($adultsSel * $pa + $childrenSel * $pc) * $multDays;
                                                            } elseif ($type === 'per_day' || $type === 'per_hour') {
                                                                $multDays = $days > 0 ? $days : max(1, (int)($nights ?? 1));
                                                                $unit = $pa > 0 ? $pa : $pc; // fallback nếu chỉ có 1 giá
                                                                $lineTotal = $unit * max(1, $qty) * $multDays;
                                                            } elseif ($type === 'per_service') {
                                                                $unit = $pa > 0 ? $pa : $pc;
                                                                $lineTotal = $unit * max(1, $qty);
                                                            } elseif ($type === 'per_night') {
                                                                $unit = $pa > 0 ? $pa : $pc;
                                                                $lineTotal = $unit * max(1, (int)($nights ?? 1));
                                                            }
                                                        }
                                                    @endphp
                                                    <li>
                                                        <strong>{{ $name }}</strong>
                                                        @if($type === 'per_person')
                                                            : Người lớn {{ $adultsSel }} — Trẻ em {{ $childrenSel }}
                                                            @if($days > 0)
                                                                — Ngày: {{ $days }}
                                                            @endif
                                                        @elseif($type === 'per_day' || $type === 'per_hour')
                                                            — Ngày: {{ max(1, $days) }} — SL: {{ max(1, $qty) }}
                                                        @elseif($type === 'per_service')
                                                            — SL: {{ max(1, $qty) }}
                                                        @elseif($type === 'per_night')
                                                            — Theo đêm
                                                        @endif
                                                        @if(!is_null($lineTotal))
                                                            — <span class="text-success">{{ number_format((float)$lineTotal) }} VNĐ</span>
                                                        @endif
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                    <p class="mb-1"><strong>Tiền dịch vụ (Admin thêm):</strong>
                                        <span class="text-{{ ($svcFromAdmin ?? 0) > 0 ? 'success' : 'muted' }}">{{ number_format($svcFromAdmin ?? 0) }} VNĐ</span>
                                    </p>
                                    <p><strong>Tiền dịch vụ (tổng):</strong>
                                        <span class="text-{{ ($svcTotal ?? 0) > 0 ? 'success' : 'muted' }}">{{ number_format($svcTotal ?? 0) }} VNĐ</span>
                                    </p>
                                    @php 
                                        $totalDiscount = $booking->payments()->where('status', '!=', 'failed')->sum('discount_amount');
                                        if ($totalDiscount <= 0 && (float)($booking->promotion_discount ?? 0) > 0) {
                                            $totalDiscount = (float) $booking->promotion_discount;
                                        }
                                        $grand = ($roomCost ?? 0) + ($guestSurcharge ?? 0) + ($roomChangeSurcharge ?? 0) + ($svcTotal ?? 0); 
                                        $finalAmount = $grand - ($totalDiscount ?? 0);
                                    @endphp
                                    @if($totalDiscount > 0)
                                        <p><strong>Khuyến mại:</strong>
                                            <span class="text-success">-{{ number_format($totalDiscount) }} VNĐ</span>
                                        </p>
                                    @endif
                                    <hr>
                                    <p class="mb-0"><strong>Tổng cộng:</strong>
                                        <span class="text-primary fw-bold">{{ number_format($finalAmount) }} VNĐ</span>
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

                                        <!-- Thu tiền phát sinh tại quầy -->
                                        @php
                                            // Tính lại tổng tiền theo logic mới để hiển thị ở phần thanh toán
                                            // 1) Số đêm và tiền phòng
                                            $ci2 = $booking->check_in_date instanceof \Carbon\Carbon ? $booking->check_in_date : \Carbon\Carbon::parse($booking->check_in_date);
                                            $co2 = $booking->check_out_date instanceof \Carbon\Carbon ? $booking->check_out_date : \Carbon\Carbon::parse($booking->check_out_date);
                                            $ciDate2 = $ci2 ? $ci2->copy()->startOfDay() : null;
                                            $coDate2 = $co2 ? $co2->copy()->startOfDay() : null;
                                            $nights2 = ($ciDate2 && $coDate2) ? (int) $ciDate2->diffInDays($coDate2) : 0;
                                            $nightly2 = (int)($booking->room->roomType->price ?? $booking->room->price ?? 0);
                                            $roomCost2 = max(0, $nights2) * $nightly2;

                                            // 2) Phụ phí người lớn/trẻ em & phụ thu đổi phòng
                                            $surcharge2 = (float)($booking->surcharge ?? 0);
                                            $roomChangeSurcharge2 = (float) $booking->roomChanges()
                                                ->whereIn('status', ['approved', 'completed'])
                                                ->sum('price_difference');
                                            $guestSurcharge2 = max(0, $surcharge2 - $roomChangeSurcharge2);

                                            // 3) Dịch vụ (khách chọn + admin thêm)
                                            $svcFromAdmin2 = (float)($booking->total_services_price ?? 0);
                                            $svcFromClient2 = (float)($booking->extra_services_total ?? 0);
                                            $svcTotal2 = $svcFromAdmin2 + $svcFromClient2;

                                            // 4) Khuyến mại và tổng tiền cuối
                                            $totalDiscount2 = $booking->payments()->where('status', '!=', 'failed')->sum('discount_amount');
                                            $totalBeforeDiscount = (float)($roomCost2 + $guestSurcharge2 + $roomChangeSurcharge2 + $svcTotal2);
                                            $totalPrice = $totalBeforeDiscount - $totalDiscount2;
                                            $totalPaid = (float)($booking->total_paid ?? 0);
                                            $outstanding = max(0, $totalPrice - $totalPaid);
                                        @endphp
                                        <div class="row mt-2">
                                            <div class="col-md-8">
                                                <div class="card border-0 bg-light">
                                                    <div class="card-body py-2">
                                                        <div class="d-flex flex-wrap gap-3 align-items-center small">
                                                            <div><strong>Tổng tiền:</strong> <span class="text-primary fw-semibold">{{ number_format($totalPrice) }} VNĐ</span></div>
                                                            <div><strong>Đã thu:</strong> <span class="text-success fw-semibold">{{ number_format($totalPaid) }} VNĐ</span></div>
                                                            <div><strong>Còn thiếu:</strong> <span class="fw-bold {{ $outstanding > 0 ? 'text-danger' : 'text-muted' }}">{{ number_format($outstanding) }} VNĐ</span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <form action="{{ route('admin.bookings.payments.collect', $booking->id) }}" method="POST" class="d-flex gap-2 align-items-start">
                                                    @csrf
                                                    <div class="flex-grow-1">
                                                        <input type="number" name="amount" class="form-control form-control-sm" placeholder="Số tiền thu (VNĐ)" min="0" step="1000" value="{{ (int)$outstanding }}" {{ $outstanding <= 0 ? 'disabled' : '' }}>
                                                        <input type="hidden" name="note" value="Thu tiền phát sinh tại quầy">
                                                    </div>
                                                    <button type="submit" class="btn btn-sm btn-primary" {{ $outstanding <= 0 ? 'disabled' : '' }}>
                                                        <i class="fas fa-money-bill-wave"></i> Thu tiền
                                                    </button>
                                                </form>
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

                    <!-- Hóa đơn VAT -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                                                    <div class="card-header">
                                        <i class="fas fa-file-invoice-dollar me-1"></i>
                                        Hóa đơn VAT
                                         {{-- <small class="text-muted">(Trạng thái: {{ $booking->vat_invoice_status ?? 'pending' }})</small>
                                        <div class="small text-muted mt-1">
                                            File path: {{ $booking->vat_invoice_file_path ?? 'Chưa có' }}
                                        </div> --}}
                                    </div>
                                <div class="card-body">
                                    @php $vatInfo = (array)($booking->vat_invoice_info ?? []); @endphp
                                    @if(!empty($vatInfo))
                                        <div class="row small">
                                            <div class="col-md-4"><strong>Công ty:</strong> {{ $vatInfo['companyName'] ?? '' }}</div>
                                            <div class="col-md-4"><strong>MST:</strong> {{ $vatInfo['taxCode'] ?? '' }}</div>
                                            <div class="col-md-4"><strong>Email nhận HĐ:</strong> {{ $vatInfo['receiverEmail'] ?? '' }}</div>
                                            <div class="col-12 mt-1"><strong>Địa chỉ:</strong> {{ $vatInfo['companyAddress'] ?? '' }}</div>
                                        </div>
                                        <div class="mt-3 d-flex gap-2 flex-wrap">
                                            <form action="{{ route('admin.bookings.vat.generate', $booking->id) }}" method="POST" class="me-2 mb-1">
                                                @csrf
                                                <button class="btn btn-outline-primary btn-sm"><i class="fas fa-file-pdf"></i> Tạo hóa đơn PDF</button>
                                            </form>
                                            
                                            @if($booking->vat_invoice_file_path)
                                                <a class="btn btn-info btn-sm me-2 mb-1" href="{{ route('admin.bookings.vat.preview', $booking->id) }}" target="_blank">
                                                    <i class="fas fa-eye"></i> Xem trước
                                                </a>
                                                <a class="btn btn-secondary btn-sm me-2 mb-1" href="{{ route('admin.bookings.vat.download', $booking->id) }}">
                                                    <i class="fas fa-download"></i> Tải xuống
                                                </a>
                                            @endif
                                            
                                            <form action="{{ route('admin.bookings.vat.send', $booking->id) }}" method="POST" class="mb-1">
                                                @csrf
                                                <button class="btn btn-primary btn-sm" {{ empty($booking->vat_invoice_file_path) ? 'disabled' : '' }}>
                                                    <i class="fas fa-envelope"></i> Gửi email hóa đơn
                                                </button>
                                                @if(empty($booking->vat_invoice_file_path))
                                                    <small class="text-muted d-block mt-1">Cần tạo hóa đơn PDF trước</small>
                                                @endif
                                            </form>
                                        </div>
                                        @php
                                            $ci = $booking->check_in_date;
                                            $co = $booking->check_out_date;
                                            $nights = $ci && $co ? $ci->copy()->startOfDay()->diffInDays($co->copy()->startOfDay()) : 0;
                                            $nightly = (int)($booking->room->roomType->price ?? $booking->room->price ?? 0);
                                            $roomCost = max(0, $nights) * $nightly;
                                            $services = (float)($booking->extra_services_total ?? 0) + (float)($booking->total_services_price ?? 0);
                                            $surcharge = (float)($booking->surcharge ?? 0);
                                            $discount = (float) $booking->payments()->where('status','!=','failed')->sum('discount_amount');
                                            $subtotal = $roomCost + $services + $surcharge - $discount;
                                            $vatRate = 0.1;
                                            $vatAmount = max(0, round($subtotal * $vatRate));
                                            $grandTotal = $subtotal + $vatAmount;
                                        @endphp
                                        
                                        <div class="alert alert-light mt-3 mb-0">
                                            <i class="fas fa-info-circle"></i>
                                            <strong>Thông tin VAT:</strong> Giá tiền đã bao gồm VAT 10%, không thu thêm phí. Hóa đơn chỉ để khách kê khai thuế.
                                        </div>
                                        
                                        @if($grandTotal >= 5000000)
                                            <div class="alert alert-warning mt-3 mb-0">
                                                <i class="fas fa-exclamation-triangle"></i>
                                                <strong>Lưu ý quan trọng:</strong> Hóa đơn từ 5.000.000đ ({{ number_format($grandTotal) }} VNĐ) phải thanh toán bằng thẻ/tài khoản công ty hoặc chuyển khoản công ty theo quy định pháp luật.
                                            </div>
                                        @else
                                            <div class="alert alert-info mt-3 mb-0">
                                                <i class="fas fa-info-circle"></i>
                                                <strong>Thông tin:</strong> Hóa đơn dưới 5.000.000đ ({{ number_format($grandTotal) }} VNĐ) cho phép thanh toán cá nhân trước, sau đó khách chuyển khoản công ty để xuất hóa đơn VAT.
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-muted">Chưa có yêu cầu xuất hóa đơn từ khách. Thực hiện từ phía khách hoặc liên hệ để bổ sung thông tin.</div>
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
                                            <span class="text-success fw-semibold">{{ number_format($bookingServices->sum('total_price')) }} VNĐ</span>
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
                                                            <div class="text-muted small">Phụ phí & dịch vụ: {{ number_format($booking->surcharge + $booking->extra_services_total + $booking->total_services_price) }} VNĐ</div>
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
                                                {{-- @if($booking->registration_status === 'generated')
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
                                                @else --}}
                                                    {{-- <div class="alert alert-info alert-sm">
                                                        <i class="fas fa-info-circle"></i>
                                                        Vui lòng tạo file trước khi xem/tải xuống
                                                    </div>
                                                @endif --}}
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