@extends('admin.layouts.admin-master')

@section('title', 'Báo cáo Tour Bookings')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0"><i class="fas fa-chart-bar"></i> Báo cáo Tour Bookings</h3>
            <a href="{{ route('admin.tour-bookings.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Bộ lọc thời gian -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.tour-bookings.report') }}" class="row g-2">
                <div class="col-md-4">
                    <label class="small text-muted">Từ ngày</label>
                    <input type="date" class="form-control" name="start_date" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="small text-muted">Đến ngày</label>
                    <input type="date" class="form-control" name="end_date" value="{{ $endDate }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search"></i> Lọc</button>
                    <a href="{{ route('admin.tour-bookings.report') }}" class="btn btn-outline-secondary">Xóa lọc</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Thống kê tổng quan -->
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted">Tổng số booking</div>
                    <div class="h4 mb-0">{{ number_format($totalBookings) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted">Doanh thu</div>
                    <div class="h4 mb-0 text-success">{{ number_format($totalRevenue, 0, ',', '.') }} VNĐ</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 mb-3">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted mb-2">Theo trạng thái</div>
                    @php
                        $statusLabels = ['pending' => 'warning', 'confirmed' => 'success', 'cancelled' => 'danger', 'completed' => 'info'];
                    @endphp
                    @forelse($statusStats as $status => $count)
                        <span class="badge badge-{{ $statusLabels[$status] ?? 'secondary' }} mr-2 mb-2">{{ ucfirst($status) }}: {{ $count }}</span>
                    @empty
                        <div class="text-muted">Không có dữ liệu</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Bảng theo tháng -->
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">Thống kê theo tháng</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="thead-light">
                        <tr>
                            <th>Tháng</th>
                            <th>Số booking</th>
                            <th>Doanh thu (VNĐ)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($monthlyStats as $row)
                            <tr>
                                <td>{{ $row->month }}</td>
                                <td>{{ number_format($row->count) }}</td>
                                <td>{{ number_format($row->revenue, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Không có dữ liệu trong khoảng thời gian đã chọn</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection


