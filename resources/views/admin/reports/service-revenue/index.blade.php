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
                <label class="form-label">Trường ngày</label>
                <select name="date_field" class="form-select">
                    @php($df = $filters['date_field'] ?? 'check_out_date')
                    <option value="created_at" {{ $df==='created_at'?'selected':'' }}>Ngày tạo</option>
                    <option value="check_in_date" {{ $df==='check_in_date'?'selected':'' }}>Ngày nhận phòng</option>
                    <option value="check_out_date" {{ $df==='check_out_date'?'selected':'' }}>Ngày trả phòng</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Dịch vụ</label>
                <select name="service_id" class="form-select">
                    <option value="">-- Tất cả --</option>
                    @php($sid = (int)($filters['service_id'] ?? 0))
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" {{ $sid===$s->id?'selected':'' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Xếp hạng theo</label>
                @php($metric = request('metric','total_revenue'))
                <select name="metric" class="form-select">
                    <option value="total_revenue" {{ $metric==='total_revenue'?'selected':'' }}>Doanh thu</option>
                    <option value="bookings_count" {{ $metric==='bookings_count'?'selected':'' }}>Số lượt sử dụng</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Hiển thị Top</label>
                <input type="number" name="limit" value="{{ old('limit', request('limit', 10)) }}" min="1" class="form-control">
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
                <div class="text-muted">Doanh thu dịch vụ</div>
                <div class="fs-4 fw-bold">{{ number_format($summary['totals']['total_revenue'] ?? 0, 2) }}</div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Top dịch vụ</h5>
            <small class="text-muted">Theo {{ $metric==='bookings_count'?'số lượt sử dụng':'doanh thu' }}</small>
        </div>
        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Dịch vụ</th>
                        <th>Loại tính phí</th>
                        <th>Số lượt</th>
                        <th>KH duy nhất</th>
                        <th>SL</th>
                        <th>Ngày</th>
                        <th>Người lớn</th>
                        <th>Trẻ em</th>
                        <th>Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($top as $i => $row)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td>{{ $row['service_name'] ?? ('#'.$row['service_id']) }}</td>
                            <td>{{ $row['charge_type'] ?? '' }}</td>
                            <td>{{ number_format($row['bookings_count'] ?? 0) }}</td>
                            <td>{{ number_format($row['unique_customers'] ?? 0) }}</td>
                            <td>{{ number_format($row['total_quantity'] ?? 0) }}</td>
                            <td>{{ number_format($row['total_days'] ?? 0) }}</td>
                            <td>{{ number_format($row['total_adults_used'] ?? 0) }}</td>
                            <td>{{ number_format($row['total_children_used'] ?? 0) }}</td>
                            <td>{{ number_format($row['total_revenue'] ?? 0, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
