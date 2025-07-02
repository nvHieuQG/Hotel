@extends('client.layouts.master')
@section('title', 'Chi tiết đánh giá')
@section('content')
<div class="container py-4">
    @if (session('success'))
        <div class="alert alert-success d-flex align-items-center justify-content-center mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger d-flex align-items-center justify-content-center mb-4" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger d-flex align-items-center justify-content-center mb-4" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Chi tiết đánh giá</h4>
                </div>
                <div class="card-body">
                    <p><strong>Ngày đánh giá:</strong> {{ $review->created_at->format('d/m/Y H:i') }}</p>
                    <p><strong>Số sao:</strong> <span class="text-warning">@for($i=1;$i<=5;$i++)<i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>@endfor</span></p>
                    <p><strong>Trạng thái:</strong> <span class="badge badge-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }}">{{ $review->status_text ?? $review->status }}</span></p>
                    <p><strong>Nội dung:</strong> {{ $review->content }}</p>
                    <hr>
                    <h5>Thông tin đặt phòng</h5>
                    <p><strong>Mã đặt phòng:</strong> {{ $review->booking->booking_id ?? 'N/A' }}</p>
                    <p><strong>Check-in:</strong> {{ $review->booking->check_in_date->format('d/m/Y') ?? 'N/A' }}</p>
                    <p><strong>Check-out:</strong> {{ $review->booking->check_out_date->format('d/m/Y') ?? 'N/A' }}</p>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ url()->previous() }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
        </div>
    </div>
</div>
@endsection 