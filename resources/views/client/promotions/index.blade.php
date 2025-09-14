@extends('client.layouts.master')

@section('title', 'Khuyến Mại')

@section('content')
<div class="hero-wrap" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-items-end justify-content-center">
            <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <p class="breadcrumbs mb-2">
                        <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span> 
                        <span>Khuyến mại</span>
                    </p>
                    <h1 class="mb-4 bread">Khuyến Mại Đặc Biệt</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section bg-light">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="row justify-content-center mb-5 pb-3">
                    <div class="col-md-7 text-center heading-section">
                        <h2 class="mb-4">Ưu Đãi Hấp Dẫn</h2>
                        <p>Khám phá những khuyến mại đặc biệt dành riêng cho bạn. Tiết kiệm chi phí cho kỳ nghỉ hoàn hảo!</p>
                    </div>
                </div>

                {{-- Bộ lọc --}}
                <div class="row mb-4">
                    <div class="col-md-12">
                        <form method="GET" action="{{ route('promotions.index') }}" class="filter-form" id="filterForm">
                            <div class="row align-items-center">
                                <div class="col-md-3">
                                    <label for="discount_type" class="form-label small text-muted">Loại giảm giá</label>
                                    <select name="discount_type" id="discount_type" class="form-control">
                                        <option value="">Tất cả loại giảm giá</option>
                                        <option value="percentage" {{ request('discount_type') == 'percentage' ? 'selected' : '' }}>Giảm theo phần trăm</option>
                                        <option value="fixed" {{ request('discount_type') == 'fixed' ? 'selected' : '' }}>Giảm cố định</option>
                                    </select>
                                </div>
                                <div class="col-md-5">
                                    <label for="search" class="form-label small text-muted">Tìm kiếm</label>
                                    <input type="text" name="search" id="search" class="form-control" placeholder="Tìm kiếm theo tên, mô tả hoặc mã..." value="{{ request('search') }}">
                                </div>
                                <div class="col-md-4 pt-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i> Tìm kiếm
                                    </button>
                                    <a href="{{ route('promotions.index') }}" class="btn btn-outline-secondary ml-2">
                                        <i class="fas fa-times"></i> Reset
                                    </a>
                                    @if(request()->anyFilled(['discount_type', 'search']))
                                        <span class="badge badge-info ml-2">
                                            {{ $total_count }} kết quả
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Danh sách khuyến mại --}}
                @if($promotions->count() > 0)
                    <div class="row">
                        @foreach($promotions as $promotion)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    @if($promotion->image)
                                        <img src="{{ asset('storage/' . $promotion->image) }}" class="card-img-top" alt="{{ $promotion->title }}" style="height: 200px; object-fit: cover;">
                                    @else
                                        <div class="card-img-top bg-primary d-flex align-items-center justify-content-center text-white" style="height: 200px;">
                                            <div class="text-center">
                                                <i class="fas fa-percentage fa-3x mb-2"></i>
                                                <h3>{{ $promotion->discount_text }}</h3>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    <div class="card-body d-flex flex-column">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title">{{ $promotion->title }}</h5>
                                            <span class="badge badge-success">{{ $promotion->discount_text }}</span>
                                        </div>
                                        
                                        <p class="card-text text-muted flex-grow-1">
                                            {{ Str::limit($promotion->description, 100) }}
                                        </p>
                                        
                                        <div class="mb-3">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-calendar-alt"></i> 
                                                Hết hạn: {{ $promotion->expired_at->format('d/m/Y') }}
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-tag"></i> 
                                                Mã: <strong>{{ $promotion->code }}</strong>
                                            </small>
                                            @if($promotion->minimum_amount > 0)
                                                <small class="text-muted d-block">
                                                    <i class="fas fa-money-bill-wave"></i> 
                                                    Tối thiểu: {{ number_format($promotion->minimum_amount, 0, ',', '.') }}đ
                                                </small>
                                            @endif
                                        </div>
                                        
                                        <div class="d-flex justify-content-between">
                                            <a href="{{ route('promotions.show', $promotion->id) }}" class="btn btn-outline-primary btn-sm">
                                                <i class="fas fa-eye"></i> Chi tiết
                                            </a>
                                            <a href="{{ route('booking') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-calendar-check"></i> Đặt phòng
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Phân trang --}}
                    <div class="row">
                        <div class="col-md-12 text-center">
                            {{ $promotions->links() }}
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-12 text-center">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle fa-2x mb-3"></i>
                                <h4>Không có khuyến mại nào</h4>
                                <p>Hiện tại chưa có khuyến mại nào phù hợp với tìm kiếm của bạn.</p>
                                <a href="{{ route('promotions.index') }}" class="btn btn-primary">Xem tất cả khuyến mại</a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

@push('styles')
<link rel="stylesheet" href="{{ asset('client/css/pages/promotions.css') }}">
@endpush

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto submit form khi thay đổi discount_type
    const discountTypeSelect = document.getElementById('discount_type');
    if (discountTypeSelect) {
        discountTypeSelect.addEventListener('change', function() {
            document.getElementById('filterForm').submit();
        });
    }
    
    // Enter to submit search
    const searchInput = document.getElementById('search');
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.getElementById('filterForm').submit();
            }
        });
    }
});
</script>
@endsection 