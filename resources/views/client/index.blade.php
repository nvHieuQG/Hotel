@extends('client.layouts.master')

@section('title', 'Home')

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
                            <div class="col-md-3 d-flex">
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
                            <div class="col-md-3 d-flex">
                                <div class="form-group d-flex align-self-stretch">
                                    <button type="submit" class="btn btn-primary py-3 px-4 align-self-stretch">Tìm kiếm phòng</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

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
                        <div class="room">
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
                                <p>
                                    <span class="price mr-2">{{ number_format($type->price) }}đ</span>
                                    <span class="per">mỗi đêm</span>
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
		              <div class="item">
		                <div class="testimony-wrap py-4 pb-5">
		                  <div class="user-img mb-4" style="background-image: url(client/images/person_1.jpg)">
		                    <span class="quote d-flex align-items-center justify-content-center">
		                      <i class="icon-quote-left"></i>
		                    </span>
		                  </div>
		                  <div class="text text-center">
		                    <p class="mb-4">A small river named Duden flows by their place and supplies it with the necessary regelialia. It is a paradisematic country, in which roasted parts of sentences fly into your mouth.</p>
		                    <p class="name">Nathan Smith</p>
		                    <span class="position">Guests</span>
		                  </div>
		                </div>
		              </div>
		              <div class="item">
		                <div class="testimony-wrap py-4 pb-5">
		                  <div class="user-img mb-4" style="background-image: url(client/images/person_2.jpg)">
		                    <span class="quote d-flex align-items-center justify-content-center">
		                      <i class="icon-quote-left"></i>
		                    </span>
		                  </div>
		                  <div class="text text-center">
		                    <p class="mb-4">A small river named Duden flows by their place and supplies it with the necessary regelialia. It is a paradisematic country, in which roasted parts of sentences fly into your mouth.</p>
		                    <p class="name">Nathan Smith</p>
		                    <span class="position">Guests</span>
		                  </div>
		                </div>
		              </div>
		              <div class="item">
		                <div class="testimony-wrap py-4 pb-5">
		                  <div class="user-img mb-4" style="background-image: url(client/images/person_3.jpg)">
		                    <span class="quote d-flex align-items-center justify-content-center">
		                      <i class="icon-quote-left"></i>
		                    </span>
		                  </div>
		                  <div class="text text-center">
		                    <p class="mb-4">A small river named Duden flows by their place and supplies it with the necessary regelialia. It is a paradisematic country, in which roasted parts of sentences fly into your mouth.</p>
		                    <p class="name">Nathan Smith</p>
		                    <span class="position">Guests</span>
		                  </div>
		                </div>
		              </div>
		              <div class="item">
		                <div class="testimony-wrap py-4 pb-5">
		                  <div class="user-img mb-4" style="background-image: url(client/images/person_1.jpg)">
		                    <span class="quote d-flex align-items-center justify-content-center">
		                      <i class="icon-quote-left"></i>
		                    </span>
		                  </div>
		                  <div class="text text-center">
		                    <p class="mb-4">A small river named Duden flows by their place and supplies it with the necessary regelialia. It is a paradisematic country, in which roasted parts of sentences fly into your mouth.</p>
		                    <p class="name">Nathan Smith</p>
		                    <span class="position">Guests</span>
		                  </div>
		                </div>
		              </div>
		              <div class="item">
		                <div class="testimony-wrap py-4 pb-5">
		                  <div class="user-img mb-4" style="background-image: url(client/images/person_1.jpg)">
		                    <span class="quote d-flex align-items-center justify-content-center">
		                      <i class="icon-quote-left"></i>
		                    </span>
		                  </div>
		                  <div class="text text-center">
		                    <p class="mb-4">A small river named Duden flows by their place and supplies it with the necessary regelialia. It is a paradisematic country, in which roasted parts of sentences fly into your mouth.</p>
		                    <p class="name">Nathan Smith</p>
		                    <span class="position">Guests</span>
		                  </div>
		                </div>
		              </div>
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

    @auth
    <!-- Chat Support Button and Chat Box (Bottom Right) -->
    <button id="openChatModal" style="position: fixed; bottom: 30px; right: 30px; z-index: 1050; background: #007bff; color: #fff; border: none; border-radius: 50%; width: 60px; height: 60px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); font-size: 28px; display: flex; align-items: center; justify-content: center;">
        <span class="icon-chat"></span>
    </button>
    <div id="chatBox" style="display: none; position: fixed; bottom: 100px; right: 30px; width: 340px; max-width: 95vw; background: #fff; border-radius: 12px; box-shadow: 0 4px 24px rgba(0,0,0,0.18); z-index: 1060; flex-direction: column; overflow: hidden;">
      <div style="background: #007bff; color: #fff; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
        <span style="font-weight: 600;">Hỗ trợ trực tuyến</span>
        <button id="closeChatBox" style="background: transparent; border: none; color: #fff; font-size: 22px; line-height: 1; cursor: pointer;">&times;</button>
      </div>
      <div id="chatMessages" style="padding: 16px; background: #f8f9fa; height: 260px; overflow-y: auto;">
        <ul style="list-style: none; padding: 0; margin: 0;">
          @php
            $ticket = Auth::user()->supportTickets()->with(['messages' => function($q){ $q->orderBy('created_at'); }])->latest()->first();
          @endphp
          @if($ticket && $ticket->messages->count())
            @foreach($ticket->messages as $msg)
              <li style="margin-bottom: 10px; text-align: {{ $msg->sender_type == 'user' ? 'right' : 'left' }};">
                <div style="font-size: 12px; color: #888; margin-bottom: 2px;">
                  @if($msg->sender_type == 'user')
                    {{ $ticket->user && $msg->sender_id == $ticket->user->id ? $ticket->user->name : 'Bạn' }}
                  @else
                    Admin
                  @endif
                </div>
                <span style="display: inline-block; background: {{ $msg->sender_type == 'user' ? '#007bff' : '#e9ecef' }}; color: {{ $msg->sender_type == 'user' ? '#fff' : '#222' }}; padding: 8px 14px; border-radius: 16px;">{{ $msg->message }}</span>
              </li>
            @endforeach
          @else
            <li style="text-align:center; color:#888;">Chưa có cuộc trò chuyện nào. Hãy gửi tin nhắn đầu tiên để tạo yêu cầu hỗ trợ!</li>
          @endif
        </ul>
      </div>
      <form id="chatForm" style="display: flex; gap: 8px; padding: 12px 16px; background: #fff; border-top: 1px solid #eee;">
        <input type="text" id="chatInput" name="message" class="form-control" placeholder="Nhập tin nhắn..." style="flex: 1;">
        <button type="submit" id="sendChatBtn" class="btn btn-primary">Gửi</button>
      </form>
      <input type="hidden" id="ticketId" value="{{ $ticket ? $ticket->id : '' }}">
    </div>
    <script>
      const openBtn = document.getElementById('openChatModal');
      const chatBox = document.getElementById('chatBox');
      const closeBtn = document.getElementById('closeChatBox');
      const chatInput = document.getElementById('chatInput');
      const chatForm = document.getElementById('chatForm');
      const chatMessages = document.querySelector('#chatMessages ul');
      const ticketIdInput = document.getElementById('ticketId');
      openBtn.onclick = function() {
        chatBox.style.display = 'flex';
        setTimeout(() => { chatInput.focus(); }, 200);
      };
      closeBtn.onclick = function() { chatBox.style.display = 'none'; };
      chatForm.onsubmit = function(e) {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (!msg) return;
        const ticketId = ticketIdInput.value;
        let url = '';
        let data = { message: msg, _token: '{{ csrf_token() }}' };
        if(ticketId) {
          url = '/support/ticket/' + ticketId + '/message';
        } else {
          url = '/support/ticket';
          data.subject = 'Chat hỗ trợ nhanh';
        }
        fetch(url, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
          body: JSON.stringify(data)
        })
        .then(res => res.redirected ? window.location.href = res.url : res.json())
        .then(res => { window.location.reload(); })
        .catch(() => { alert('Có lỗi khi gửi tin nhắn!'); });
      };
      chatInput.addEventListener('keydown', function(e) { if (e.key === 'Enter') chatForm.dispatchEvent(new Event('submit')); });
    </script>
    @endauth

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Xử lý form Check Availability (tìm kiếm phòng)
        const availabilityForm = document.getElementById('availabilityForm');
        const checkInDate = document.getElementById('check_in_date');
        const checkOutDate = document.getElementById('check_out_date');
        const guestsSelect = document.getElementById('guests');

        // Validation cho ngày check-in và check-out
        function validateDates() {
            const checkIn = new Date(checkInDate.value);
            const checkOut = new Date(checkOutDate.value);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (checkIn < today) {
                alert('Ngày check-in không thể là ngày trong quá khứ!');
                checkInDate.focus();
                return false;
            }

            if (checkOut <= checkIn) {
                alert('Ngày check-out phải sau ngày check-in!');
                checkOutDate.focus();
                return false;
            }

            return true;
        }

        // Validation cho form tìm kiếm
        availabilityForm.addEventListener('submit', function(e) {
            if (!checkInDate.value) {
                alert('Vui lòng chọn ngày check-in!');
                checkInDate.focus();
                e.preventDefault();
                return;
            }

            if (!checkOutDate.value) {
                alert('Vui lòng chọn ngày check-out!');
                checkOutDate.focus();
                e.preventDefault();
                return;
            }

            if (!validateDates()) {
                e.preventDefault();
                return;
            }

            // Form hợp lệ, cho phép submit để tìm kiếm phòng
            console.log('Tìm kiếm phòng với thông tin:', {
                check_in_date: checkInDate.value,
                check_out_date: checkOutDate.value,
                guests: guestsSelect.value
            });
        });

        // Tự động cập nhật ngày check-out khi thay đổi ngày check-in
        checkInDate.addEventListener('change', function() {
            if (this.value) {
                const checkIn = new Date(this.value);
                const nextDay = new Date(checkIn);
                nextDay.setDate(nextDay.getDate() + 1);
                checkOutDate.min = nextDay.toISOString().split('T')[0];
                
                // Nếu ngày check-out hiện tại nhỏ hơn ngày check-in + 1, cập nhật
                if (checkOutDate.value && new Date(checkOutDate.value) <= checkIn) {
                    checkOutDate.value = nextDay.toISOString().split('T')[0];
                }
            }
        });

        // Hiển thị thông báo khi thay đổi số khách
        guestsSelect.addEventListener('change', function() {
            if (this.value) {
                console.log('Số khách đã chọn:', this.value);
            }
        });
    });
    </script>
@endsection
