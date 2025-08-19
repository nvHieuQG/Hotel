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
                {{-- Khuyến mại nổi bật --}}
                @if($featuredPromotions && $featuredPromotions->count() > 0)
                    <div class="col-12 mb-4">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-gradient-primary text-white">
                                <i class="fas fa-gift mr-2"></i> 
                                <strong>Ưu đãi đặc biệt hôm nay</strong>
                            </div>
                            <div class="card-body p-3">
                                <div class="row">
                                    @foreach($featuredPromotions->take(3) as $promo)
                                        <div class="col-md-4 mb-3">
                                            <div class="promotion-item p-3 border rounded bg-light h-100">
                                                <div class="d-flex flex-column h-100">
                                                    <div class="flex-grow-1">
                                                        <div class="fw-bold text-primary mb-2">{{ $promo->title }}</div>
                                                        <div class="small text-muted mb-2">{{ Str::limit($promo->description ?? '', 80) }}</div>
                                                    </div>
                                                    <div class="mt-auto">
                                                        <div class="badge bg-success mb-2">{{ $promo->discount_text }}</div>
                                                        @if(!empty($promo->code))
                                                            <div class="badge bg-secondary small">{{ $promo->code }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

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

@section('styles')
<style>
/* Promotion styles */
.promotion-section { margin-bottom: 2rem; }
.promotion-item { transition: all 0.3s ease; border: 1px solid #e9ecef !important; }
.promotion-item:hover { border-color: #007bff !important; box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15); transform: translateY(-1px); }
.bg-gradient-primary { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); }
.bg-gradient-success { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); }
.cursor-pointer { cursor: pointer; }
.cursor-pointer:hover { color: #0056b3 !important; }

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
</style>
@endsection
