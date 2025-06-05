@extends('admin.layouts.admin-master')

@section('title', 'Dashboard')

@section('header', 'Dashboard')

@section('content')
<div class="row">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Đặt phòng (Hôm nay)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayBookings ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-calendar fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Doanh thu (Tháng)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($monthlyRevenue ?? 0, 0, ',', '.') }} VNĐ</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Tỷ lệ đặt phòng</div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ $bookingRate ?? 0 }}%</div>
                            </div>
                            <div class="col">
                                <div class="progress progress-sm mr-2">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: {{ $bookingRate ?? 0 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Đặt phòng chờ xác nhận</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingBookings ?? 0 }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-comments fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Đặt phòng gần đây</h6>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-primary">Xem tất cả</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Khách hàng</th>
                                <th>Phòng</th>
                                <th>Ngày nhận</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings ?? [] as $booking)
                            <tr>
                                <td>{{ $booking->booking_id }}</td>
                                <td>{{ $booking->user->name ?? 'N/A' }}</td>
                                <td>{{ $booking->room->name ?? 'N/A' }}</td>
                                <td>{{ $booking->check_in_date->format('d/m/Y') }}</td>
                                <td>
                                    @switch($booking->status)
                                        @case('pending')
                                            <span class="badge bg-warning">Chờ xác nhận</span>
                                            @break
                                        @case('confirmed')
                                            <span class="badge bg-success">Đã xác nhận</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Đã hủy</span>
                                            @break
                                        @case('completed')
                                            <span class="badge bg-info">Hoàn thành</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary">{{ $booking->status }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có đặt phòng nào</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Thống kê đặt phòng</h6>
            </div>
            <div class="card-body">
                <div class="chart-pie pt-4">
                    <canvas id="bookingStatusChart"></canvas>
                </div>
                <div class="mt-4 text-center small">
                    <span class="mr-2">
                        <i class="fas fa-circle text-warning"></i> Chờ xác nhận
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-success"></i> Đã xác nhận
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-danger"></i> Đã hủy
                    </span>
                    <span class="mr-2">
                        <i class="fas fa-circle text-info"></i> Hoàn thành
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Dữ liệu biểu đồ (có thể thay thế bằng dữ liệu thực tế)
    const statusData = {
        pending: {{ $statusCounts['pending'] ?? 0 }},
        confirmed: {{ $statusCounts['confirmed'] ?? 0 }},
        cancelled: {{ $statusCounts['cancelled'] ?? 0 }},
        completed: {{ $statusCounts['completed'] ?? 0 }}
    };

    // Tạo biểu đồ
    const ctx = document.getElementById('bookingStatusChart').getContext('2d');
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Chờ xác nhận', 'Đã xác nhận', 'Đã hủy', 'Hoàn thành'],
            datasets: [{
                data: [statusData.pending, statusData.confirmed, statusData.cancelled, statusData.completed],
                backgroundColor: ['#f6c23e', '#1cc88a', '#e74a3b', '#36b9cc'],
                hoverBackgroundColor: ['#daa520', '#169b6b', '#c0392b', '#2596be'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            maintainAspectRatio: false,
            tooltips: {
                backgroundColor: "rgb(255,255,255)",
                bodyFontColor: "#858796",
                borderColor: '#dddfeb',
                borderWidth: 1,
                xPadding: 15,
                yPadding: 15,
                displayColors: false,
                caretPadding: 10,
            },
            legend: {
                display: false
            },
            cutoutPercentage: 80,
        },
    });
</script>
@endsection 