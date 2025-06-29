@extends('admin.layouts.master')

@section('title', 'Thống kê đánh giá')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-chart-bar text-info"></i> Thống kê đánh giá
                        </h3>
                        <a href="{{ route('admin.reviews.index') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ $total_reviews }}</h4>
                                            <p class="mb-0">Tổng đánh giá</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-star fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ $pending_reviews }}</h4>
                                            <p class="mb-0">Chờ duyệt</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-clock fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ $approved_reviews }}</h4>
                                            <p class="mb-0">Đã duyệt</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-check-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-3 col-md-6 mb-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4 class="mb-0">{{ $rejected_reviews }}</h4>
                                            <p class="mb-0">Từ chối</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-times-circle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Rating Distribution Chart -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-chart-pie text-warning"></i> Phân bố đánh giá
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="rating-distribution">
                                        @for($i = 5; $i >= 1; $i--)
                                            @php
                                                $percentage = $approved_reviews > 0 ? ($rating_stats[$i] / $approved_reviews) * 100 : 0;
                                            @endphp
                                            <div class="rating-item mb-2">
                                                <div class="d-flex align-items-center">
                                                    <div class="rating-stars me-2">
                                                        @for($j = 1; $j <= 5; $j++)
                                                            <i class="fas fa-star{{ $j <= $i ? '' : '-o' }} text-warning"></i>
                                                        @endfor
                                                    </div>
                                                    <div class="progress flex-grow-1 me-2" style="height: 20px;">
                                                        <div class="progress-bar bg-warning" style="width: {{ $percentage }}%"></div>
                                                    </div>
                                                    <div class="rating-count" style="min-width: 60px;">
                                                        <span class="badge badge-secondary">{{ $rating_stats[$i] }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Average Rating -->
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-star text-warning"></i> Điểm đánh giá trung bình
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    <div class="average-rating">
                                        <div class="display-4 text-warning mb-3">
                                            {{ $average_rating }}/5
                                        </div>
                                        <div class="stars mb-3">
                                            @for($i = 1; $i <= 5; $i++)
                                                <i class="fas fa-star{{ $i <= round($average_rating) ? '' : '-o' }} fa-2x text-warning"></i>
                                            @endfor
                                        </div>
                                        <div class="rating-text">
                                            @if($average_rating >= 4.5)
                                                <span class="badge badge-success">Xuất sắc</span>
                                            @elseif($average_rating >= 4.0)
                                                <span class="badge badge-info">Tốt</span>
                                            @elseif($average_rating >= 3.0)
                                                <span class="badge badge-warning">Trung bình</span>
                                            @else
                                                <span class="badge badge-danger">Cần cải thiện</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Rooms -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">
                                        <i class="fas fa-trophy text-warning"></i> Top phòng được đánh giá nhiều nhất
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead class="table-dark">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Phòng</th>
                                                    <th>Số đánh giá</th>
                                                    <th>Điểm trung bình</th>
                                                    <th>Rating</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($top_rooms as $index => $room)
                                                    <tr>
                                                        <td>
                                                            @if($index == 0)
                                                                <i class="fas fa-crown text-warning"></i>
                                                            @elseif($index == 1)
                                                                <i class="fas fa-medal text-secondary"></i>
                                                            @elseif($index == 2)
                                                                <i class="fas fa-award text-bronze"></i>
                                                            @else
                                                                {{ $index + 1 }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <div class="fw-bold">{{ $room->name }}</div>
                                                            <small class="text-muted">{{ $room->roomType->name }}</small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-primary">{{ $room->approved_reviews_count }}</span>
                                                        </td>
                                                        <td>
                                                            <div class="fw-bold">{{ $room->average_rating }}/5</div>
                                                        </td>
                                                        <td>
                                                            <div class="text-warning">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <i class="fas fa-star{{ $i <= $room->stars ? '' : '-o' }}"></i>
                                                                @endfor
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="5" class="text-center py-4">
                                                            <i class="fas fa-star fa-2x text-muted mb-3"></i>
                                                            <p class="text-muted">Chưa có đánh giá nào</p>
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

<style>
.rating-distribution .rating-item {
    padding: 8px 0;
}

.rating-stars {
    min-width: 80px;
}

.progress {
    border-radius: 10px;
}

.progress-bar {
    border-radius: 10px;
}

.rating-count {
    text-align: right;
}

.average-rating .display-4 {
    font-weight: bold;
}

.stars .fas {
    margin: 0 2px;
}

.text-bronze {
    color: #cd7f32;
}

.card {
    border: none;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
}

.table th {
    font-weight: 600;
    background-color: #343a40;
    color: white;
}

.badge {
    font-size: 0.875rem;
}
</style>
@endsection 