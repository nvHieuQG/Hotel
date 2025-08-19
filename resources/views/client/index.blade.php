@extends('client.layouts.master')

@section('title', 'Home')

@section('styles')
<style>
/* Modern Chat Widget Styles */
.chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    font-family: 'Roboto', sans-serif;
}

.chat-button {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #1E88E5 0%, #1976D2 100%);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(30, 136, 229, 0.3);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.chat-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(30, 136, 229, 0.4);
}

.chat-button .notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #F44336;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    display: none;
}

.chat-box {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 350px;
    height: 500px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    display: none;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #E0E0E0;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.chat-header {
    background: linear-gradient(135deg, #1E88E5 0%, #1976D2 100%);
    color: white;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 16px 16px 0 0;
}

.chat-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-logo {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.chat-header-info h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.chat-status {
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    opacity: 0.9;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4CAF50;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.close-chat {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background 0.2s;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-chat:hover {
    background: rgba(255, 255, 255, 0.2);
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #F8F9FA;
    scroll-behavior: smooth;
}

.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.message {
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
    animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message.sent {
    align-items: flex-end;
}

.message.received {
    align-items: flex-start;
}

.message-bubble {
    max-width: 85%;
    padding: 12px 16px;
    border-radius: 20px;
    font-size: 14px;
    line-height: 1.4;
    word-wrap: break-word;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.message.sent .message-bubble {
    background: linear-gradient(135deg, #1E88E5 0%, #1976D2 100%);
    color: white;
    border-bottom-right-radius: 6px;
}

.message.received .message-bubble {
    background: white;
    color: #333;
    border: 1px solid #E0E0E0;
    border-bottom-left-radius: 6px;
}

.message-time {
    font-size: 11px;
    color: #9E9E9E;
    margin-top: 6px;
    text-align: center;
}

.welcome-message {
    text-align: center;
    padding: 20px;
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

.welcome-message .welcome-icon {
    font-size: 48px;
    color: #1E88E5;
    margin-bottom: 12px;
    opacity: 0.7;
}

.chat-input-container {
    padding: 20px;
    border-top: 1px solid #E0E0E0;
    background: white;
    border-radius: 0 0 16px 16px;
}

.chat-input-wrapper {
    display: flex;
    align-items: flex-end;
    gap: 12px;
    background: #F5F5F5;
    border-radius: 24px;
    padding: 10px 16px;
    border: 1px solid #E0E0E0;
    transition: border-color 0.2s;
}

.chat-input-wrapper:focus-within {
    border-color: #1E88E5;
    box-shadow: 0 0 0 3px rgba(30, 136, 229, 0.1);
}

.chat-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 14px;
    resize: none;
    max-height: 100px;
    min-height: 20px;
    padding: 6px 0;
    line-height: 1.4;
}

.chat-input::placeholder {
    color: #9E9E9E;
}

.chat-attachments {
    display: flex;
    gap: 8px;
}

.attachment-btn {
    width: 28px;
    height: 28px;
    border: none;
    border-radius: 50%;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
    color: #9E9E9E;
    font-size: 14px;
}

.attachment-btn:hover {
    background: rgba(0, 0, 0, 0.1);
    color: #1E88E5;
}

.send-btn {
    width: 36px;
    height: 36px;
    border: none;
    border-radius: 50%;
    background: #1E88E5;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 16px;
    box-shadow: 0 2px 8px rgba(30, 136, 229, 0.3);
}

.send-btn:hover {
    background: #1976D2;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(30, 136, 229, 0.4);
}

.send-btn:disabled {
    background: #9E9E9E;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Admin-style notification for chat errors */
.chat-error {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    color: #c53030;
    padding: 16px 20px;
    border-radius: 12px;
    font-size: 13px;
    margin: 12px 0;
    border: 1px solid #feb2b2;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 12px rgba(197, 48, 48, 0.1);
    position: relative;
    overflow: hidden;
}

.chat-error::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
}

.chat-error i {
    font-size: 18px;
    color: #e53e3e;
    flex-shrink: 0;
}

.chat-error span {
    flex: 1;
    line-height: 1.4;
    font-weight: 500;
}

/* Success notification style */
.chat-success {
    background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
    color: #22543d;
    padding: 16px 20px;
    border-radius: 12px;
    font-size: 13px;
    margin: 12px 0;
    border: 1px solid #9ae6b4;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 12px rgba(34, 84, 61, 0.1);
    position: relative;
    overflow: hidden;
}

.chat-success::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
}

.chat-success i {
    font-size: 18px;
    color: #38a169;
    flex-shrink: 0;
}

.chat-success span {
    flex: 1;
    line-height: 1.4;
    font-weight: 500;
}

/* Info notification style */
.chat-info {
    background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
    color: #2a4365;
    padding: 16px 20px;
    border-radius: 12px;
    font-size: 13px;
    margin: 12px 0;
    border: 1px solid #90cdf4;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 12px rgba(42, 67, 101, 0.1);
    position: relative;
    overflow: hidden;
}

.chat-info::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
}

.chat-info i {
    font-size: 18px;
    color: #3182ce;
    flex-shrink: 0;
}

.chat-info span {
    flex: 1;
    line-height: 1.4;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 480px) {
    .chat-box {
        width: calc(100vw - 40px);
        right: 20px;
        left: 20px;
        height: 60vh;
    }

    .chat-button {
        width: 56px;
        height: 56px;
        font-size: 22px;
    }
}
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
                                @php
                                    $representativeRoom = $type->rooms()->first();
                                @endphp
                                
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

    @auth
    <!-- Modern Chat Widget -->
    <div class="chat-widget">
        <button id="openChatModal" class="chat-button">
            <i class="fas fa-comments"></i>
            <span class="notification-badge">2</span>
        </button>

        <div id="chatBox" class="chat-box">
            <div class="chat-header">
                <div class="chat-header-left">
                    <div class="chat-logo">M</div>
                    <div class="chat-header-info">
                        <h3>Hỗ trợ khách hàng</h3>
                        <div class="chat-status">
                            <div class="status-indicator"></div>
                            <span>Online</span>
                        </div>
                    </div>
                </div>
                <button id="closeChatBox" class="close-chat">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div id="chatMessages" class="chat-messages">
                @php
                    // Lấy conversation của user hiện tại thông qua SupportService
                    $currentUserId = Auth::id();
                    $supportService = app(\App\Services\SupportService::class);
                    $latestMessage = $supportService->getUserConversation($currentUserId);
                    
                    if ($latestMessage) {
                        $conversationId = $latestMessage->conversation_id;
                        // Lấy tất cả tin nhắn trong conversation này
                        $messages = $supportService->getUserConversationMessages($currentUserId);
                        
                        // Debug info
                        // echo "<!-- Debug: User ID: $currentUserId, Conversation ID: $conversationId, Messages count: " . $messages->count() . " -->";
                    } else {
                        $conversationId = null;
                        $messages = collect();
                        // echo "<!-- Debug: User ID: $currentUserId, No existing conversation -->";
                    }
                @endphp
                @if($messages && $messages->count() > 0)
                    @foreach($messages as $msg)
                        @if(!empty(trim($msg->message)))
                            <div class="message {{ $msg->sender_type == 'user' ? 'sent' : 'received' }}" data-message-id="{{ $msg->id }}">
                                <div class="message-bubble">{{ $msg->message }}</div>
                                <div class="message-time">{{ $msg->created_at->format('H:i') }}</div>
                            </div>
                        @endif
                    @endforeach
                @else
                    <div class="welcome-message">
                        <div class="welcome-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <p>Xin chào! Chúng tôi có thể giúp gì cho bạn?</p>
                        <p style="font-size: 12px; opacity: 0.7;">Hãy gửi tin nhắn để bắt đầu cuộc trò chuyện</p>
                    </div>
                @endif
            </div>

            <div class="chat-input-container">
                <form id="chatForm">
                    @csrf
                    <div class="chat-input-wrapper">
                        <textarea id="chatInput" name="message" class="chat-input" placeholder="Nhập tin nhắn..." required></textarea>
                        <div class="chat-attachments">
                            <button type="button" class="attachment-btn" title="Đính kèm ảnh">
                                <i class="fas fa-image"></i>
                            </button>
                            <button type="button" class="attachment-btn" title="Đính kèm file">
                                <i class="fas fa-paperclip"></i>
                            </button>
                        </div>
                        <button type="submit" id="sendChatBtn" class="send-btn" title="Gửi tin nhắn">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

                                <input type="hidden" id="conversationIdInput" value="{{ $conversationId ?? '' }}">
    </div>
    <script>
      const openBtn = document.getElementById('openChatModal');
      const chatBox = document.getElementById('chatBox');
      const closeBtn = document.getElementById('closeChatBox');
      const chatInput = document.getElementById('chatInput');
      const chatForm = document.getElementById('chatForm');
      const chatMessages = document.querySelector('#chatMessages');
      const conversationIdInput = document.getElementById('conversationIdInput');

      // Debug info
      console.log('Chat initialized for user:', {{ Auth::id() }});
      console.log('Conversation ID:', conversationIdInput.value);

      // Biến để lưu trạng thái realtime
      let isRealtimeEnabled = false;
      let lastMessageId = 0;
      let isSending = false;

      // Khởi tạo lastMessageId từ tin nhắn cuối cùng
      const lastMessage = chatMessages.querySelector('.message[data-message-id]:last-child');
      if (lastMessage) {
        lastMessageId = parseInt(lastMessage.getAttribute('data-message-id'));
        console.log('Last message ID:', lastMessageId);
      }

      openBtn.onclick = function() {
        chatBox.style.display = 'flex';
        setTimeout(() => { chatInput.focus(); }, 200);
        
        // Bắt đầu realtime nếu đã có conversation
        const conversationId = conversationIdInput.value;
        if(conversationId) {
          console.log('Starting realtime for conversation:', conversationId);
          startRealtimeChat();
          showChatInfo('Đã kết nối với cuộc trò chuyện!');
        } else {
          showChatInfo('Chào mừng! Hãy gửi tin nhắn để bắt đầu.');
        }
      };

      closeBtn.onclick = function() {
        chatBox.style.display = 'none';
        stopRealtimeChat();
      };

      // Hàm gửi tin nhắn
      function sendMessage(message) {
        if(isSending) return;

        isSending = true;
        const sendBtn = document.getElementById('sendChatBtn');
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

        const conversationId = conversationIdInput.value;
        let url = '';
        let data = { message: message, _token: '{{ csrf_token() }}' };

        if(conversationId) {
          url = '/support/conversation/' + conversationId + '/message';
          console.log('Sending message to existing conversation:', conversationId);
        } else {
          url = '/support/message';
          console.log('Creating new conversation for user:', {{ Auth::id() }});
        }

        fetch(url, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          },
          body: JSON.stringify(data)
        })
        .then(res => {
          if (!res.ok) {
            throw new Error(`HTTP error! status: ${res.status}`);
          }
          return res.json();
        })
        .then(data => {
          console.log('Send message response:', data);
          if(data.success) {
            // Cập nhật conversation ID nếu là tin nhắn đầu tiên
            if(!conversationId && data.conversation_id) {
              conversationIdInput.value = data.conversation_id;
              console.log('New conversation created:', data.conversation_id);
              showChatSuccess('Cuộc trò chuyện đã được tạo!');
            }
            
            // Thêm tin nhắn vào UI
            addMessageToUI(message, 'user', data.message_id);
            chatInput.value = '';
            chatInput.style.height = 'auto';
            
            // Bắt đầu realtime sau khi gửi tin nhắn đầu tiên
            if(!isRealtimeEnabled) {
              startRealtimeChat();
              showChatInfo('Đã bật chế độ realtime!');
            }
          } else {
            showChatError(data.message || 'Có lỗi khi gửi tin nhắn!');
          }
        })
        .catch((error) => {
          console.error('Chat error:', error);
          showChatError('Kết nối mạng có vấn đề. Vui lòng thử lại sau!');
        })
        .finally(() => {
          isSending = false;
          sendBtn.disabled = false;
          sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        });
      }

      // Hàm hiển thị lỗi chat
      function showChatError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'chat-error';
        errorDiv.innerHTML = `
          <i class="fas fa-exclamation-triangle"></i>
          <span>${message}</span>
        `;

        chatMessages.insertBefore(errorDiv, chatMessages.firstChild);

        setTimeout(() => {
          if (errorDiv.parentNode) {
            errorDiv.remove();
          }
        }, 5000);
      }

      // Hàm hiển thị thông báo thành công
      function showChatSuccess(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'chat-success';
        successDiv.innerHTML = `
          <i class="fas fa-check-circle"></i>
          <span>${message}</span>
        `;

        chatMessages.insertBefore(successDiv, chatMessages.firstChild);

        setTimeout(() => {
          if (successDiv.parentNode) {
            successDiv.remove();
          }
        }, 3000);
      }

      // Hàm hiển thị thông báo thông tin
      function showChatInfo(message) {
        const infoDiv = document.createElement('div');
        infoDiv.className = 'chat-info';
        infoDiv.innerHTML = `
          <i class="fas fa-info-circle"></i>
          <span>${message}</span>
        `;

        chatMessages.insertBefore(infoDiv, chatMessages.firstChild);

        setTimeout(() => {
          if (infoDiv.parentNode) {
            infoDiv.remove();
          }
        }, 4000);
      }

      // Auto-resize textarea
      chatInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
      });

      // Hàm thêm tin nhắn vào UI
      function addMessageToUI(message, senderType, messageId = null) {
        if(!message || message.trim() === '') return;

        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${senderType === 'user' ? 'sent' : 'received'}`;
        
        if(messageId) {
          messageDiv.setAttribute('data-message-id', messageId);
        }

        const messageBubble = document.createElement('div');
        messageBubble.className = 'message-bubble';
        messageBubble.textContent = message.trim();

        const messageTime = document.createElement('div');
        messageTime.className = 'message-time';
        messageTime.textContent = new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});

        messageDiv.appendChild(messageBubble);
        messageDiv.appendChild(messageTime);
        chatMessages.appendChild(messageDiv);

        // Scroll xuống tin nhắn mới nhất
        chatMessages.scrollTop = chatMessages.scrollHeight;
      }

      // Hàm bắt đầu realtime chat
      function startRealtimeChat() {
        if(isRealtimeEnabled) return;

        isRealtimeEnabled = true;
        console.log('Starting realtime chat...');
        
        // Cập nhật lastMessageId nếu chưa có
        if(lastMessageId === 0) {
          const lastMessage = chatMessages.querySelector('.message[data-message-id]:last-child');
          if(lastMessage) {
            lastMessageId = parseInt(lastMessage.getAttribute('data-message-id'));
            console.log('Updated last message ID:', lastMessageId);
          }
        }

        // Bắt đầu polling
        checkNewMessages();
      }

      // Hàm dừng realtime chat
      function stopRealtimeChat() {
        if(window.realtimeInterval) {
          clearInterval(window.realtimeInterval);
          window.realtimeInterval = null;
        }
        isRealtimeEnabled = false;
        console.log('Stopped realtime chat');
      }

      // Hàm kiểm tra tin nhắn mới
      function checkNewMessages() {
        if(!isRealtimeEnabled) return;

        const conversationId = conversationIdInput.value;
        if(!conversationId) return;

        console.log('Checking new messages for conversation:', conversationId, 'last ID:', lastMessageId);

        fetch(`/support/conversation/${conversationId}/messages?last_id=${lastMessageId}`, {
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
          }
        })
        .then(res => res.json())
        .then(data => {
          if(data.success && data.messages && data.messages.length > 0) {
            console.log('Received new messages:', data.messages.length);
            
            data.messages.forEach(msg => {
              // Kiểm tra xem tin nhắn đã tồn tại chưa để tránh duplicate
              const existingMessage = chatMessages.querySelector(`[data-message-id="${msg.id}"]`);
              if(!existingMessage && msg.id > lastMessageId) {
                addMessageToUI(msg.message, msg.sender_type, msg.id);
                lastMessageId = Math.max(lastMessageId, msg.id);
                
                // Thông báo khi nhận tin nhắn từ admin
                if(msg.sender_type === 'admin') {
                  showChatSuccess('Có tin nhắn mới từ hỗ trợ viên!');
                }
              }
            });
          }
        })
        .catch(err => {
          console.error('Error checking new messages:', err);
        })
        .finally(() => {
          // Tiếp tục polling nếu realtime vẫn được bật
          if(isRealtimeEnabled) {
            setTimeout(checkNewMessages, 3000);
          }
        });
      }

      // Form submit handler
      chatForm.onsubmit = function(e) {
        e.preventDefault();
        const msg = chatInput.value.trim();
        if (!msg || isSending) return;
        sendMessage(msg);
      };

      // Enter key handler
      chatInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) {
          e.preventDefault();
          const msg = this.value.trim();
          if (!msg || isSending) return;
          sendMessage(msg);
        }
      });

      // Xử lý đính kèm file
      document.querySelectorAll('.attachment-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          showChatInfo('Tính năng đính kèm file đang được phát triển!');
        });
      });

      // Dừng realtime khi tab không active
      document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
          stopRealtimeChat();
        } else {
          const conversationId = conversationIdInput.value;
          if(conversationId && chatBox.style.display === 'flex') {
            startRealtimeChat();
          }
        }
      });
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
