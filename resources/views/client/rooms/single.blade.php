@extends('client.layouts.master')

@section('title', $roomType->name)

@section('content')
    <div class="hero-wrap" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
                <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                    <div class="text">
                        <p class="breadcrumbs mb-2">
                            <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                            <span class="mr-2"><a href="{{ route('rooms') }}">Loại phòng</a></span>
                            <span>Chi tiết loại phòng</span>
                        </p>
                        <h1 class="mb-4 bread">{{ $roomType->name }}</h1>
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
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <h2 class="mb-0">{{ $roomType->name }}</h2>
                                @php
                                    $promotionData = \App\Models\Promotion::getBestPromotionForRoomType($roomType->id, (float)$roomType->price);
                                    $bestPromotion = $promotionData ? $promotionData['promotion'] : null;
                                @endphp
                                @if($bestPromotion)
                                    <div class="promotion-badge">
                                        <span class="badge badge-danger px-3 py-2" style="font-size: 1rem; background: linear-gradient(45deg, #ff6b6b, #ee5a52);">
                                            <i class="fas fa-tag mr-1"></i>
                                            {{ $promotionData['discount_text'] }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="single-slider owl-carousel">
                                @php
                                    $roomImages = collect();
                                    $rooms = $roomType->rooms()->with(['primaryImage', 'firstImage'])->get();
                                    
                                    foreach ($rooms as $room) {
                                        if ($room->primaryImage) {
                                            $roomImages->push($room->primaryImage->full_image_url);
                                        } elseif ($room->firstImage) {
                                            $roomImages->push($room->firstImage->full_image_url);
                                        }
                                    }
                                    
                                    // Nếu không có ảnh thực tế, sử dụng ảnh mẫu
                                    if ($roomImages->isEmpty()) {
                                        $roomImages = collect([
                                            asset('client/images/room-1.jpg'),
                                            asset('client/images/room-2.jpg'),
                                            asset('client/images/room-3.jpg')
                                        ]);
                                    }
                                @endphp
                                
                                @foreach($roomImages->take(5) as $imageUrl)
                                    <div class="item">
                                        <div class="room-img" style="background-image: url('{{ $imageUrl }}');"></div>
                                    </div>
                                @endforeach
                            </div>  
                        </div>
                        <div class="col-md-12 room-single mt-4 mb-5 ftco-animate">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h4>Thông tin loại phòng</h4>
                                <div class="text-right">
                                    @php $basePrice = (int) $roomType->price; 
                                        $bestData = \App\Models\Promotion::getBestPromotionForRoomType($roomType->id, $basePrice);
                                        $initialFinal = $bestData ? (int) $bestData['final_price'] : $basePrice;
                                        $hasBest = !empty($bestData);
                                    @endphp
                                    <div>
                                        <span id="price_original" class="text-muted" style="text-decoration: line-through; {{ $hasBest ? '' : 'display:none;' }}">{{ number_format($basePrice) }}đ</span>
                                        <span id="price_final" class="price ml-2">{{ number_format($initialFinal) }}đ</span>
                                    </div>
                                </div>
                            </div>
                            <p class="mb-4">{{ $roomType->description }}</p>
                            <div class="d-md-flex mt-5 mb-5">
                                <ul class="list">
                                    <li><span>Sức chứa tối đa:</span> {{ $roomType->capacity }} người</li>
                                </ul>
                                <ul class="list ml-md-5">
                                    <li><span>Mô tả:</span> {{ Str::limit($roomType->description, 100) }}</li>
                                </ul>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-7">
                                   
                                    @if(!empty($topPromotions) || !empty($allPromotions))
                                        <div class="promotion-section mb-4">
                                            <div class="card border-0 shadow-sm">
                                                <div class="card-header bg-gradient-primary text-danger">
                                                    <i class="fas fa-gift mr-2"></i> 
                                                    <strong>Ưu đãi đặc biệt</strong>
                                                </div>
                                                <div class="card-body p-3">
                                                    @php
                                                        $listToShow = !empty($topPromotions) ? $topPromotions : [];
                                                    @endphp
                                                    @if(!empty($listToShow))
                                                        @foreach($listToShow as $promo)
                                                            <div class="promotion-item mb-3 p-3 border rounded bg-light">
                                                                <div class="d-flex justify-content-between align-items-start">
                                                                    <div class="flex-grow-1">
                                                                        <div class="fw-bold text-primary mb-1">{{ $promo['title'] }}</div>
                                                                        <div class="small text-muted">{{ Str::limit($promo['description'] ?? '', 80) }}</div>
                                                                    </div>
                                                                    <div class="text-end ms-2">
                                                                        <div class="badge bg-success mb-1 text-white">{{ $promo['discount_text'] }}</div>
                                                                        @if(!empty($promo['code']))
                                                                            <div class="badge bg-secondary small text-white">{{ $promo['code'] }}</div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                        @if(!empty($allPromotions) && count($allPromotions) > count($listToShow))
                                                            <details class="mt-3">
                                                                <summary class="text-primary small cursor-pointer">
                                                                    <i class="fas fa-chevron-down"></i> Xem thêm {{ count($allPromotions) - count($listToShow) }} ưu đãi
                                                                </summary>
                                                                <div class="mt-2">
                                                                    @foreach($allPromotions as $promo)
                                                                        @if(!in_array($promo['id'], array_column($listToShow, 'id')))
                                                                            <div class="promotion-item mb-2 p-2 border rounded bg-light">
                                                                                <div class="d-flex justify-content-between align-items-start">
                                                                                    <div class="flex-grow-1">
                                                                                        <div class="fw-bold text-primary mb-1">{{ $promo['title'] }}</div>
                                                                                        <div class="small text-muted">{{ Str::limit($promo['description'] ?? '', 60) }}</div>
                                                                                    </div>
                                                                                    <div class="text-end ms-2">
                                                                                        <div class="badge bg-success mb-1">{{ $promo['discount_text'] }}</div>
                                                                                        @if(!empty($promo['code']))
                                                                                            <div class="badge bg-secondary small">{{ $promo['code'] }}</div>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            </details>
                                                        @endif
                                                    @else
                                                        <div class="text-center text-muted py-3">
                                                            <i class="fas fa-gift fa-2x mb-2"></i>
                                                            <div>Hiện chưa có khuyến mại phù hợp</div>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Thông tin phòng -->
                                    <div class="room-info">
                                        <h4 class="mb-3">Thông tin phòng</h4>
                                        <ul class="list ml-md-5">
                                            <li><span>Mô tả:</span> {{ Str::limit($roomType->description, 100) }}</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="card border-0 shadow-sm">
                                        <div class="card-header bg-gradient-success text-danger">
                                            <i class="fas fa-receipt mr-2"></i> 
                                            <strong>Tóm tắt giá</strong>
                                        </div>
                                        <div class="card-body">
                                            @php $nights = isset($nights) ? (int)$nights : 1; $sumBase = $basePrice * $nights; $sumFinal = $initialFinal * $nights; $sumDiscount = $sumBase - $sumFinal; @endphp
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Giá gốc ({{ $nights }} đêm)</span>
                                                <span id="sum_original" class="fw-bold">{{ number_format($sumBase) }} đ</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-2 text-success">
                                                <span>Khuyến mại</span>
                                                <span id="sum_discount" class="fw-bold">- {{ number_format(max(0,$sumDiscount)) }} đ</span>
                                            </div>
                                            <hr class="my-3">
                                            <div class="d-flex justify-content-between fw-bold fs-5">
                                                <span>Giá sau giảm</span>
                                                <span id="sum_final" class="text-primary">{{ number_format($sumFinal) }} đ</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Dịch vụ đi kèm --}}
@if ($roomType->services && $roomType->services->count())
    <div class="services-section mb-5">
        <div class="card border-0 shadow-sm">
                <h4>Dịch vụ đi kèm</h4>
            <div class="card-body">
                @foreach ($serviceCategories as $category)
                    @php
                        $servicesInCategory = $category->services->filter(function ($service) use ($roomType) {
                            return $roomType->services->contains($service);
                        });
                    @endphp

                    @if ($servicesInCategory->count())
                        <div class="mb-4">
                            <h6 class="text-dark mb-2" style="font-size: 14px; font-weight: normal;">
                                <i class="fas fa-star text-warning me-2"></i>
                                {{ $category->name }}
                            </h6>
                            <div class="row">
                                @foreach ($servicesInCategory->chunk(ceil($servicesInCategory->count() / 3)) as $chunk)
                                    <div class="col-md-4">
                                        @foreach ($chunk as $service)
                                            <div class="mb-2">
                                                <span
                                                    class="badge bg-light border text-dark px-2 py-1 w-100 d-block text-start"
                                                    style="font-size: 12.5px; font-weight: normal;">
                                                    {{ $service->name }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endif

                            <div class="text-center">
                                @php
                                    $bookingParams = [];
                                    if (request()->has('check_in_date')) {
                                        $bookingParams['check_in_date'] = request()->get('check_in_date');
                                    }
                                    if (request()->has('check_out_date')) {
                                        $bookingParams['check_out_date'] = request()->get('check_out_date');
                                    }
                                    if (request()->has('guests')) {
                                        $bookingParams['guests'] = request()->get('guests');
                                    }
                                    $bookingParams['room_type_id'] = $roomType->id;
                                @endphp
                                <a id="bookNowBtn" href="{{ route('booking') }}?{{ http_build_query($bookingParams) }}" class="btn btn-primary py-3 px-5">Đặt phòng ngay</a>
                            </div>
                        </div>

                        <!-- Phần đánh giá -->
                        <div class="col-md-12 room-single ftco-animate mb-5 reviews-section">
                            <h4 class="mb-4">Đánh giá</h4>
                            
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
                                            {{-- <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#reviewModal">
                                                <i class="icon-pencil"></i> Viết đánh giá
                                            </button> --}}
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
                                @if($completedBookings->isNotEmpty())
                                    <div class="review-form mb-4">
                                        <div class="card border-0 shadow-sm {{ $completedBookings->count() > 1 ? 'border-warning' : '' }}">
                                            <div class="card-header {{ $completedBookings->count() > 1 ? 'bg-warning text-dark' : 'bg-primary text-white' }}">
                                                <h5 class="card-title mb-0">
                                                    <i class="fas fa-star mr-2"></i>Viết đánh giá của bạn
                                                    @if($completedBookings->count() > 1)
                                                        <span class="badge badge-primary ml-2">{{ $completedBookings->count() }} booking chưa đánh giá</span>
                                                    @endif
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <!-- Chọn booking để đánh giá (chỉ hiển thị khi có từ 2 booking trở lên) -->
                                                @if($completedBookings->count() > 1)
                                                    <div class="form-group mb-4">
                                                        <label for="booking_select" class="font-weight-bold text-dark">
                                                            <i class="fas fa-calendar-check text-primary mr-2"></i>Chọn booking để đánh giá:
                                                        </label>
                                                        <select id="booking_select" class="form-control border-0 bg-light" required>
                                                            <option value="">Chọn booking...</option>
                                                            @foreach($completedBookings as $booking)
                                                                <option value="{{ $booking->id }}" data-booking-id="{{ $booking->id }}">
                                                                    Booking #{{ $booking->booking_id }} - {{ $booking->room->roomType->name }} 
                                                                    ({{ $booking->check_in_date->format('d/m/Y') }} - {{ $booking->check_out_date->format('d/m/Y') }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @else
                                                    <!-- Tự động chọn booking nếu chỉ có 1 -->
                                                    <input type="hidden" id="booking_select" value="{{ $completedBookings->first()->id }}">
                                                    <input type="hidden" id="selected_booking_id" value="{{ $completedBookings->first()->id }}">
                                                    <div class="alert alert-info mb-4">
                                                        <i class="fas fa-info-circle mr-2"></i>
                                                        <strong>Đánh giá cho:</strong> Booking #{{ $completedBookings->first()->booking_id }} - {{ $completedBookings->first()->room->roomType->name }}
                                                        ({{ $completedBookings->first()->check_in_date->format('d/m/Y') }} - {{ $completedBookings->first()->check_out_date->format('d/m/Y') }})
                                                    </div>
                                                @endif
                                                
                                                <form id="reviewForm" data-room-type-id="{{ $roomType->id }}" class="review-form-modern">
                                                    @csrf
                                                    <input type="hidden" name="booking_id" id="selected_booking_id">
                                                    
                                                    <!-- Rating tổng thể -->
                                                    <div class="form-group mb-4">
                                                        <label class="form-label font-weight-bold text-dark mb-3">
                                                            <i class="fas fa-star text-warning mr-2"></i>Đánh giá tổng thể
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <div class="rating-container">
                                                            <div class="rating-stars" data-rating="0">
                                                                @for ($i = 5; $i >= 1; $i--)
                                                                    <input type="radio" name="rating" value="{{ $i }}" id="overall_star{{ $i }}" class="rating-input" required>
                                                                    <label for="overall_star{{ $i }}" class="rating-star" data-value="{{ $i }}">
                                                                        <i class="fas fa-star"></i>
                                                                    </label>
                                                                @endfor
                                                            </div>
                                                            <div class="rating-text mt-2">
                                                                <span class="text-muted">Chọn số sao để đánh giá</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Nội dung đánh giá -->
                                                    <div class="form-group mb-4">
                                                        <label for="comment" class="form-label font-weight-bold text-dark mb-2">
                                                            <i class="fas fa-comment text-primary mr-2"></i>Nội dung đánh giá của bạn
                                                            <span class="text-danger">*</span>
                                                        </label>
                                                        <textarea name="comment" id="comment" class="form-control border-0 bg-light" rows="4" 
                                                                  placeholder="Chia sẻ trải nghiệm của bạn về phòng này... (tối thiểu 10 ký tự)" required></textarea>
                                                        <div class="form-text">
                                                            <small class="text-muted">
                                                                <i class="fas fa-info-circle mr-1"></i>
                                                                Nội dung đánh giá sẽ giúp khách hàng khác hiểu rõ hơn về chất lượng phòng
                                                            </small>
                                                        </div>
                                                    </div>

                                                    <!-- Tùy chọn ẩn danh -->
                                                    <div class="form-group mb-4">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" class="custom-control-input" id="is_anonymous" name="is_anonymous" value="1">
                                                            <label class="custom-control-label" for="is_anonymous">
                                                                <i class="fas fa-user-secret text-muted mr-2"></i>
                                                                Đánh giá ẩn danh
                                                            </label>
                                                        </div>
                                                        <small class="form-text text-muted">
                                                            Khi bật, tên của bạn sẽ không hiển thị trong đánh giá
                                                        </small>
                                                    </div>

                                                    <!-- Nút gửi -->
                                                    <div class="form-actions">
                                                        <button type="submit" class="btn btn-primary btn-lg btn-block shadow-sm" disabled>
                                                            <i class="fas fa-paper-plane mr-2"></i>
                                                            <span class="btn-text">Gửi đánh giá</span>
                                                            <span class="btn-loading d-none">
                                                                <i class="fas fa-spinner fa-spin mr-2"></i>Đang gửi...
                                                            </span>
                                                        </button>
                                                    </div>
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
                                                        <p class="mb-2 text-muted"><em>Không có nội dung đánh giá</em></p>
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
                            <h4 class="mb-4">Các loại phòng khác</h4>
                            <div class="row">
                                @foreach ($roomTypes->where('id', '!=', $roomType->id)->take(3) as $otherType)
                                    <div class="col-sm col-md-6 ftco-animate">
                                        <div class="room">
                                            <a href="{{ route('rooms-single', $otherType->id) }}"
                                                class="img img-2 d-flex justify-content-center align-items-center"
                                                style="background-image: url('{{ asset('client/images/room-' . ($loop->iteration + 3) . '.jpg') }}');">
                                                <div class="icon d-flex justify-content-center align-items-center">
                                                    <span class="icon-search2"></span>
                                                </div>
                                            </a>
                                            <div class="text p-3 text-center">
                                                <h3 class="mb-3"><a
                                                        href="{{ route('rooms-single', $otherType->id) }}">{{ $otherType->name }}</a></h3>
                                                <p><span class="price mr-2">{{ number_format($otherType->price) }}đ</span>
                                                    <span class="per">mỗi đêm</span></p>
                                                <hr>
                                                <p class="pt-1"><a href="{{ route('rooms-single', $otherType->id) }}"
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
                    <div class="sidebar-box ftco-animate">
                        <form action="{{ route('booking') }}" method="GET" class="p-3 bg-light">
                            <h3>Đặt phòng này vào ngày:</h3>
                            <input type="hidden" name="room_type_id" value="{{ $roomType->id }}">
                            <div class="form-group">
                                <label for="checkin_date">Ngày nhận phòng</label>
                                <input type="date" name="check_in_date" class="form-control" required
                                    min="{{ date('Y-m-d') }}" 
                                    value="{{ request()->get('check_in_date', date('Y-m-d')) }}">
                            </div>
                            <div class="form-group">
                                <label for="checkout_date">Ngày trả phòng</label>
                                <input type="date" name="check_out_date" class="form-control" required
                                    min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                    value="{{ request()->get('check_out_date', date('Y-m-d', strtotime('+1 day'))) }}">
                            </div>
                            <div class="form-group">
                                <label for="guests">Số khách</label>
                                <select name="guests" class="form-control">
                                    @for ($i = 1; $i <= $roomType->capacity; $i++)
                                        <option value="{{ $i }}" {{ request()->get('guests', 2) == $i ? 'selected' : '' }}>
                                            {{ $i }} người
                                        </option>
                                    @endfor
                                </select>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary py-2 px-4">Đặt ngay</button>
                            </div>
                        </form>
                    </div>
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
                                    <a href="{{ route('rooms-single', $type->id) }}">
                                        {{ $type->name }}
                                    </a>
                                </li>
                            @endforeach

                        </div>
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
    display: none;
}

.rating-star {
    font-size: 2.5rem;
    color: #e0e0e0;
    cursor: pointer;
    transition: color 0.2s ease;
}

.rating-star:hover,
.rating-star:hover ~ .rating-star,
.rating-input:checked ~ .rating-star {
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

/* Highlight form khi có nhiều booking */
.review-form .card.border-warning {
    border: 2px solid #ffc107 !important;
    box-shadow: 0 0 15px rgba(255, 193, 7, 0.3) !important;
}

.review-form .card.border-warning .card-header {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 193, 7, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 193, 7, 0);
    }
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Xử lý form đặt phòng nhanh
    const quickBookingForm = $('.sidebar-box form');
    const checkInDate = quickBookingForm.find('input[name="check_in_date"]');
    const checkOutDate = quickBookingForm.find('input[name="check_out_date"]');
    const guestsSelect = quickBookingForm.find('select[name="guests"]');

    // Tự động cập nhật ngày check-out khi thay đổi ngày check-in
    checkInDate.on('change', function() {
        if (this.value) {
            const checkIn = new Date(this.value);
            const nextDay = new Date(checkIn);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutDate.attr('min', nextDay.toISOString().split('T')[0]);
            
            // Nếu ngày check-out hiện tại nhỏ hơn ngày check-in + 1, cập nhật
            if (checkOutDate.val() && new Date(checkOutDate.val()) <= checkIn) {
                checkOutDate.val(nextDay.toISOString().split('T')[0]);
            }
        }
    });

    // Validation cho form đặt phòng nhanh
    quickBookingForm.on('submit', function(e) {
        const checkIn = new Date(checkInDate.val());
        const checkOut = new Date(checkOutDate.val());
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (checkIn < today) {
            alert('Ngày check-in không thể là ngày trong quá khứ!');
            checkInDate.focus();
            e.preventDefault();
            return;
        }

        if (checkOut <= checkIn) {
            alert('Ngày check-out phải sau ngày check-in!');
            checkOutDate.focus();
            e.preventDefault();
            return;
        }

        // Form hợp lệ, cho phép submit
        console.log('Đặt phòng nhanh với thông tin:', {
            room_type_id: {{ $roomType->id }},
            check_in_date: checkInDate.val(),
            check_out_date: checkOutDate.val(),
            guests: guestsSelect.val()
        });
    });

    // Rating stars chính
    $('.rating-stars').each(function() {
        const container = $(this);
        const stars = container.find('.rating-star');
        const text = container.siblings('.rating-text');
        const ratingTexts = [
            'Chọn số sao để đánh giá',
            'Rất không hài lòng',
            'Không hài lòng', 
            'Bình thường',
            'Hài lòng',
            'Rất hài lòng'
        ];
        
        stars.on('click', function() {
            const value = $(this).data('value');
            container.attr('data-rating', value);
            text.text(ratingTexts[value]);
            
            // Reset tất cả stars
            stars.find('i').removeClass('text-warning').addClass('text-muted');
            
            // Highlight stars đã chọn
            stars.each(function() {
                const starValue = $(this).data('value');
                if (starValue <= value) {
                    $(this).find('i').removeClass('text-muted').addClass('text-warning');
                }
            });
        });
        
        // Hover effect
        stars.on('mouseenter', function() {
            const value = $(this).data('value');
            stars.find('i').removeClass('text-warning').addClass('text-muted');
            stars.each(function() {
                const starValue = $(this).data('value');
                if (starValue <= value) {
                    $(this).find('i').removeClass('text-muted').addClass('text-warning');
                }
            });
        });
        
        stars.on('mouseleave', function() {
            const currentRating = container.attr('data-rating') || 0;
            stars.find('i').removeClass('text-warning').addClass('text-muted');
            stars.each(function() {
                const starValue = $(this).data('value');
                if (starValue <= currentRating) {
                    $(this).find('i').removeClass('text-muted').addClass('text-warning');
                }
            });
        });
    });
    
    // Rating stars nhỏ
    $('.rating-stars-sm').each(function() {
        const container = $(this);
        const stars = container.find('.rating-star-sm');
        
        stars.on('click', function() {
            const value = $(this).data('value');
            container.attr('data-rating', value);
            
            // Reset tất cả stars
            stars.find('i').removeClass('text-warning').addClass('text-muted');
            
            // Highlight stars đã chọn
            stars.each(function() {
                const starValue = $(this).data('value');
                if (starValue <= value) {
                    $(this).find('i').removeClass('text-muted').addClass('text-warning');
                }
            });
        });
        
        // Hover effect
        stars.on('mouseenter', function() {
            const value = $(this).data('value');
            stars.find('i').removeClass('text-warning').addClass('text-muted');
            stars.each(function() {
                const starValue = $(this).data('value');
                if (starValue <= value) {
                    $(this).find('i').removeClass('text-muted').addClass('text-warning');
                }
            });
        });
        
        stars.on('mouseleave', function() {
            const currentRating = container.attr('data-rating') || 0;
            stars.find('i').removeClass('text-warning').addClass('text-muted');
            stars.each(function() {
                const starValue = $(this).data('value');
                if (starValue <= currentRating) {
                    $(this).find('i').removeClass('text-muted').addClass('text-warning');
                }
            });
        });
    });
    
    // Xử lý chọn booking
    $('#booking_select').on('change', function() {
        const selectedBookingId = $(this).val();
        $('#selected_booking_id').val(selectedBookingId);
        
        // Enable/disable form dựa trên việc chọn booking
        if (selectedBookingId) {
            $('#reviewForm .form-control, #reviewForm .rating-input').prop('disabled', false);
            $('#reviewForm button[type="submit"]').prop('disabled', false);
        } else {
            $('#reviewForm .form-control, #reviewForm .rating-input').prop('disabled', true);
            $('#reviewForm button[type="submit"]').prop('disabled', true);
        }
    });
    
    // Tự động enable form nếu chỉ có 1 booking (đã được chọn tự động)
    if ($('#booking_select').length && $('#booking_select').val()) {
        $('#reviewForm .form-control, #reviewForm .rating-input').prop('disabled', false);
        $('#reviewForm button[type="submit"]').prop('disabled', false);
    }
    
    // Xử lý form đánh giá
    $('#reviewForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const roomTypeId = form.data('room-type-id');
        const bookingId = $('#selected_booking_id').val();
        const submitBtn = form.find('button[type="submit"]');
        const btnText = submitBtn.find('.btn-text');
        const btnLoading = submitBtn.find('.btn-loading');
        
        // Kiểm tra xem đã chọn booking chưa
        if (!bookingId) {
            showToast('Vui lòng chọn booking để đánh giá!', 'warning');
            return;
        }
        
        // Kiểm tra rating tổng thể
        const overallRating = form.find('.rating-stars').attr('data-rating');
        if (!overallRating || overallRating == '0') {
            showToast('Vui lòng chọn đánh giá tổng thể!', 'warning');
            return;
        }
        
        // Kiểm tra comment
        const comment = form.find('#comment').val().trim();
        if (comment.length < 10) {
            showToast('Nội dung đánh giá phải có ít nhất 10 ký tự!', 'warning');
            return;
        }
        
        // Disable button và hiển thị loading
        submitBtn.prop('disabled', true);
        btnText.addClass('d-none');
        btnLoading.removeClass('d-none');
        
        // Lấy dữ liệu form
        const formData = new FormData(this);
        formData.append('room_type_id', roomTypeId);
        formData.append('booking_id', bookingId);
        
        // Gửi request AJAX
        
        $.ajax({
            url: '/room-type-reviews/store-ajax',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success) {
                    // Hiển thị thông báo thành công
                    showToast('Đánh giá đã được gửi thành công!', 'success');
                    
                    // Reset form
                    form[0].reset();
                    $('#booking_select').val('');
                    $('#selected_booking_id').val('');
                    $('.rating-stars').attr('data-rating', '0');
                    $('.rating-star i').removeClass('text-warning').addClass('text-muted');
                    $('.rating-text').text('Chọn số sao để đánh giá');
                    
                    // Disable form
                    $('#reviewForm .form-control, #reviewForm .rating-input').prop('disabled', true);
                    $('#reviewForm button[type="submit"]').prop('disabled', true);
                    
                    // Tự động load danh sách đánh giá mới
                    loadReviews(roomTypeId);
                    
                    // Ẩn form đánh giá sau khi gửi thành công
                    setTimeout(() => {
                        $('.review-form').fadeOut();
                    }, 1000);
                } else {
                    showToast(data.message || 'Có lỗi xảy ra khi gửi đánh giá!', 'danger');
                }
            },
            error: function(xhr, status, error) {
                
                let errorMessage = 'Có lỗi xảy ra khi gửi đánh giá!';
                
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.status === 419) {
                    errorMessage = 'Lỗi CSRF token. Vui lòng refresh trang và thử lại.';
                } else if (xhr.status === 401) {
                    errorMessage = 'Bạn cần đăng nhập để đánh giá.';
                } else if (xhr.status === 403) {
                    errorMessage = 'Bạn không có quyền thực hiện hành động này.';
                }
                
                showToast(errorMessage, 'danger');
            },
            complete: function() {
                // Enable button và khôi phục text
                submitBtn.prop('disabled', false);
                btnText.removeClass('d-none');
                btnLoading.addClass('d-none');
            }
        });
    });
    
    // Character counter cho comment
    $('#comment').on('input', function() {
        const length = $(this).val().length;
        const minLength = 10;
        const maxLength = 1000;
        
        if (length < minLength) {
            $(this).addClass('is-invalid').removeClass('is-valid');
        } else if (length > maxLength) {
            $(this).addClass('is-invalid').removeClass('is-valid');
        } else {
            $(this).removeClass('is-invalid').addClass('is-valid');
        }
    });
});

// Khuyến mại: cập nhật giá và link đặt phòng
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="promotion_id"]');
    const priceOriginalEl = document.getElementById('price_original');
    const priceFinalEl = document.getElementById('price_final');
    const sumOriginalEl = document.getElementById('sum_original');
    const sumDiscountEl = document.getElementById('sum_discount');
    const sumFinalEl = document.getElementById('sum_final');
    const bookNowBtn = document.getElementById('bookNowBtn');

    const baseNightPrice = {{ (int) $roomType->price }};
    const nights = {{ isset($nights) ? (int)$nights : 1 }};

    function formatVND(number) {
        return Number(number).toLocaleString('vi-VN') + ' đ';
    }

    function updatePrice(discountAmount, finalAmount) {
        const base = baseNightPrice;
        if (discountAmount > 0) {
            priceOriginalEl.style.display = '';
            priceOriginalEl.textContent = formatVND(base);
        } else {
            priceOriginalEl.style.display = 'none';
        }
        priceFinalEl.textContent = formatVND(finalAmount / nights);
        sumOriginalEl.textContent = formatVND(base * nights);
        sumDiscountEl.textContent = '- ' + formatVND(discountAmount);
        sumFinalEl.textContent = formatVND(finalAmount);
    }

    function preview(promotionId) {
        const url = new URL(`{{ route('api.room-type.promotion-preview') }}`, window.location.origin);
        url.searchParams.set('room_type_id', '{{ $roomType->id }}');
        const checkIn = new URLSearchParams(window.location.search).get('check_in_date');
        const checkOut = new URLSearchParams(window.location.search).get('check_out_date');
        if (checkIn) url.searchParams.set('check_in_date', checkIn);
        if (checkOut) url.searchParams.set('check_out_date', checkOut);
        if (promotionId) url.searchParams.set('promotion_id', promotionId);

        fetch(url.toString(), { headers: { 'Accept': 'application/json' }})
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    updatePrice(data.discount_amount || 0, data.final_amount || (baseNightPrice * nights));
                }
            })
            .catch(() => {});
    }

    radios.forEach(r => {
        r.addEventListener('change', function() {
            preview(this.value);

            // Thêm promotion_id vào link Đặt phòng
            if (bookNowBtn) {
                const url = new URL(bookNowBtn.getAttribute('href'), window.location.origin);
                url.searchParams.set('promotion_id', this.value);
                bookNowBtn.setAttribute('href', url.pathname + '?' + url.searchParams.toString());
            }
        });
    });
});

function loadReviews(roomTypeId) {
    $.ajax({
        url: `/room-type-reviews/${roomTypeId}/ajax`,
        type: 'GET',
        success: function(html) {
            $('#reviewsList').html(html);
        },
        error: function(xhr, status, error) {
            console.error('Error loading reviews:', error);
        }
    });
}

// Toast notification function
function showToast(message, type = 'info') {
    const toast = $(`
        <div class="toast-notification toast-notification-${type}" role="alert">
            <div class="toast-header">
                <i class="fas fa-${type === 'success' ? 'check-circle text-success' : type === 'warning' ? 'exclamation-triangle text-warning' : type === 'danger' ? 'times-circle text-danger' : 'info-circle text-info'} mr-2"></i>
                <strong class="mr-auto">Thông báo</strong>
                <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                    <span>&times;</span>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `);
    
    // Thêm toast vào container
    if ($('#toast-container').length === 0) {
        $('body').append('<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
    }
    
    $('#toast-container').append(toast);
    toast.toast({ delay: 5000 }).toast('show');
    
    // Tự động xóa sau khi ẩn
    toast.on('hidden.bs.toast', function() {
        $(this).remove();
    });
}
</script>

<style>
/* Review form styles */
.review-form-modern {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.rating-container {
    text-align: center;
}

.rating-stars {
    display: inline-flex;
    flex-direction: row-reverse;
    gap: 8px;
}

.rating-input, .rating-input-sm {
    display: none;
}

.rating-star {
    font-size: 2.5rem;
    color: #e0e0e0;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 4px;
    border-radius: 50%;
}

.rating-star:hover,
.rating-star:hover ~ .rating-star,
.rating-input:checked ~ .rating-star {
    color: #ffc107;
    transform: scale(1.1);
}

.rating-star i {
    transition: all 0.2s ease;
}

.rating-text {
    font-size: 0.9rem;
    min-height: 20px;
}

/* Rating stars nhỏ cho đánh giá chi tiết */
.rating-stars-sm {
    display: inline-flex;
    flex-direction: row-reverse;
    gap: 4px;
}

.rating-star-sm {
    font-size: 1.2rem;
    color: #e0e0e0;
    cursor: pointer;
    transition: all 0.2s ease;
    padding: 2px;
}

.rating-star-sm:hover,
.rating-star-sm:hover ~ .rating-star-sm,
.rating-input-sm:checked ~ .rating-star-sm {
    color: #ffc107;
    transform: scale(1.1);
}

.rating-item {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.2s ease;
}

.rating-item:hover {
    background: #ffffff;
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0,123,255,0.1);
}

.rating-label {
    display: block;
    font-weight: 600;
    color: #495057;
    margin-bottom: 8px;
    font-size: 0.9rem;
}

.form-control {
    border-radius: 8px;
    border: 2px solid #e9ecef;
    transition: all 0.2s ease;
    font-size: 0.95rem;
}

.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    background-color: #ffffff;
}

.custom-switch .custom-control-label::before {
    border-radius: 20px;
    height: 24px;
    width: 44px;
}

.custom-switch .custom-control-label::after {
    border-radius: 50%;
    height: 18px;
    width: 18px;
    top: 3px;
    left: 3px;
}

.custom-switch .custom-control-input:checked ~ .custom-control-label::after {
    transform: translateX(20px);
}

.btn-lg {
    padding: 12px 24px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-lg:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,123,255,0.3);
}

.btn-loading {
    display: none;
}

.btn-loading.d-none {
    display: none !important;
}

/* Toast styles */
.toast-notification {
    min-width: 300px;
    margin-bottom: 10px;
}

.toast-notification-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.toast-notification-warning {
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

.toast-notification-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.toast-notification-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
}

/* Responsive */
@media (max-width: 768px) {
    .rating-star {
        font-size: 2rem;
    }
    
    .rating-star-sm {
        font-size: 1rem;
    }
    
    .rating-item {
        padding: 12px;
    }
    
    .btn-lg {
        padding: 10px 20px;
        font-size: 1rem;
    }
}

/* Card improvements */
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

.card-header {
    border-bottom: none;
    padding: 1rem 1.25rem;
}

.card-body {
    padding: 1.25rem;
}

/* Badge improvements */
.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
}

.badge.bg-success {
    background-color: #28a745 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

/* Form check improvements */
.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

.form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Promotion badge styles */
.promotion-badge .badge {
    box-shadow: 0 2px 8px rgba(0,0,0,0.2);
    border: 2px solid #fff;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.promotion-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>
@endsection
