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
                            <h2 class="mb-4">{{ $room->roomType->name }} - {{ $room->room_number }}</h2>
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
                                    <li><span>Phòng số:</span> {{ $room->room_number }}</li>
                                </ul>
                                <ul class="list ml-md-5">
                                    <li><span>Loại phòng:</span> {{ $room->roomType->name }}</li>
                                    <li><span>Trạng thái:</span>
                                        {{ $room->status == 'available' ? 'Còn trống' : 'Đã đặt' }}</li>
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
                                        <a href="{{ route('reviews.create-room', $room->id) }}" class="btn btn-outline-primary">
                                            <i class="icon-pencil"></i> Viết đánh giá
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-outline-primary">
                                            <i class="icon-user"></i> Đăng nhập để đánh giá
                                        </a>
                                    @endauth
                                </div>
                            </div>

                            <!-- Danh sách đánh giá -->
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
                                                    <p class="mb-0">{{ $review->comment }}</p>
                                                @else
                                                    <p class="mb-0 text-muted"><em>Không có bình luận</em></p>
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
                                        <a href="{{ route('reviews.create-room', $room->id) }}" class="btn btn-primary">
                                            Viết đánh giá đầu tiên
                                        </a>
                                    @else
                                        <a href="{{ route('login') }}" class="btn btn-primary">
                                            Đăng nhập để đánh giá
                                        </a>
                                    @endauth
                                </div>
                            @endif
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
                                                        href="{{ route('rooms-single', $relRoom->id) }}">{{ $relRoom->roomType->name }}
                                                        - {{ $relRoom->room_number }}</a></h3>
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
                            @foreach (\App\Models\RoomType::all() as $type)
                                <li>
                                    <a href="{{ route('rooms.search', ['type' => $type->id]) }}">
                                        {{ $type->name }} <span>({{ $type->rooms->count() }})</span>
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
