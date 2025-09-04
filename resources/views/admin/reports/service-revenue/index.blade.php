@extends('admin.layouts.admin-master')

@section('title', 'Báo cáo doanh thu dịch vụ')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="mb-0">Báo cáo doanh thu dịch vụ</h4>
    </div>

    <form method="GET" action="{{ route('admin.reports.extra-services') }}" class="card card-body mb-3">
        <div class="row g-2">
            <div class="col-md-3">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="date_from" value="{{ old('date_from', $filters['date_from'] ?? '') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="date_to" value="{{ old('date_to', $filters['date_to'] ?? '') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">Dịch vụ</label>
                <select name="service_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @php
                        $sid = (int)($filters['service_id'] ?? 0);
                    @endphp
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" {{ $sid===$s->id?'selected':'' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 align-self-end">
                <button class="btn btn-primary w-100" type="submit">Lọc</button>
            </div>
        </div>
        <input type="hidden" name="statuses[]" value="checked_out">
        <input type="hidden" name="statuses[]" value="completed">
    </form>

    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card card-body h-100">
                <div class="text-muted">Số booking có dịch vụ</div>
                <div class="fs-4 fw-bold">{{ number_format($summary['totals']['bookings_with_services'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body h-100">
                <div class="text-muted">Khách hàng duy nhất</div>
                <div class="fs-4 fw-bold">{{ number_format($summary['totals']['unique_customers'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body h-100">
                <div class="text-muted">Doanh thu (dịch vụ + phụ phí)</div>
                @php
                    $svcCustomer = (float) ($summary['totals']['total_revenue'] ?? 0);
                    $svcSurcharge = (float) ($summary['totals']['surcharge_revenue'] ?? 0);
                    $svcAdmin = (float) ($summary['admin_totals']['total_revenue'] ?? 0);
                    $svcAll = $svcCustomer + $svcSurcharge + $svcAdmin;
                @endphp
                <div class="fs-4 fw-bold">{{ number_format($svcAll) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Dịch vụ theo doanh thu</h5>
            <small class="text-muted">Tổng tiền: {{ number_format($summary['totals']['total_revenue'] ?? 0) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Dịch vụ</th>
                        <th>Loại tính phí</th>
                        <th>Lượt sử dụng</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                @php
                    $ctMap = [
                        'per_person' => 'Theo người',
                        'per_night' => 'Theo đêm',
                        'per_day' => 'Theo ngày',
                        'per_service' => 'Theo lần',
                        'per_hour' => 'Theo giờ',
                    ];
                @endphp
                <tbody>
                    @if(!empty($top) && count($top) > 0)
                        @foreach($top as $i => $row)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $row['service_name'] ?? ('#'.$row['service_id']) }}</td>
                                <td>{{ $ctMap[strtolower($row['charge_type'] ?? '')] ?? ($row['charge_type'] ?? '') }}</td>
                                <td>{{ number_format($row['total_uses'] ?? 0) }}</td>
                                <td>{{ number_format($row['total_revenue'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Dịch vụ admin thêm theo doanh thu</h5>
            <small class="text-muted">Tổng tiền: {{ number_format($summary['admin_totals']['total_revenue'] ?? 0) }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Dịch vụ</th>
                        <th>Mã đặt phòng</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $adminRows = (array) ($summary['admin_by_service'] ?? []);
                    @endphp
                    @if(!empty($adminRows) && count($adminRows) > 0)
                        @foreach($adminRows as $i => $row)
                            <tr>
                                <td>{{ $i+1 }}</td>
                                <td>{{ $row['service_name'] ?? ('#'.$row['service_id']) }}</td>
                                <td>
                                    @php
                                        $codes = $row['booking_codes'] ?? [];
                                        $ids = $row['booking_ids'] ?? [];
                                    @endphp
                                    @if(!empty($codes))
                                        <span class="text-nowrap">{{ implode(', ', $codes) }}</span>
                                    @elseif(!empty($ids))
                                        <span class="text-nowrap">{{ implode(', ', $ids) }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>{{ number_format($row['total_revenue'] ?? 0) }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
