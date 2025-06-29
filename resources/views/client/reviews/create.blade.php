@extends('client.layouts.master')

@section('title', 'Đánh giá phòng')

@section('content')
<div class="hero-wrap" style="background-image: url('client/images/bg_1.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
            <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <p class="breadcrumbs mb-2">
                        <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                        <span class="mr-2"><a href="{{ route('my-bookings') }}">Đặt phòng của tôi</a></span>
                        <span>Đánh giá phòng</span>
                    </p>
                    <h1 class="mb-4 bread">Đánh giá phòng</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section bg-light">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="bg-white p-4">
                    <h3 class="mb-4">Đánh giá phòng {{ $booking->room->name }}</h3>
                    
                    <div class="booking-info mb-4 p-3 bg-light rounded">
                        <h5>Thông tin đặt phòng:</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mã đặt phòng:</strong> {{ $booking->booking_id }}</p>
                                <p><strong>Ngày nhận phòng:</strong> {{ $booking->check_in_date->format('d/m/Y') }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Ngày trả phòng:</strong> {{ $booking->check_out_date->format('d/m/Y') }}</p>
                                <p><strong>Tổng tiền:</strong> {{ number_format($booking->price) }}đ</p>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('reviews.store', $booking->id) }}" method="POST">
                        @csrf
                        
                        <div class="form-group">
                            <label for="rating" class="form-label">Điểm đánh giá <span class="text-danger">*</span></label>
                            <div class="rating-input">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" {{ old('rating') == $i ? 'checked' : '' }} required>
                                    <label for="star{{ $i }}" class="star-label">
                                        <i class="fas fa-star"></i>
                                    </label>
                                @endfor
                            </div>
                            @error('rating')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="comment" class="form-label">Bình luận</label>
                            <textarea name="comment" id="comment" rows="5" class="form-control @error('comment') is-invalid @enderror" placeholder="Chia sẻ trải nghiệm của bạn về phòng này...">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Tối đa 1000 ký tự</small>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="is_anonymous">
                                    Đánh giá ẩn danh
                                </label>
                            </div>
                            <small class="form-text text-muted">Nếu chọn, tên của bạn sẽ không hiển thị trong đánh giá</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Gửi đánh giá
                            </button>
                            <a href="{{ route('my-bookings') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.rating-input {
    display: flex;
    flex-direction: row-reverse;
    justify-content: flex-end;
}

.rating-input input {
    display: none;
}

.star-label {
    font-size: 2rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s;
}

.rating-input input:checked ~ .star-label,
.rating-input input:hover ~ .star-label,
.star-label:hover,
.star-label:hover ~ .star-label {
    color: #ffc107;
}

.rating-input:hover input:checked ~ .star-label {
    color: #ddd;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const starLabels = document.querySelectorAll('.star-label');
    
    ratingInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            // Reset all stars
            starLabels.forEach(label => {
                label.style.color = '#ddd';
            });
            
            // Color stars up to selected rating
            for (let i = 0; i <= index; i++) {
                starLabels[i].style.color = '#ffc107';
            }
        });
    });
    
    // Set initial rating if exists
    const checkedInput = document.querySelector('input[name="rating"]:checked');
    if (checkedInput) {
        const index = Array.from(ratingInputs).indexOf(checkedInput);
        for (let i = 0; i <= index; i++) {
            starLabels[i].style.color = '#ffc107';
        }
    }
});
</script>
@endsection 