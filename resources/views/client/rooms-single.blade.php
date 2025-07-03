@extends('client.layouts.master')

@section('title', $room->roomType->name)

@section('content')
    <div class="hero-wrap" style="background-image: url('/client/images/bg_1.jpg');">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
                <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                    <div class="text">
                        <p class="breadcrumbs mb-2">
                            <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                            <span class="mr-2"><a href="{{ route('rooms') }}">Phòng</a></span>
                            <span>Chi tiết phòng</span>
                        </p>
                        <h1 class="mb-4 bread">{{ $room->roomType->name }}</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="ftco-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-12 ftco-animate">
                            <h2 class="mb-4">{{ $room->roomType->name }}</h2>
                            <div class="single-slider owl-carousel">
                                <div class="item">
                                    <div class="room-img" style="background-image: url('/client/images/room-1.jpg');"></div>
                                </div>
                                <div class="item">
                                    <div class="room-img" style="background-image: url('/client/images/room-2.jpg');"></div>
                                </div>
                                <div class="item">
                                    <div class="room-img" style="background-image: url('/client/images/room-3.jpg');"></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 room-single mt-4 mb-5 ftco-animate">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>Thông tin phòng</h4>
                                <p><span class="price mr-2">{{ number_format($room->price) }}đ</span> <span
                                        class="per">mỗi đêm</span></p>
                            </div>
                            <p class="mb-4">{{ $room->roomType->description }}</p>
                            <div class="d-md-flex mt-5 mb-5">
                                <ul class="list">
                                    <li><span>Sức chứa tối đa:</span> {{ $room->capacity }} người</li>
                                </ul>
                                <ul class="list ml-md-5">
                                    <li><span>Loại phòng:</span> {{ $room->roomType->name }}</li>
                                </ul>
                            </div>
                            @if ($room->status == 'available')
                                <div class="text-center">
                                    <a href="{{ route('booking') }}" class="btn btn-primary py-3 px-5">Đặt phòng ngay</a>
                                </div>
                            @endif
                        </div>

                        <!-- Phần đánh giá và bình luận -->
                        <div class="col-md-12 room-single ftco-animate mb-5 reviews-section">
                            <h4 class="mb-4">Đánh giá và bình luận</h4>
                            
                            <!-- Thống kê đánh giá -->
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <h2 class="mb-0 text-primary">{{ number_format($averageRating, 1) }}</h2>
                                            <div class="stars">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    @if ($i <= $averageRating)
                                                        <span class="icon-star text-warning"></span>
                                                    @elseif ($i - $averageRating < 1)
                                                        <span class="icon-star-half text-warning"></span>
                                                    @else
                                                        <span class="icon-star-o text-muted"></span>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                        <div>
                                            <p class="mb-1"><strong>{{ $reviewsCount }}</strong> đánh giá</p>
                                            <p class="mb-0 text-muted">Dựa trên {{ $reviewsCount }} đánh giá thực tế</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 text-right">
                                    @auth
                                        @if(isset($canReview) && $canReview)
                                            <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#reviewModal">
                                                <i class="icon-pencil"></i> Viết đánh giá
                                            </button>
                                        @else
                                            <span class="text-muted">
                                                <i class="icon-info"></i> Cần đặt phòng và hoàn thành để đánh giá
                                            </span>
                                        @endif
                                    @else
                                        <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="btn btn-outline-primary">
                                            <i class="icon-user"></i> Đăng nhập để đánh giá
                                        </a>
                                    @endauth
                                </div>
                            </div>

                            <!-- Form đánh giá (chỉ hiển thị khi đã đăng nhập và có booking hoàn thành) -->
                            @auth
                                @if(isset($canReview) && $canReview)
                                    <div class="review-form mb-4">
                                        <div class="card">
                                            <div class="card-body">
                                                <h5 class="card-title">Viết đánh giá của bạn</h5>
                                                <form id="reviewForm" data-room-type-id="{{ $room->roomType->id }}">
                                                    @csrf
                                                    <div class="form-group">
                                                        <label>Điểm đánh giá tổng thể:</label>
                                                        <div class="rating-input">
                                                            @for ($i = 5; $i >= 1; $i--)
                                                                <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" class="rating-radio" required>
                                                                <label for="star{{ $i }}" class="rating-star">☆</label>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Đánh giá chi tiết -->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Vệ sinh:</label>
                                                                <div class="rating-input">
                                                                    @for ($i = 5; $i >= 1; $i--)
                                                                        <input type="radio" name="cleanliness_rating" value="{{ $i }}" id="cleanliness{{ $i }}" class="rating-radio" required>
                                                                        <label for="cleanliness{{ $i }}" class="rating-star">☆</label>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Tiện nghi:</label>
                                                                <div class="rating-input">
                                                                    @for ($i = 5; $i >= 1; $i--)
                                                                        <input type="radio" name="comfort_rating" value="{{ $i }}" id="comfort{{ $i }}" class="rating-radio" required>
                                                                        <label for="comfort{{ $i }}" class="rating-star">☆</label>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Vị trí:</label>
                                                                <div class="rating-input">
                                                                    @for ($i = 5; $i >= 1; $i--)
                                                                        <input type="radio" name="location_rating" value="{{ $i }}" id="location{{ $i }}" class="rating-radio" required>
                                                                        <label for="location{{ $i }}" class="rating-star">☆</label>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label>Cơ sở vật chất:</label>
                                                                <div class="rating-input">
                                                                    @for ($i = 5; $i >= 1; $i--)
                                                                        <input type="radio" name="facilities_rating" value="{{ $i }}" id="facilities{{ $i }}" class="rating-radio" required>
                                                                        <label for="facilities{{ $i }}" class="rating-star">☆</label>
                                                                    @endfor
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label>Giá trị:</label>
                                                        <div class="rating-input">
                                                            @for ($i = 5; $i >= 1; $i--)
                                                                <input type="radio" name="value_rating" value="{{ $i }}" id="value{{ $i }}" class="rating-radio" required>
                                                                <label for="value{{ $i }}" class="rating-star">☆</label>
                                                            @endfor
                                                        </div>
                                                    </div>
                                                    
                                                    <div class="form-group">
                                                        <label for="comment">Bình luận:</label>
                                                        <textarea class="form-control" id="comment" name="comment" rows="3" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                                                    </div>
                                                    <div class="form-group">
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" class="custom-control-input" id="is_anonymous" name="is_anonymous">
                                                            <label class="custom-control-label" for="is_anonymous">Đánh giá ẩn danh</label>
                                                        </div>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="icon-paper-plane"></i> Gửi đánh giá
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endauth

                            <!-- Danh sách đánh giá -->
                            <div id="reviewsList">
                                @if($reviews->count() > 0)
                                    <div class="reviews-list">
                                        @foreach($reviews as $review)
                                            <div class="review-item border-bottom pb-3 mb-3">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <div class="d-flex align-items-center">
                                                        <div class="stars mr-2">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                                @if ($i <= $review->rating)
                                                                    <span class="icon-star text-warning"></span>
                                                                @else
                                                                    <span class="icon-star-o text-muted"></span>
                                                                @endif
                                                            @endfor
                                                        </div>
                                                        <span class="text-muted">{{ $review->rating }}/5</span>
                                                    </div>
                                                    <small class="text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</small>
                                                </div>
                                                
                                                <div class="review-content">
                                                    @if($review->is_anonymous)
                                                        <p class="mb-1"><strong>Khách hàng ẩn danh</strong></p>
                                                    @else
                                                        <p class="mb-1"><strong>{{ $review->user->name }}</strong></p>
                                                    @endif
                                                    
                                                    @if($review->comment)
                                                        <p class="mb-2">{{ $review->comment }}</p>
                                                    @else
                                                        <p class="mb-2 text-muted"><em>Không có bình luận</em></p>
                                                    @endif
                                                    
                                                    <!-- Hiển thị đánh giá chi tiết -->
                                                    @if($review->cleanliness_rating || $review->comfort_rating || $review->location_rating || $review->facilities_rating || $review->value_rating)
                                                        <div class="detailed-ratings">
                                                            <small class="text-muted">
                                                                @if($review->cleanliness_rating)
                                                                    <span class="mr-3">Vệ sinh: {{ $review->cleanliness_rating }}/5</span>
                                                                @endif
                                                                @if($review->comfort_rating)
                                                                    <span class="mr-3">Tiện nghi: {{ $review->comfort_rating }}/5</span>
                                                                @endif
                                                                @if($review->location_rating)
                                                                    <span class="mr-3">Vị trí: {{ $review->location_rating }}/5</span>
                                                                @endif
                                                                @if($review->facilities_rating)
                                                                    <span class="mr-3">Cơ sở vật chất: {{ $review->facilities_rating }}/5</span>
                                                                @endif
                                                                @if($review->value_rating)
                                                                    <span>Giá trị: {{ $review->value_rating }}/5</span>
                                                                @endif
                                                            </small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    
                                    <!-- Phân trang -->
                                    @if($reviews->hasPages())
                                        <div class="d-flex justify-content-center">
                                            {{ $reviews->links() }}
                                        </div>
                                    @endif
                                @else
                                    <div class="text-center py-4">
                                        <i class="icon-star-o text-muted" style="font-size: 3rem;"></i>
                                        <p class="text-muted mt-2">Chưa có đánh giá nào cho phòng này</p>
                                        @auth
                                            @if(isset($canReview) && $canReview)
                                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#reviewModal">
                                                    Viết đánh giá đầu tiên
                                                </button>
                                            @else
                                                <span class="text-muted">Cần đặt phòng và hoàn thành để đánh giá</span>
                                            @endif
                                        @else
                                            <a href="{{ route('login') }}?redirect={{ urlencode(request()->url()) }}" class="btn btn-primary">
                                                Đăng nhập để đánh giá
                                            </a>
                                        @endauth
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-12 room-single ftco-animate mb-5 mt-5">
                            <h4 class="mb-4">Các phòng khác cùng loại</h4>
                            <div class="row">
                                @foreach ($relatedRooms as $relRoom)
                                    <div class="col-sm col-md-6 ftco-animate">
                                        <div class="room">
                                            <a href="{{ route('rooms-single', $relRoom->id) }}"
                                                class="img img-2 d-flex justify-content-center align-items-center"
                                                style="background-image: url('/client/images/room-{{ $loop->iteration + 3 }}.jpg');">
                                                <div class="icon d-flex justify-content-center align-items-center">
                                                    <span class="icon-search2"></span>
                                                </div>
                                            </a>
                                            <div class="text p-3 text-center">
                                                <h3 class="mb-3"><a
                                                        href="{{ route('rooms-single', $relRoom->id) }}">{{ $relRoom->roomType->name }}</a></h3>
                                                <p><span class="price mr-2">{{ number_format($relRoom->price) }}đ</span>
                                                    <span class="per">mỗi đêm</span></p>
                                                <hr>
                                                <p class="pt-1"><a href="{{ route('rooms-single', $relRoom->id) }}"
                                                        class="btn-custom">Xem chi tiết <span
                                                            class="icon-long-arrow-right"></span></a></p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div> <!-- .col-md-8 -->

                <div class="col-lg-4 sidebar ftco-animate">
                    <div class="sidebar-box">
                        <form action="{{ route('rooms.search') }}" method="GET">
                            <div class="fields">
                                <div class="form-group">
                                    <input type="text" name="keyword" class="form-control" placeholder="Từ khóa"
                                        value="{{ request()->input('keyword') }}">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="sidebar-box ftco-animate">
                        <div class="categories">
                            <h3>Loại phòng</h3>
                            @foreach ($roomTypes as $type)
                                <li>
                                    <a href="{{ route('rooms.search', ['type' => $type->id]) }}">
                                        {{ $type->name }}
                                    </a>
                                </li>
                            @endforeach

                        </div>
                    </div>

                    <div class="sidebar-box ftco-animate">
                        <h3>Đặt phòng nhanh</h3>
                        <form action="{{ route('booking') }}" method="GET" class="p-3 bg-light">
                            <input type="hidden" name="room_id" value="{{ $room->id }}">
                            <div class="form-group">
                                <label for="checkin_date">Ngày nhận phòng</label>
                                <input type="date" name="check_in" class="form-control" required
                                    min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group">
                                <label for="checkout_date">Ngày trả phòng</label>
                                <input type="date" name="check_out" class="form-control" required
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                            <div class="form-group">
                                <label for="guests">Số khách</label>
                                <select name="guests" class="form-control">
                                    @for ($i = 1; $i <= $room->capacity; $i++)
                                        <option value="{{ $i }}">{{ $i }} người</option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary py-2 px-4">Đặt ngay</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
<style>
/* CSS cho rating stars */
.rating-input {
    display: inline-block;
    direction: rtl;
}

.rating-input input[type="radio"] {
    display: none;
}

.rating-star {
    font-size: 1.5rem;
    color: #ccc;
    cursor: pointer;
    transition: color 0.2s ease;
}

.rating-star:hover,
.rating-star:hover ~ .rating-star {
    color: #ffc107;
}

.rating-input input[type="radio"]:checked ~ .rating-star {
    color: #ffc107;
}

/* CSS cho detailed ratings */
.detailed-ratings {
    background-color: #f8f9fa;
    padding: 0.5rem;
    border-radius: 0.25rem;
    margin-top: 0.5rem;
}

.detailed-ratings span {
    display: inline-block;
    margin-right: 1rem;
}

/* CSS cho alert */
.alert-container {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 9999;
}

.custom-alert {
    padding: 1rem;
    margin-bottom: 0.5rem;
    border-radius: 0.25rem;
    color: white;
    position: relative;
    min-width: 300px;
}

.alert-success {
    background-color: #28a745;
}

.alert-danger {
    background-color: #dc3545;
}

.close-alert {
    position: absolute;
    top: 5px;
    right: 10px;
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
}

/* Responsive */
@media (max-width: 768px) {
    .rating-star {
        font-size: 1.2rem;
    }
    
    .detailed-ratings span {
        display: block;
        margin-bottom: 0.25rem;
    }
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Xử lý form đánh giá
    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const roomTypeId = form.data('room-type-id');
        const submitBtn = form.find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        // Disable button và hiển thị loading
        submitBtn.prop('disabled', true).html('<i class="icon-spinner"></i> Đang gửi...');
        
        // Lấy dữ liệu form
        const formData = new FormData(this);
        formData.append('room_type_id', roomTypeId);
        
        // Gửi request AJAX
        fetch('/room-type-reviews/store-ajax', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Hiển thị thông báo thành công
                showAlert('Đánh giá đã được gửi thành công!', 'success');
                
                // Reset form
                form[0].reset();
                $('.rating-star').removeClass('text-warning').addClass('text-muted');
                
                // Reload danh sách đánh giá
                loadReviews(roomTypeId);
            } else {
                showAlert(data.message || 'Có lỗi xảy ra khi gửi đánh giá!', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Có lỗi xảy ra khi gửi đánh giá!', 'danger');
        })
        .finally(() => {
            // Enable button và khôi phục text
            submitBtn.prop('disabled', false).html(originalText);
        });
    });
    
    // Xử lý hover rating stars cho tất cả các loại đánh giá
    $('.rating-star').hover(
        function() {
            const forId = $(this).attr('for');
            const rating = forId.replace(/^(star|cleanliness|comfort|location|facilities|value)/, '');
            const type = forId.replace(rating, '');
            highlightStarsByType(type, rating);
        },
        function() {
            const forId = $(this).attr('for');
            const type = forId.replace(/\d+$/, '');
            const selectedRating = $(`input[name="${getInputName(type)}"]:checked`).val();
            if (selectedRating) {
                highlightStarsByType(type, selectedRating);
            } else {
                $(`.rating-star[for^="${type}"]`).removeClass('text-warning').addClass('text-muted');
            }
        }
    );
    
    // Xử lý click rating stars
    $('.rating-star').click(function() {
        const forId = $(this).attr('for');
        const rating = forId.replace(/^(star|cleanliness|comfort|location|facilities|value)/, '');
        const type = forId.replace(rating, '');
        const inputName = getInputName(type);
        $(`input[name="${inputName}"]`).val([rating]);
        highlightStarsByType(type, rating);
    });
    
    function getInputName(type) {
        const mapping = {
            'star': 'rating',
            'cleanliness': 'cleanliness_rating',
            'comfort': 'comfort_rating',
            'location': 'location_rating',
            'facilities': 'facilities_rating',
            'value': 'value_rating'
        };
        return mapping[type] || 'rating';
    }
    
    function highlightStarsByType(type, rating) {
        $(`.rating-star[for^="${type}"]`).removeClass('text-warning').addClass('text-muted');
        for (let i = 1; i <= rating; i++) {
            $(`label[for="${type}${i}"]`).removeClass('text-muted').addClass('text-warning');
        }
    }
});

function highlightStars(rating) {
    $('.rating-star[for^="star"]').removeClass('text-warning').addClass('text-muted');
    for (let i = 1; i <= rating; i++) {
        $(`label[for="star${i}"]`).removeClass('text-muted').addClass('text-warning');
    }
}

function loadReviews(roomTypeId) {
    fetch(`/room-type-reviews/${roomTypeId}/ajax`)
        .then(response => response.text())
        .then(html => {
            $('#reviewsList').html(html);
        })
        .catch(error => {
            console.error('Error loading reviews:', error);
        });
}

function showAlert(message, type) {
    const alertContainer = $('.alert-container');
    if (alertContainer.length === 0) {
        $('body').append('<div class="alert-container"></div>');
    }
    
    const alert = $(`
        <div class="custom-alert alert-${type}">
            <button type="button" class="close-alert">&times;</button>
            ${message}
        </div>
    `);
    
    $('.alert-container').append(alert);
    
    // Tự động ẩn sau 5 giây
    setTimeout(() => {
        alert.fadeOut(300, function() {
            $(this).remove();
        });
    }, 5000);
    
    // Xử lý nút đóng
    alert.find('.close-alert').click(function() {
        alert.fadeOut(300, function() {
            $(this).remove();
        });
    });
}
</script>
@endsection
