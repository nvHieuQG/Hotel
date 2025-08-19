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
            {{-- Thông báo tìm kiếm --}}
            @if($searchMessage)
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <i class="fas fa-search"></i> {{ $searchMessage }}
                            <a href="{{ route('rooms') }}" class="float-right text-decoration-none">
                                <i class="fas fa-times"></i> Xóa bộ lọc
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <div class="row">
                {{-- Danh sách phòng được tìm thấy --}}
                <div class="col-lg-9">
                    <div class="row">
                        @if ($roomTypes->count() > 0)
                            {{-- Duyệt qua từng loại phòng nếu có kết quả --}}
                            @foreach ($roomTypes as $type)
                                <div class="col-sm col-md-6 col-lg-4 ftco-animate">
                                    <div class="room">
                                        {{-- Đường dẫn và hình ảnh loại phòng --}}
                                        <a href="{{ route('rooms-single', $type->id) }}"
                                            class="img d-flex justify-content-center align-items-center"
                                            style="background-image: url(
                                                @php
                                                    $representativeRoom = $type->rooms()->first();
                                                    $roomImage = null;
                                                    if ($representativeRoom) {
                                                        if ($representativeRoom->primaryImage) {
                                                            $roomImage = asset('storage/' . $representativeRoom->primaryImage->image_url);
                                                        } elseif ($representativeRoom->firstImage) {
                                                            $roomImage = asset('storage/' . $representativeRoom->firstImage->image_url);
                                                        }
                                                    }
                                                    echo $roomImage ?: 'client/images/room-' . (($loop->iteration % 6) + 1) . '.jpg';
                                                @endphp
                                            );">
                                            <div class="icon d-flex justify-content-center align-items-center">
                                                <span class="icon-search2"></span>
                                            </div>
                                        </a>
                                        <div class="text p-2 text-center">
                                            <h3 class="mb-3">
                                                <a href="{{ route('rooms-single', $type->id) }}">
                                                    {{ $type->name }}
                                                </a>
                                            </h3>
                                            @php
                                                $representativeRoom = $type->rooms()->where('status', 'available')->first();
                                                $promotions = collect();
                                                if ($representativeRoom) {
                                                    // Tính số đêm từ bộ lọc nếu có
                                                    $nights = 1;
                                                    try {
                                                        $ci = request()->get('check_in_date');
                                                        $co = request()->get('check_out_date');
                                                        if ($ci && $co) {
                                                            $nights = max(1, \Carbon\Carbon::parse($ci)->diffInDays(\Carbon\Carbon::parse($co)));
                                                        }
                                                    } catch (\Exception $e) {}
                                                    $amountContext = (float)($type->price ?? 0) * $nights;
                                                    $promotions = app(\App\Services\RoomPromotionService::class)->getTopPromotions($representativeRoom, $amountContext, 3);
                                                }
                                            @endphp
                                @if($promotions && $promotions->count() > 0)
                                                @php
                                                    $topList = $promotions->take(3);
                                                    $extraCount = max(0, $promotions->count() - $topList->count());
                                                @endphp
                                                @if($loop->first)
                                                    <style>
                                                        .promo-box{background:#F9F5EF;border-radius:8px;padding:12px;text-align:left;margin-top:8px;margin-bottom:8px}
                                                        .promo-title{font-weight:700;font-size:15px;margin-bottom:6px;color:#8E713D;display:flex;align-items:center;gap:6px}
                                                        .promo-list{list-style:none;margin:0;padding:0}
                                                        .promo-item{display:flex;align-items:center;gap:8px;margin:5px 0;line-height:1.2}
                                                        .promo-code-badge{background:#8E713D;color:#fff;border-radius:5px;font-weight:700;font-size:12px;padding:2px 6px;white-space:nowrap}
                                                        .promo-desc{font-size:13px;color:#333;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
                                                        .promo-extra{font-size:12px;color:#555;margin-top:6px}
                                                    </style>
                                                @endif
                                                <div class="promo-box">
                                                    <div class="promo-title">🎯 Ưu đãi hôm nay</div>
                                                    <ul class="promo-list">
                                                        @foreach($topList as $promo)
                                                            @php
                                                                $icon = '💸';
                                                                if (strtolower($promo->discount_type ?? '') === 'percentage') { $icon = '🎁'; }
                                                                if (Str::contains(strtolower($promo->title), ['flash','sale','hot'])) { $icon = '⏳'; }
                                                            @endphp
                                                            <li class="promo-item" title="{{ $promo->discount_text }}">
                                                                <span class="promo-code-badge">{{ $promo->code }}</span>
                                                                <span class="promo-desc">{{ $icon }} {{ $promo->title }}</span>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                    @if($extraCount > 0)
                                                        <div class="promo-extra">+{{ $extraCount }} ưu đãi khác</div>
                                                    @endif
                                                </div>
                                            @endif
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
                                                @if($searchParams)
                                                    <a href="{{ route('booking') }}?{{ http_build_query(array_merge($searchParams, ['room_type_id' => $type->id])) }}"
                                                        class="btn-custom ml-2">
                                                        Đặt ngay <span class="icon-long-arrow-right"></span>
                                                    </a>
                                                @else
                                                    <a href="{{ route('booking') }}?room_type_id={{ $type->id }}"
                                                        class="btn-custom ml-2">
                                                        Đặt ngay <span class="icon-long-arrow-right"></span>
                                                    </a>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            {{-- THÔNG BÁO KHI KHÔNG TÌM THẤY PHÒNG --}}
                            <div class="col-12 text-center py-5">
                                @php
                                    $keyword = request()->input('keyword');
                                    $priceMin = request()->input('price_min');
                                    $priceMax = request()->input('price_max');

                                    $hasKeyword = !empty($keyword);
                                    $hasPriceMin = is_numeric($priceMin);
                                    $hasPriceMax = is_numeric($priceMax);
                                @endphp

                                <div class="alert bg-white border shadow-sm p-5 rounded-4">
                                    <div class="text-center mb-4">
                                        <i class="bi bi-search text-primary" style="font-size: 3rem;"></i>
                                        <h4 class="fw-bold text-dark mt-3">KHÔNG TÌM THẤY PHÒNG PHÙ HỢP</h4>
                                    </div>

                                    @if ($hasKeyword || $hasPriceMin || $hasPriceMax)
                                        <div class="mb-4">
                                            <div class="d-flex flex-wrap justify-content-center gap-3">

                                                @if ($hasKeyword)
                                                    <div
                                                        class="d-flex align-items-center bg-light border px-3 py-2 rounded shadow-sm">
                                                        <i class="bi bi-search me-2 text-primary"></i>
                                                        <span class="text-dark small">Từ khóa:
                                                            <strong>"{{ $keyword }}"</strong></span>
                                                    </div>
                                                @endif

                                                @if ($hasPriceMin || $hasPriceMax)
                                                    <div
                                                        class="d-flex align-items-center bg-light border px-3 py-2 rounded shadow-sm">
                                                        <i class="bi bi-currency-dollar me-2 text-warning"></i>
                                                        <span class="text-dark small">
                                                            Giá :
                                                            @if ($hasPriceMin)
                                                                từ <strong>{{ number_format((int) $priceMin) }}đ</strong>
                                                            @endif
                                                            @if ($hasPriceMin && $hasPriceMax)
                                                                &nbsp;–&nbsp;
                                                            @endif
                                                            @if ($hasPriceMax)
                                                                đến <strong>{{ number_format((int) $priceMax) }}đ</strong>
                                                            @endif
                                                        </span>
                                                    </div>
                                                @endif

                                            </div>
                                        </div>
                                    @endif

                                    <p class="text-secondary mt-3">Vui lòng thử lại với bộ lọc khác.</p>

                                    <div class="text-center mt-4">
                                        <a href="{{ route('rooms.search') }}"
                                            class="btn btn-outline-secondary btn-sm py-2 px-4 rounded-pill">
                                            <i class="fas fa-undo-alt me-2"></i> Đặt lại bộ lọc
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
                                        <select name="type" class="form-control">
                                            <option value="">Loại phòng</option>
                                            @foreach (\App\Models\RoomType::all() as $type)
                                                <option value="{{ $type->id }}"
                                                    {{ request()->input('type') == $type->id ? 'selected' : '' }}>
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
                                            placeholder="Từ" value="{{ request()->input('price_min') }}" min="0"
                                            max="5000000">
                                        <span class="mx-1">–</span>
                                        <input type="number" name="price_max" id="price_max" class="form-control"
                                            placeholder="Đến" value="{{ request()->input('price_max', 5000000) }}"
                                            min="0" max="5000000">
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
