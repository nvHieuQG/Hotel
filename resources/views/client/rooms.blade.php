@extends('client.layouts.master')

{{-- Tiêu đề trang --}}
@section('title', 'Danh Sách Phòng')

@section('content')

    {{-- Banner đầu trang --}}
    <div class="hero-wrap" style="background-image: url('client/images/bg_1.jpg');">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
                <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                    <div class="text">
                        <p class="breadcrumbs mb-2">
                            <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                            <span>Phòng</span>
                        </p>
                        <h1 class="mb-4 bread">Danh Sách Phòng</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Danh sách phòng & Tìm kiếm --}}
    <section class="ftco-section bg-light">
        <div class="container">
            <div class="row">
                {{-- Danh sách phòng --}}
                <div class="col-lg-9">
                    <div class="row">
                        @if ($rooms->count() > 0)
                            @foreach ($rooms as $room)
                                <div class="col-sm col-md-6 col-lg-4 ftco-animate">
                                    <div class="room">
                                        <a href="{{ route('rooms-single', $room->id) }}"
                                            class="img d-flex justify-content-center align-items-center"
                                            style="background-image: url(client/images/room-{{ ($loop->iteration % 6) + 1 }}.jpg);">
                                            <div class="icon d-flex justify-content-center align-items-center">
                                                <span class="icon-search2"></span>
                                            </div>
                                        </a>
                                        <div class="text p-3 text-center">
                                            <h3 class="mb-3">
                                                <a href="{{ route('rooms-single', $room->id) }}">
                                                    {{ $room->roomType->name }}
                                                </a>
                                            </h3>
                                            <p>
                                                <span class="price mr-2">{{ number_format($room->price) }}đ</span>
                                                <span class="per">mỗi đêm</span>
                                            </p>
                                            <ul class="list">
                                                <li><span>Sức chứa:</span> {{ $room->capacity }} Người</li>
                                                <li><span>Phòng số:</span> {{ $room->room_number }}</li>
                                                <li><span>Trạng thái:</span>
                                                    {{ $room->status == 'available' ? 'Còn trống' : 'Đã đặt' }}
                                                </li>
                                            </ul>
                                            <hr>
                                            <p class="pt-1">
                                                <a href="{{ route('rooms-single', $room->id) }}" class="btn-custom">
                                                    Chi tiết <span class="icon-long-arrow-right"></span>
                                                </a>
                                                @if ($room->status == 'available')
                                                    <a href="{{ route('booking') }}" class="btn-custom ml-2">
                                                        Đặt ngay <span class="icon-long-arrow-right"></span>
                                                    </a>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-5">
                                @php
                                    $messages = [];
                                    $keyword = request()->input('keyword');
                                    $capacity = request()->input('capacity');
                                    $typeId = request()->input('type');
                                    $priceMin = request()->input('price_min');
                                    $priceMax = request()->input('price_max');

                                    if ($keyword) {
                                        $messages[] = '🔍 <span class="fw-semibold">Từ khóa</span>: "' . e($keyword) . '"';
                                    }
                                    if ($capacity) {
                                        $capacityText = $capacity == 6 ? '6 người trở lên' : $capacity . ' người';
                                        $messages[] = '👥 <span class="fw-semibold">Số người</span>: ' . $capacityText;
                                    }
                                    if ($typeId) {
                                        $roomType = \App\Models\RoomType::find($typeId);
                                        if ($roomType) {
                                            $messages[] = '🏷️ <span class="fw-semibold">Loại phòng</span>: "' . e($roomType->name) . '"';
                                        }
                                    }
                                    if ($priceMin || $priceMax) {
                                        $priceText = '💰 <span class="fw-semibold">Khoảng giá</span>: ';
                                        if ($priceMin && $priceMax) {
                                            $priceText .= number_format($priceMin) . 'đ - ' . number_format($priceMax) . 'đ';
                                        } elseif ($priceMin) {
                                            $priceText .= 'Từ ' . number_format($priceMin) . 'đ';
                                        } elseif ($priceMax) {
                                            $priceText .= 'Đến ' . number_format($priceMax) . 'đ';
                                        }
                                        $messages[] = $priceText;
                                    }
                                @endphp

                                <div class="alert bg-light border-0 shadow-sm p-4 rounded-3" style="font-family: 'Roboto', sans-serif;">
                                    <div class="text-center mb-4">
                                        <i class="fas fa-circle-exclamation text-warning fs-1 mb-3"></i>
                                        <h4 class="fw-bold mb-2" style="font-size: 1.5rem;">KHÔNG TÌM THẤY PHÒNG NÀO PHÙ HỢP</h4>
                                        <p class="mb-0 fw-semibold text-secondary" style="font-size: 1.1rem;">
                                            Dựa trên các tiêu chí bạn đã chọn:
                                        </p>
                                    </div>
                                    <ul class="list-unstyled ps-4 mb-3 text-start">
                                        @foreach ($messages as $item)
                                            <li class="mb-1">{!! $item !!}</li>
                                        @endforeach
                                    </ul>

                                    @if ($keyword === 'đơn' && $priceMin && (int) $priceMin > 500000)
                                        <div class="text-danger small mb-1">
                                            ⚠️ Giá tối thiểu {{ number_format($priceMin) }}đ cao hơn giá phòng đơn (500.000đ).
                                        </div>
                                    @endif

                                    @if ($capacity && $typeId && $roomType && $capacity > $roomType->capacity)
                                        <div class="text-danger small mb-2">
                                            ⚠️ Loại phòng <strong>{{ $roomType->name }}</strong> chỉ chứa tối đa {{ $roomType->capacity }} người.
                                        </div>
                                    @endif

                                    <div class="text-end">
                                        <a href="{{ route('rooms.search') }}" class="btn btn-outline-primary btn-sm">
                                            Thử lại với tiêu chí khác
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Sidebar tìm kiếm --}}
                <div class="col-lg-3 sidebar">
                    <div class="sidebar-wrap bg-light ftco-animate">
                        <h3 class="heading mb-4">Tìm phòng</h3>
                        <form action="{{ route('rooms.search') }}" method="GET">
                            <div class="fields">
                                <div class="form-group">
                                    <input type="text" name="keyword" class="form-control" placeholder="Từ khóa"
                                        value="{{ request()->input('keyword') }}">
                                </div>

                                <div class="form-group">
                                    <div class="select-wrap one-third">
                                        <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                        <select name="capacity" class="form-control">
                                            <option value="">Số người</option>
                                            @for ($i = 1; $i <= 6; $i++)
                                                <option value="{{ $i }}" {{ request()->input('capacity') == $i ? 'selected' : '' }}>
                                                    {{ $i }} {{ $i < 6 ? 'người' : 'người trở lên' }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="select-wrap one-third">
                                        <div class="icon"><span class="ion-ios-arrow-down"></span></div>
                                        <select name="type" class="form-control">
                                            <option value="">Loại phòng</option>
                                            @foreach (\App\Models\RoomType::all() as $type)
                                                <option value="{{ $type->id }}" {{ request()->input('type') == $type->id ? 'selected' : '' }}>
                                                    {{ $type->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <label for="price_min" class="form-label fw-semibold">Khoảng giá (VNĐ)</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="number" name="price_min" id="price_min" class="form-control"
                                            placeholder="Từ" value="{{ request()->input('price_min', 0) }}" min="0" max="5000000">
                                        <span class="mx-1">–</span>
                                        <input type="number" name="price_max" id="price_max" class="form-control"
                                            placeholder="Đến" value="{{ request()->input('price_max', 5000000) }}" min="0" max="5000000">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <input type="submit" value="Tìm kiếm" class="btn btn-primary py-3 px-5">
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                {{-- End sidebar --}}
            </div>
        </div>
    </section>
@endsection
