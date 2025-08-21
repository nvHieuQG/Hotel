@extends('admin.layouts.admin-master')

@section('title', 'Thống kê VAT Invoice Tour Booking')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-bar"></i> Thống kê VAT Invoice Tour Booking
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.tour-vat-invoices.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Thống kê tổng quan -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ number_format($stats['total_requests']) }}</h3>
                                    <p>Tổng yêu cầu VAT</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ number_format($stats['pending_requests']) }}</h3>
                                    <p>Đang chờ xử lý</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ number_format($stats['generated_invoices']) }}</h3>
                                    <p>Đã xuất hóa đơn</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check"></i>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ number_format($stats['total_revenue'], 0, ',', '.') }}</h3>
                                    <p>Tổng doanh thu (VNĐ)</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Biểu đồ thống kê -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Tỷ lệ xử lý VAT Invoice</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="vatProcessingChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thống kê theo tháng</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="monthlyChart" width="400" height="200"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bảng thống kê chi tiết -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thống kê chi tiết theo tháng</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Tháng</th>
                                                    <th>Tổng yêu cầu</th>
                                                    <th>Đã xử lý</th>
                                                    <th>Chờ xử lý</th>
                                                    <th>Tỷ lệ xử lý</th>
                                                    <th>Doanh thu (VNĐ)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $currentMonth = now()->startOfMonth();
                                                    $months = collect();
                                                    for ($i = 0; $i < 12; $i++) {
                                                        $months->push($currentMonth->copy()->subMonths($i));
                                                    }
                                                @endphp
                                                
                                                @foreach($months as $month)
                                                    @php
                                                        $monthStart = $month->copy()->startOfMonth();
                                                        $monthEnd = $month->copy()->endOfMonth();
                                                        
                                                        $monthlyStats = [
                                                            'total' => \App\Models\TourBooking::where('need_vat_invoice', true)
                                                                ->whereBetween('created_at', [$monthStart, $monthEnd])
                                                                ->count(),
                                                            'processed' => \App\Models\TourBooking::where('need_vat_invoice', true)
                                                                ->whereNotNull('vat_invoice_number')
                                                                ->whereBetween('created_at', [$monthStart, $monthEnd])
                                                                ->count(),
                                                            'pending' => \App\Models\TourBooking::where('need_vat_invoice', true)
                                                                ->whereNull('vat_invoice_number')
                                                                ->whereBetween('created_at', [$monthStart, $monthEnd])
                                                                ->count(),
                                                            'revenue' => \App\Models\TourBooking::where('need_vat_invoice', true)
                                                                ->whereNotNull('vat_invoice_number')
                                                                ->whereBetween('created_at', [$monthStart, $monthEnd])
                                                                ->sum('final_price')
                                                        ];
                                                    @endphp
                                                    
                                                    <tr>
                                                        <td>{{ $month->format('m/Y') }}</td>
                                                        <td>{{ $monthlyStats['total'] }}</td>
                                                        <td>{{ $monthlyStats['processed'] }}</td>
                                                        <td>{{ $monthlyStats['pending'] }}</td>
                                                        <td>
                                                            @if($monthlyStats['total'] > 0)
                                                                {{ number_format(($monthlyStats['processed'] / $monthlyStats['total']) * 100, 1) }}%
                                                            @else
                                                                0%
                                                            @endif
                                                        </td>
                                                        <td>{{ number_format($monthlyStats['revenue'], 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thống kê theo công ty -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Top công ty yêu cầu VAT Invoice</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>STT</th>
                                                    <th>Tên công ty</th>
                                                    <th>Mã số thuế</th>
                                                    <th>Số lần yêu cầu</th>
                                                    <th>Tổng giá trị (VNĐ)</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $topCompanies = \App\Models\TourBooking::where('need_vat_invoice', true)
                                                        ->selectRaw('company_name, company_tax_code, COUNT(*) as request_count, SUM(final_price) as total_value')
                                                        ->groupBy('company_name', 'company_tax_code')
                                                        ->orderBy('request_count', 'desc')
                                                        ->limit(10)
                                                        ->get();
                                                @endphp
                                                
                                                @forelse($topCompanies as $index => $company)
                                                    <tr>
                                                        <td>{{ $index + 1 }}</td>
                                                        <td><strong>{{ $company->company_name }}</strong></td>
                                                        <td>{{ $company->company_tax_code }}</td>
                                                        <td>{{ $company->request_count }}</td>
                                                        <td>{{ number_format($company->total_value, 0, ',', '.') }}</td>
                                                        <td>
                                                            @php
                                                                $processedCount = \App\Models\TourBooking::where('need_vat_invoice', true)
                                                                    ->where('company_name', $company->company_name)
                                                                    ->whereNotNull('vat_invoice_number')
                                                                    ->count();
                                                                $processingRate = ($processedCount / $company->request_count) * 100;
                                                            @endphp
                                                            
                                                            @if($processingRate == 100)
                                                                <span class="badge badge-success">100% xử lý</span>
                                                            @elseif($processingRate > 50)
                                                                <span class="badge badge-warning">{{ number_format($processingRate, 1) }}% xử lý</span>
                                                            @else
                                                                <span class="badge badge-danger">{{ number_format($processingRate, 1) }}% xử lý</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center">
                                                            <p class="text-muted mb-0">Không có dữ liệu</p>
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Biểu đồ tỷ lệ xử lý VAT Invoice
    const vatProcessingCtx = document.getElementById('vatProcessingChart').getContext('2d');
    new Chart(vatProcessingCtx, {
        type: 'doughnut',
        data: {
            labels: ['Đã xử lý', 'Chờ xử lý'],
            datasets: [{
                data: [{{ $stats['generated_invoices'] }}, {{ $stats['pending_requests'] }}],
                backgroundColor: ['#28a745', '#ffc107'],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Biểu đồ thống kê theo tháng
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
            datasets: [{
                label: 'Yêu cầu VAT',
                data: [12, 19, 3, 5, 2, 3, 7, 8, 9, 10, 11, 12], // Dữ liệu mẫu
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection
