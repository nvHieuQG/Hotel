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
                                <p><span class="price mr-2">{{ number_format($room->price) }}đ</span> <span class="per">mỗi đêm</span></p>
                            </div>
                            <p class="mb-4">{{ $room->roomType->description }}</p>
                            <div class="d-md-flex mt-5 mb-5">
                                <ul class="list">
                                    <li><span>Sức chứa tối đa:</span> {{ $room->capacity }} người</li>
                                    <li><span>Phòng số:</span> {{ $room->room_number }}</li>
                                </ul>
                                <ul class="list ml-md-5">
                                    <li><span>Loại phòng:</span> {{ $room->roomType->name }}</li>
                                    <li><span>Trạng thái:</span> {{ $room->status == 'available' ? 'Còn trống' : 'Đã đặt' }}</li>
                                </ul>
                            </div>
                            @if($room->status == 'available')
                            <div class="text-center">
                                <a href="{{ route('booking') }}" class="btn btn-primary py-3 px-5">Đặt phòng ngay</a>
                            </div>
                            @endif
                        </div>

                        <div class="col-md-12 room-single ftco-animate mb-5 mt-5">
                            <h4 class="mb-4">Các phòng khác cùng loại</h4>
                            <div class="row">
                                @foreach($relatedRooms as $relRoom)
                                <div class="col-sm col-md-6 ftco-animate">
                                    <div class="room">
                                        <a href="{{ route('rooms-single', $relRoom->id) }}" class="img img-2 d-flex justify-content-center align-items-center" style="background-image: url('/client/images/room-{{ $loop->iteration + 3 }}.jpg');">
                                            <div class="icon d-flex justify-content-center align-items-center">
                                                <span class="icon-search2"></span>
                                            </div>
                                        </a>
                                        <div class="text p-3 text-center">
                                            <h3 class="mb-3"><a href="{{ route('rooms-single', $relRoom->id) }}">{{ $relRoom->roomType->name }} - {{ $relRoom->room_number }}</a></h3>
                                            <p><span class="price mr-2">{{ number_format($relRoom->price) }}đ</span> <span class="per">mỗi đêm</span></p>
                                            <hr>
                                            <p class="pt-1"><a href="{{ route('rooms-single', $relRoom->id) }}" class="btn-custom">Xem chi tiết <span class="icon-long-arrow-right"></span></a></p>
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
                        <form action="{{ route('rooms') }}" method="GET" class="search-form">
                            <div class="form-group">
                                <span class="icon fa fa-search"></span>
                                <input type="text" name="keyword" class="form-control" placeholder="Tìm phòng khác">
                            </div>
                        </form>
                    </div>
                    <div class="sidebar-box ftco-animate">
                        <div class="categories">
                            <h3>Loại phòng</h3>
                            @foreach(\App\Models\RoomType::all() as $type)
                            <li><a href="{{ route('rooms', ['type' => $type->id]) }}">{{ $type->name }} <span>({{ $type->rooms->count() }})</span></a></li>
                            @endforeach
                        </div>
                    </div>

                    <div class="sidebar-box ftco-animate">
                        <h3>Đặt phòng nhanh</h3>
                        <form action="{{ route('booking') }}" method="GET" class="p-3 bg-light">
                            <input type="hidden" name="room_id" value="{{ $room->id }}">
                            <div class="form-group">
                                <label for="checkin_date">Ngày nhận phòng</label>
                                <input type="date" name="check_in" class="form-control" required min="{{ date('Y-m-d') }}">
                            </div>
                            <div class="form-group">
                                <label for="checkout_date">Ngày trả phòng</label>
                                <input type="date" name="check_out" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            </div>
                            <div class="form-group">
                                <label for="guests">Số khách</label>
                                <select name="guests" class="form-control">
                                    @for($i = 1; $i <= $room->capacity; $i++)
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