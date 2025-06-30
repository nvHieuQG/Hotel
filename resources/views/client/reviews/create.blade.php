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
                        <span class="mr-2"><a href="{{ route('reviews.index') }}">Đánh giá phòng</a></span>
                        <span>Viết đánh giá</span>
                    </p>
                    <h1 class="mb-4 bread">Viết đánh giá</h1>
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
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i>
                        <strong>Lưu ý:</strong> Bạn chỉ có thể đánh giá những đặt phòng đã hoàn thành và chưa được đánh giá. 
                        Đánh giá của bạn sẽ được kiểm duyệt trước khi hiển thị công khai.
                    </div>

                    <h3 class="mb-4">Đánh giá phòng {{ $booking->room->name }}</h3>
                    
                    <div class="booking-info mb-4 p-3 bg-light rounded">
                        <h5><i class="fas fa-calendar-check"></i> Thông tin đặt phòng:</h5>
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
                        <div class="row mt-2">
                            <div class="col-12">
                                <p><strong>Loại phòng:</strong> {{ $booking->room->roomType->name ?? 'N/A' }}</p>
                                @if($booking->room->capacity)
                                    <p><strong>Sức chứa:</strong> {{ $booking->room->capacity }} người</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('reviews.store', $booking->id) }}" method="POST" id="reviewForm">
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
                            <div class="rating-labels mt-2">
                                <small class="text-muted">
                                    <span class="mr-3">1 - Rất không hài lòng</span>
                                    <span class="mr-3">2 - Không hài lòng</span>
                                    <span class="mr-3">3 - Bình thường</span>
                                    <span class="mr-3">4 - Hài lòng</span>
                                    <span>5 - Rất hài lòng</span>
                                </small>
                            </div>
                            @error('rating')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="comment" class="form-label">Bình luận</label>
                            <textarea name="comment" id="comment" rows="5" class="form-control @error('comment') is-invalid @enderror" placeholder="Chia sẻ trải nghiệm của bạn về phòng này, dịch vụ, nhân viên...">{{ old('comment') }}</textarea>
                            @error('comment')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Tối đa 1000 ký tự. Bình luận sẽ giúp chúng tôi cải thiện dịch vụ.</small>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_anonymous" name="is_anonymous" value="1" {{ old('is_anonymous') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_anonymous">
                                    <i class="fas fa-user-secret"></i> Đánh giá ẩn danh
                                </label>
                            </div>
                            <small class="form-text text-muted">Nếu chọn, tên của bạn sẽ không hiển thị trong đánh giá công khai</small>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">
                                <i class="fas fa-paper-plane"></i> Gửi đánh giá
                            </button>
                            <a href="{{ route('reviews.index') }}" class="btn btn-secondary btn-lg">
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
    gap: 5px;
}

.rating-input input[type="radio"] {
    display: none;
}

.star-label {
    font-size: 2.5rem;
    color: #ddd;
    cursor: pointer;
    transition: color 0.2s ease;
}

.star-label:hover,
.star-label:hover ~ .star-label,
.rating-input input[type="radio"]:checked ~ .star-label {
    color: #ffc107;
}

.rating-input:hover .star-label {
    color: #ddd;
}

.rating-input:hover .star-label:hover,
.rating-input:hover .star-label:hover ~ .star-label {
    color: #ffc107;
}

.rating-labels {
    font-size: 0.85rem;
}

.booking-info {
    border-left: 4px solid #007bff;
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    display: block;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const ratingInputs = document.querySelectorAll('input[name="rating"]');
    const starLabels = document.querySelectorAll('.star-label');
    const form = document.getElementById('reviewForm');
    const submitBtn = document.getElementById('submitBtn');
    
    console.log('Script loaded');
    console.log('Rating inputs:', ratingInputs.length);
    console.log('Star labels:', starLabels.length);
    
    // Hàm cập nhật màu sao
    function updateStars(selectedIndex) {
        starLabels.forEach((label, index) => {
            if (index <= selectedIndex) {
                label.style.color = '#ffc107';
            } else {
                label.style.color = '#ddd';
            }
        });
    }
    
    // Xử lý sự kiện click cho từng sao
    starLabels.forEach((label, index) => {
        label.addEventListener('click', function() {
            const radioInput = document.getElementById('star' + (5 - index));
            radioInput.checked = true;
            updateStars(index);
            console.log('Star clicked:', 5 - index);
        });
    });
    
    // Xử lý sự kiện change cho radio inputs
    ratingInputs.forEach((input, index) => {
        input.addEventListener('change', function() {
            updateStars(4 - index);
            console.log('Rating changed:', input.value);
        });
    });
    
    // Khởi tạo màu sao nếu có giá trị được chọn
    const checkedInput = document.querySelector('input[name="rating"]:checked');
    if (checkedInput) {
        const value = parseInt(checkedInput.value);
        updateStars(5 - value);
        console.log('Initial rating:', value);
    }
    
    // Xử lý form submit
    form.addEventListener('submit', function(e) {
        const rating = document.querySelector('input[name="rating"]:checked');
        if (!rating) {
            e.preventDefault();
            alert('Vui lòng chọn điểm đánh giá!');
            return false;
        }
        
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang gửi...';
        
        console.log('Form submitted');
        console.log('Rating:', rating.value);
        console.log('Comment:', document.getElementById('comment').value);
        console.log('Anonymous:', document.getElementById('is_anonymous').checked);
    });
});
</script>
@endsection 