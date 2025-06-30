@extends('admin.layouts.admin-master')

@section('header', 'Tạo đánh giá mới')
@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.reviews.index') }}">Quản lý đánh giá</a></li>
        <li class="breadcrumb-item active">Tạo đánh giá mới</li>
    </ol>
    
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-gradient-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <i class="fas fa-star me-2"></i>
                    <h5 class="mb-0">Tạo đánh giá mới</h5>
                </div>
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-arrow-left me-1"></i> Quay lại
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('admin.reviews.store') }}" method="POST">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label for="booking_id" class="form-label fw-bold">
                            <i class="fas fa-calendar-check me-1"></i>Chọn booking <span class="text-danger">*</span>
                        </label>
                        <select name="booking_id" id="booking_id" class="form-select @error('booking_id') is-invalid @enderror" required>
                            <option value="">Chọn booking chưa đánh giá</option>
                            @foreach($bookings as $booking)
                                <option value="{{ $booking->id }}" {{ old('booking_id') == $booking->id ? 'selected' : '' }}>
                                    Booking #{{ $booking->booking_id }} - {{ $booking->user->name }} - {{ $booking->room->name }} 
                                    ({{ $booking->check_in->format('d/m/Y') }} - {{ $booking->check_out->format('d/m/Y') }})
                                </option>
                            @endforeach
                        </select>
                        @error('booking_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Chỉ hiển thị các booking đã hoàn thành và chưa được đánh giá
                        </small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="rating" class="form-label fw-bold">
                            <i class="fas fa-star me-1"></i>Đánh giá <span class="text-danger">*</span>
                        </label>
                        <select name="rating" id="rating" class="form-select @error('rating') is-invalid @enderror" required>
                            <option value="">Chọn đánh giá</option>
                            <option value="1" {{ old('rating') == 1 ? 'selected' : '' }}>⭐ 1 sao - Rất tệ</option>
                            <option value="2" {{ old('rating') == 2 ? 'selected' : '' }}>⭐⭐ 2 sao - Tệ</option>
                            <option value="3" {{ old('rating') == 3 ? 'selected' : '' }}>⭐⭐⭐ 3 sao - Bình thường</option>
                            <option value="4" {{ old('rating') == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ 4 sao - Tốt</option>
                            <option value="5" {{ old('rating') == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ 5 sao - Rất tốt</option>
                        </select>
                        @error('rating')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6">
                        <label for="status" class="form-label fw-bold">
                            <i class="fas fa-toggle-on me-1"></i>Trạng thái <span class="text-danger">*</span>
                        </label>
                        <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                            <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                            <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Từ chối</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label for="comment" class="form-label fw-bold">
                        <i class="fas fa-comment me-1"></i>Bình luận
                    </label>
                    <textarea name="comment" id="comment" rows="4" class="form-control @error('comment') is-invalid @enderror" 
                              placeholder="Nhập bình luận của khách hàng...">{{ old('comment') }}</textarea>
                    @error('comment')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted">Tối đa 1000 ký tự</small>
                </div>

                <div class="mb-3">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_anonymous">
                            <i class="fas fa-user-secret me-1"></i>Đánh giá ẩn danh
                        </label>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Tạo đánh giá
                    </button>
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times me-1"></i> Hủy
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-submit khi chọn booking để load thông tin
    $('#booking_id').change(function() {
        const bookingId = $(this).val();
        if (bookingId) {
            // Có thể thêm AJAX để load thông tin booking nếu cần
            console.log('Selected booking:', bookingId);
        }
    });
});
</script>
@endsection 