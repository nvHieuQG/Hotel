@extends('client.layouts.master')

@section('title', 'Home')

@section('styles')
<style>
/* Modern Chat Widget Styles */

</style>
@endsection

@section('content')
    {{-- <nav class="navbar navbar-expand-lg navbar-dark ftco_navbar bg-dark ftco-navbar-light" id="ftco-navbar">
        <div class="container">
            <a class="navbar-brand" href="{{ route('index') }}">MARRON</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#ftco-nav" aria-controls="ftco-nav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="oi oi-menu"></span> Menu
            </button>
            <div class="collapse navbar-collapse" id="ftco-nav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item active"><a href="{{ route('index') }}" class="nav-link">Home</a></li>
                    <li class="nav-item"><a href="{{ route('rooms') }}" class="nav-link">Rooms</a></li>
                    <li class="nav-item"><a href="{{ route('restaurant') }}" class="nav-link">Restaurant</a></li>
                    <li class="nav-item"><a href="{{ route('about') }}" class="nav-link">About</a></li>
                    <li class="nav-item"><a href="{{ route('blog') }}" class="nav-link">Blog</a></li>
                    <li class="nav-item"><a href="{{ route('contact') }}" class="nav-link">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav> --}}
    <!-- END nav -->

    <section class="home-slider owl-carousel">
        <div class="slider-item" style="background-image:url(client/images/bg_1.jpg);">
            <div class="overlay"></div>
            <div class="container">
                <div class="row no-gutters slider-text align-items-center justify-content-center">
                    <div class="col-md-12 ftco-animate text-center">
                        <div class="text mb-5 pb-3">
                            <h1 class="mb-3">Welcome To MARRON</h1>
                            <h2>Hotels &amp; Resorts</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="slider-item" style="background-image:url(client/images/bg_2.jpg);">
            <div class="overlay"></div>
            <div class="container">
                <div class="row no-gutters slider-text align-items-center justify-content-center">
                    <div class="col-md-12 ftco-animate text-center">
                        <div class="text mb-5 pb-3">
                            <h1 class="mb-3">Enjoy A Luxury Experience</h1>
                            <h2>Join With Us</h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="ftco-booking">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <form action="{{ route('rooms') }}" method="GET" class="booking-form" id="availabilityForm">
                        <div class="row">
                            <div class="col-md-3 d-flex">
                                <div class="form-group p-4 align-self-stretch d-flex align-items-end">
                                    <div class="wrap">
                                        <label for="check_in_date">Check-in Date</label>
                                        <input type="date" name="check_in_date" id="check_in_date" class="form-control checkin_date"
                                               placeholder="Check-in date" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 d-flex">
                                <div class="form-group p-4 align-self-stretch d-flex align-items-end">
                                    <div class="wrap">
                                        <label for="check_out_date">Check-out Date</label>
                                        <input type="date" name="check_out_date" id="check_out_date" class="form-control checkout_date"
                                               placeholder="Check-out date" min="{{ date('Y-m-d', strtotime('+1 day')) }}" value="{{ date('Y-m-d', strtotime('+1 day')) }}">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex">
                                <div class="form-group p-4 align-self-stretch d-flex align-items-end">
                                    <div class="wrap">
                                        <label for="guests">Số khách</label>
                                        <div class="form-field">
                                            <div class="select-wrap">
                                                <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                                <select name="guests" id="guests" class="form-control">
                                                    <option value="1">1 Người</option>
                                                    <option value="2" selected>2 Người</option>
                                                    <option value="3">3 Người</option>
                                                    <option value="4">4 Người</option>
                                                    <option value="5">5 Người</option>
                                                    <option value="6">6 Người</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2 d-flex">
                                <div class="form-group p-4 align-self-stretch d-flex align-items-end">
                                    <div class="wrap">
                                        <label for="booking_type">Loại đặt phòng</label>
                                        <div class="form-field">
                                            <div class="select-wrap">
                                                <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                                <select name="booking_type" id="booking_type" class="form-control">
                                                    <option value="individual">Cá nhân</option>
                                                    <option value="tour">Tour du lịch</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Button submit ở dòng riêng và full width -->
                        <div class="row mt-3">
                            <div class="col-12">
                                <div class="form-group text-center">
                                    <button type="submit" class="btn btn-primary py-3 px-5 w-100" id="searchBtn" style="font-size: 18px; font-weight: 600;">
                                        <i class="fa fa-search mr-2"></i>Tìm kiếm phòng
                                    </button>
                                </div>
                            </div>
                        </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('availabilityForm');
            const bookingTypeSelect = document.getElementById('booking_type');
            const searchBtn = document.getElementById('searchBtn');

            form.addEventListener('submit', function(e) {
                const bookingType = bookingTypeSelect.value;

                if (bookingType === 'tour') {
                    e.preventDefault();

                    // Lấy dữ liệu từ form
                    const formData = new FormData(form);
                    const searchParams = new URLSearchParams();

                    // Thêm các tham số cần thiết cho tour booking
                    searchParams.append('check_in_date', formData.get('check_in_date'));
                    searchParams.append('check_out_date', formData.get('check_out_date'));
                    searchParams.append('total_guests', formData.get('guests'));
                    searchParams.append('tour_name', 'Tour du lịch - ' + new Date().toLocaleDateString('vi-VN'));

                    // Chuyển hướng đến trang tour booking
                    window.location.href = '{{ route("tour-booking.search") }}?' + searchParams.toString();
                }
            });

            // Cập nhật label và placeholder dựa trên loại đặt phòng
            bookingTypeSelect.addEventListener('change', function() {
                const guestsSelect = document.getElementById('guests');
                const guestsLabel = document.querySelector('label[for="guests"]');

                if (this.value === 'tour') {
                    guestsLabel.textContent = 'Tổng số khách';
                    guestsSelect.innerHTML = '';
                    for (let i = 5; i <= 50; i += 5) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = i + ' Người';
                        if (i === 10) option.selected = true;
                        guestsSelect.appendChild(option);
                    }
                } else {
                    guestsLabel.textContent = 'Số khách';
                    guestsSelect.innerHTML = '';
                    for (let i = 1; i <= 6; i++) {
                        const option = document.createElement('option');
                        option.value = i;
                        option.textContent = i + ' Người';
                        if (i === 2) option.selected = true;
                        guestsSelect.appendChild(option);
                    }
                }
            });
        });
    </script>

    <section class="ftco-section ftc-no-pb ftc-no-pt">
			<div class="container">
				<div class="row">
					<div class="col-md-5 p-md-5 img img-2 d-flex justify-content-center align-items-center" style="background-image: url(client/images/bg_2.jpg);">
						<a href="https://vimeo.com/45830194" class="icon popup-vimeo d-flex justify-content-center align-items-center">
							<span class="icon-play"></span>
						</a>
					</div>
					<div class="col-md-7 py-5 wrap-about pb-md-5 ftco-animate">
	          <div class="heading-section heading-section-wo-line pt-md-5 pl-md-5 mb-5">
	          	<div class="ml-md-0">
		          	<span class="subheading">Welcome to MARRON Hotel</span>
		            <h2 class="mb-4">Welcome To Our Hotel</h2>
	            </div>
	          </div>
	          <div class="pb-md-5">
							<p>On her way she met a copy. The copy warned the Little Blind Text, that where it came from it would have been rewritten a thousand times and everything that was left from its origin would be the word "and" and the Little Blind Text should turn around and return to its own, safe country. But nothing the copy said could convince her and so it didn't take long until a few insidious Copy Writers ambushed her, made her drunk with Longe and Parole and dragged her into their agency, where they abused her for their.</p>
							<p>When she reached the first hills of the Italic Mountains, she had a last view back on the skyline of her hometown Bookmarksgrove, the headline of Alphabet Village and the subline of her own road, the Line Lane. Pityful a rethoric question ran over her cheek, then she continued her way.</p>
							<ul class="ftco-social d-flex">
                <li class="ftco-animate"><a href="#"><span class="icon-twitter"></span></a></li>
                <li class="ftco-animate"><a href="#"><span class="icon-facebook"></span></a></li>
                <li class="ftco-animate"><a href="#"><span class="icon-google-plus"></span></a></li>
                <li class="ftco-animate"><a href="#"><span class="icon-instagram"></span></a></li>
              </ul>
						</div>
					</div>
				</div>
			</div>
		</section>

		<section class="ftco-section">
      <div class="container">
        <div class="row d-flex">
          <div class="col-md-3 d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services py-4 d-block text-center">
              <div class="d-flex justify-content-center">
              	<div class="icon d-flex align-items-center justify-content-center">
              		<span class="flaticon-reception-bell"></span>
              	</div>
              </div>
              <div class="media-body p-2 mt-2">
                <h3 class="heading mb-3">25/7 Front Desk</h3>
                <p>A small river named Duden flows by their place and supplies.</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services py-4 d-block text-center">
              <div class="d-flex justify-content-center">
              	<div class="icon d-flex align-items-center justify-content-center">
              		<span class="flaticon-serving-dish"></span>
              	</div>
              </div>
              <div class="media-body p-2 mt-2">
                <h3 class="heading mb-3">Restaurant Bar</h3>
                <p>A small river named Duden flows by their place and supplies.</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 d-flex align-sel Searchf-stretch ftco-animate">
            <div class="media block-6 services py-4 d-block text-center">
              <div class="d-flex justify-content-center">
              	<div class="icon d-flex align-items-center justify-content-center">
              		<span class="flaticon-car"></span>
              	</div>
              </div>
              <div class="media-body p-2 mt-2">
                <h3 class="heading mb-3">Transfer Services</h3>
                <p>A small river named Duden flows by their place and supplies.</p>
              </div>
            </div>
          </div>
          <div class="col-md-3 d-flex align-self-stretch ftco-animate">
            <div class="media block-6 services py-4 d-block text-center">
              <div class="d-flex justify-content-center">
              	<div class="icon d-flex align-items-center justify-content-center">
              		<span class="flaticon-spa"></span>
              	</div>
              </div>
              <div class="media-body p-2 mt-2">
                <h3 class="heading mb-3">Spa Suites</h3>
                <p>A small river named Duden flows by their place and supplies.</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section">
        <div class="container">
            <div class="row justify-content-center mb-5 pb-3">
                <div class="col-md-7 heading-section text-center ftco-animate">
                    <h2 class="mb-4">Phòng Nổi Bật</h2>
                </div>
            </div>
            <div class="row">
                @foreach ($roomTypes as $type)
                    <div class="col-sm col-md-6 col-lg-4 ftco-animate">
                        <div class="room position-relative">
                            <!-- Khuyến mãi badge -->
                            @php
                                $n = 1;
                                $amountContext = (float)$type->price * $n;
                                $promotionData = \App\Models\Promotion::getBestPromotionForRoomType($type->id, $amountContext);
                                $bestPromotion = $promotionData ? $promotionData['promotion'] : null;
                                $finalPrice = $promotionData ? max(0, (int)round($promotionData['final_price'] / $n)) : $type->price;
                            @endphp
                            @if($bestPromotion)
                                <div class="promotion-badge position-absolute" style="top: 10px; left: 10px; z-index: 10;">
                                    <span class="badge badge-danger px-3 py-2" style="font-size: 0.8rem; background: linear-gradient(45deg, #ff6b6b, #ee5a52);">
                                        <i class="fas fa-tag mr-1"></i>
                                        {{ $promotionData['discount_text'] }}
                                    </span>
                                </div>
                            @endif

                            <a href="{{ route('rooms-single', $type->id) }}"
                                class="img d-flex justify-content-center align-items-center"
                                style="background-image: url(client/images/room-{{ ($loop->iteration % 6) + 1 }}.jpg);">
                                <div class="icon d-flex justify-content-center align-items-center">
                                    <span class="icon-search2"></span>
                                </div>
                            </a>
                            <div class="text p-3 text-center">
                                <h3 class="mb-3">
                                    <a href="{{ route('rooms-single', $type->id) }}">
                                        {{ $type->name }}
                                    </a>
                                </h3>
                                @php
                                    $representativeRoom = $type->rooms()->first();
                                @endphp

                                <p>
                                    @if($bestPromotion)
                                        <span class="price mr-2 text-decoration-line-through text-muted" style="font-size: 0.9em;">{{ number_format($type->price) }}đ</span>
                                        <span class="price mr-2 text-danger font-weight-bold">{{ number_format($finalPrice) }}đ</span>
                                        <span class="per">mỗi đêm</span>
                                    @else
                                        <span class="price mr-2">{{ number_format($type->price) }}đ</span>
                                        <span class="per">mỗi đêm</span>
                                    @endif
                                </p>
                                <ul class="list">
                                    <li><span>Sức chứa:</span> {{ $type->capacity }} Người</li>
                                </ul>
                                <hr>
                                <p class="pt-1">
                                    <a href="{{ route('rooms-single', $type->id) }}" class="btn-custom">
                                        Chi tiết <span class="icon-long-arrow-right"></span>
                                    </a>
                                    @if ($type->status == 'available')
                                        <a href="{{ route('booking') }}" class="btn-custom ml-2">
                                            Đặt ngay <span class="icon-long-arrow-right"></span>
                                        </a>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="ftco-section ftco-counter img" id="section-counter" style="background-image: url(client/images/bg_1.jpg);">
    	<div class="container">
    		<div class="row justify-content-center">
    			<div class="col-md-10">
		    		<div class="row">
		          <div class="col-md-3 d-flex justify-content-center counter-wrap ftco-animate">
		            <div class="block-18 text-center">
		              <div class="text">
		                <strong class="number" data-number="50000">0</strong>
		                <span>Happy Guests</span>
		              </div>
		            </div>
		          </div>
		          <div class="col-md-3 d-flex justify-content-center counter-wrap ftco-animate">
		            <div class="block-18 text-center">
		              <div class="text">
		                <strong class="number" data-number="3000">0</strong>
		                <span>Rooms</span>
		              </div>
		            </div>
		          </div>
		          <div class="col-md-3 d-flex justify-content-center counter-wrap ftco-animate">
		            <div class="block-18 text-center">
		              <div class="text">
		                <strong class="number" data-number="1000">0</strong>
		                <span>Staffs</span>
		              </div>
		            </div>
		          </div>
		          <div class="col-md-3 d-flex justify-content-center counter-wrap ftco-animate">
		            <div class="block-18 text-center">
		              <div class="text">
		                <strong class="number" data-number="100">0</strong>
		                <span>Destination</span>
		              </div>
		            </div>
		          </div>
		        </div>
	        </div>
        </div>
    	</div>
    </section>

    <style>
    .promotion-badge .badge {
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        border: 2px solid #fff;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .room .position-relative {
        overflow: hidden;
    }

    .room .promotion-badge {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }
    </style>

    {{-- Khuyến mại nổi bật --}}
    @if($featuredPromotions && $featuredPromotions->count() > 0)
        <section class="ftco-section bg-light">
            <div class="container">
                <div class="row justify-content-center mb-4">
                    <div class="col-md-8 text-center">
                        <h2 class="mb-3">
                            <i class="fas fa-gift text-primary mr-2"></i>
                            Ưu đãi đặc biệt hôm nay
                        </h2>
                        <p class="text-muted">Khám phá các khuyến mại hấp dẫn cho chuyến du lịch của bạn</p>
                    </div>
                </div>
                <div class="row">
                    @foreach($featuredPromotions->take(3) as $promo)
                        <div class="col-md-4 mb-4">
                            <div class="card border-0 shadow-sm h-100 promotion-card">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="fas fa-gift fa-3x text-primary"></i>
                                    </div>
                                    <h5 class="card-title text-primary mb-3">{{ $promo->title }}</h5>
                                    <p class="card-text text-muted mb-3">{{ Str::limit($promo->description ?? '', 100) }}</p>
                                    <div class="mb-3">
                                        <span class="badge bg-success fs-6 px-3 py-2">{{ $promo->discount_text }}</span>
                                    </div>
                                    @if(!empty($promo->code))
                                        <div class="mb-3">
                                            <span class="badge bg-secondary fs-6 px-3 py-2">
                                                <i class="fas fa-tag mr-1"></i>{{ $promo->code }}
                                            </span>
                                        </div>
                                    @endif
                                    <a href="{{ route('rooms') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-arrow-right mr-1"></i>Xem phòng
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <!-- Section Giới thiệu khách sạn -->
    <section class="ftco-section bg-light">
      <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section text-center ftco-animate">
            <span class="subheading">Về Chúng Tôi</span>
            <h2 class="mb-4">Chào mừng đến với MARRON Hotel</h2>
            <p>MARRON Hotel là điểm đến lý tưởng cho kỳ nghỉ dưỡng và công tác của bạn. Chúng tôi cung cấp hệ thống phòng nghỉ hiện đại, dịch vụ chuyên nghiệp và không gian sang trọng, mang đến trải nghiệm tuyệt vời cho mọi khách hàng.</p>
            <a href="{{ route('about') }}" class="btn btn-primary mt-3">Xem thêm</a>
          </div>
        </div>
      </div>
    </section>

    <!-- Section Liên hệ khách sạn -->
    <section class="ftco-section contact-section">
      <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section text-center ftco-animate">
            <span class="subheading">Liên hệ</span>
            <h2 class="mb-4">Thông tin liên hệ</h2>
            <p>Liên hệ với chúng tôi để được hỗ trợ nhanh nhất!</p>
          </div>
        </div>
        <div class="row d-flex contact-info">
          <div class="col-md-4 d-flex ftco-animate">
            <div class="info bg-white p-4 w-100 text-center">
              <div class="icon mb-2"><span class="icon-map-marker"></span></div>
              <p><span>Địa chỉ:</span> 198 West 21th Street, Suite 721 New York NY 10016</p>
            </div>
          </div>
          <div class="col-md-4 d-flex ftco-animate">
            <div class="info bg-white p-4 w-100 text-center">
              <div class="icon mb-2"><span class="icon-phone"></span></div>
              <p><span>Điện thoại:</span> <a href="tel://1234567920">+ 1235 2355 98</a></p>
            </div>
          </div>
          <div class="col-md-4 d-flex ftco-animate">
            <div class="info bg-white p-4 w-100 text-center">
              <div class="icon mb-2"><span class="icon-envelope"></span></div>
              <p><span>Email:</span> <a href="mailto:info@yoursite.com">info@yoursite.com</a></p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="ftco-section testimony-section bg-light">
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-md-8 ftco-animate">
          	<div class="row ftco-animate">
		          <div class="col-md-12">
		            <div class="carousel-testimony owl-carousel ftco-owl">
		              @forelse($fiveStarReviews as $review)
		              <div class="item">
		                <div class="testimony-wrap py-4 pb-5">
		                  <div class="user-img mb-4" style="background-image: url(client/images/person_{{ $loop->index % 3 + 1 }}.jpg)">
		                    <span class="quote d-flex align-items-center justify-content-center">
		                      <i class="icon-quote-left"></i>
		                    </span>
		                  </div>
		                  <div class="text text-center">
		                    <p class="mb-4">{{ Str::limit($review->comment, 150) }}</p>
		                    <p class="name">{{ $review->reviewer_name }}</p>
		                    <span class="position">{{ $review->roomType->name ?? 'Khách hàng' }}</span>
		                    <div class="stars mt-2">
		                      @for($i = 1; $i <= 5; $i++)
		                        <i class="icon-star{{ $i <= $review->rating ? '' : '-o' }} text-warning"></i>
		                      @endfor
		                    </div>
		                  </div>
		                </div>
		              </div>
		              @empty
		              <!-- Fallback content if no reviews -->
		              <div class="item">
		                <div class="testimony-wrap py-4 pb-5">
		                  <div class="user-img mb-4" style="background-image: url(client/images/person_1.jpg)">
		                    <span class="quote d-flex align-items-center justify-content-center">
		                      <i class="icon-quote-left"></i>
		                    </span>
		                  </div>
		                  <div class="text text-center">
		                    <p class="mb-4">Khách sạn tuyệt vời với dịch vụ chất lượng cao và phòng ốc sạch sẽ.</p>
		                    <p class="name">Khách hàng</p>
		                    <span class="position">Khách hàng</span>
		                    <div class="stars mt-2">
		                      <i class="icon-star text-warning"></i>
		                      <i class="icon-star text-warning"></i>
		                      <i class="icon-star text-warning"></i>
		                      <i class="icon-star text-warning"></i>
		                      <i class="icon-star text-warning"></i>
		                    </div>
		                  </div>
		                </div>
		              </div>
		              @endforelse
		            </div>
		          </div>
		        </div>
          </div>
        </div>
      </div>
    </section>


    <section class="ftco-section">
      <div class="container">
        <div class="row justify-content-center mb-5 pb-3">
          <div class="col-md-7 heading-section text-center ftco-animate">
            <h2>Recent Blog</h2>
          </div>
        </div>
        <div class="row d-flex">
          <div class="col-md-3 d-flex ftco-animate">
            <div class="blog-entry align-self-stretch">
              <a href="blog-single.html" class="block-20" style="background-image: url('client/images/image_1.jpg');">
              </a>
              <div class="text mt-3 d-block">
                <h3 class="heading mt-3"><a href="#">Even the all-powerful Pointing has no control about the blind texts</a></h3>
                <div class="meta mb-3">
                  <div><a href="#">Dec 6, 2018</a></div>
                  <div><a href="#">Admin</a></div>
                  <div><a href="#" class="meta-chat"><span class="icon-chat"></span> 3</a></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3 d-flex ftco-animate">
            <div class="blog-entry align-self-stretch">
              <a href="blog-single.html" class="block-20" style="background-image: url('client/images/image_2.jpg');">
              </a>
              <div class="text mt-3">
                <h3 class="heading mt-3"><a href="#">Even the all-powerful Pointing has no control about the blind texts</a></h3>
                <div class="meta mb-3">
                  <div><a href="#">Dec 6, 2018</a></div>
                  <div><a href="#">Admin</a></div>
                  <div><a href="#" class="meta-chat"><span class="icon-chat"></span> 3</a></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3 d-flex ftco-animate">
            <div class="blog-entry align-self-stretch">
              <a href="blog-single.html" class="block-20" style="background-image: url('client/images/image_3.jpg');">
              </a>
              <div class="text mt-3">
                <h3 class="heading mt-3"><a href="#">Even the all-powerful Pointing has no control about the blind texts</a></h3>
                <div class="meta mb-3">
                  <div><a href="#">Dec 6, 2018</a></div>
                  <div><a href="#">Admin</a></div>
                  <div><a href="#" class="meta-chat"><span class="icon-chat"></span> 3</a></div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-3 d-flex ftco-animate">
            <div class="blog-entry align-self-stretch">
              <a href="blog-single.html" class="block-20" style="background-image: url('client/images/image_4.jpg');">
              </a>
              <div class="text mt-3">
                <h3 class="heading mt-3"><a href="#">Even the all-powerful Pointing has no control about the blind texts</a></h3>
                <div class="meta mb-3">
                  <div><a href="#">Dec 6, 2018</a></div>
                  <div><a href="#">Admin</a></div>
                  <div><a href="#" class="meta-chat"><span class="icon-chat"></span> 3</a></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>



    <style>
    /* Promotion styles */
    .promotion-card {
        transition: all 0.3s ease;
        border: 1px solid #e9ecef !important;
    }

    .promotion-card:hover {
        border-color: #007bff !important;
        box-shadow: 0 4px 20px rgba(0, 123, 255, 0.2) !important;
        transform: translateY(-5px);
    }

    .promotion-card .card-body {
        transition: all 0.3s ease;
    }

    .promotion-card:hover .card-body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .badge.bg-success {
        background-color: #28a745 !important;
    }

    .badge.bg-secondary {
        background-color: #6c757d !important;
    }

    .fs-6 {
        font-size: 0.875rem !important;
    }

    .btn-outline-primary:hover {
        background-color: #007bff;
        border-color: #007bff;
        color: white;
    }
    </style>
@endsection
