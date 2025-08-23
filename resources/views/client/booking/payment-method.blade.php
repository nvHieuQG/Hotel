@extends('client.layouts.master')

@section('title', 'Phương Thức Thanh Toán')

@section('content')
    <div class="hero-wrap"
        style="background-image: url('{{ asset('client/images/bg_1.jpg') }}'); font-family: 'Segoe UI', sans-serif;">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text d-flex align-items-end justify-content-center">
                <div class="col-md-9 text-center d-flex align-items-end justify-content-center">
                    <div class="text">
                        <p class="breadcrumbs mb-2">
                            <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                            <span class="mr-2"><a href="{{ route('booking.confirm') }}">Xác nhận đặt phòng</a></span>
                            <span>Thanh toán</span>
                        </p>
                        <h3 class="mb-4 bread">Chọn phương thức thanh toán</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="ftco-section bg-light" style="font-family: 'Segoe UI', sans-serif;">
        <div class="container">
            <div class="row">
                {{-- Phương thức thanh toán --}}
                <div class="col-md-8 mb-4">
                    <div class="card p-4 shadow-sm rounded-lg h-100">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">Bạn muốn thanh toán như thế nào ?</h4>
                        </div>
                        
                        <!-- Thông báo deadline thanh toán -->
                        <div class="alert alert-warning mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock mr-2"></i>
                                <div>
                                    <strong>Thời gian thanh toán:</strong>
                                    <div class="text-danger font-weight-bold">30 phút</div>
                                    <small class="text-muted">Vui lòng hoàn tất thanh toán trong vòng 30 phút để giữ phòng</small>
                                </div>
                            </div>
                        </div>
                        
                        <div id="payment-method-section">
                            {{-- Khuyến mại --}}
                            <div class="card mb-4">
                                <div class="card-header">
                                    <i class="fas fa-gift mr-1"></i> Khuyến mại
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-7">
                                            <div class="input-group mb-3">
                                                <input type="text" class="form-control" id="promotion_code" placeholder="Nhập mã khuyến mại">
                                                <button class="btn btn-outline-primary" id="applyPromotionCode">Áp dụng mã</button>
                                            </div>
                                            @if(!empty($availablePromotions))
                                                <div class="mb-2 text-muted small">Hoặc chọn từ danh sách:</div>
                                                @foreach($availablePromotions as $promo)
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="radio" name="promotion_id" value="{{ $promo['id'] }}" id="promo_{{ $promo['id'] }}" {{ request()->get('promotion_id') == $promo['id'] ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="promo_{{ $promo['id'] }}">
                                                            <strong>{{ $promo['title'] }}</strong>
                                                            <span class="badge bg-success ms-2">{{ $promo['discount_text'] }}</span>
                                                            @if($promo['expired_at'])
                                                                <small class="text-muted ms-2">Hết hạn: {{ \Carbon\Carbon::parse($promo['expired_at'])->format('d/m/Y') }}</small>
                                                            @endif
                                                        </label>
                                                    </div>
                                                @endforeach
                                            @else
                                                <div class="text-muted small">Hiện chưa có khuyến mại phù hợp</div>
                                            @endif
                                            <div id="promotionAlert" class="alert d-none mt-2" role="alert"></div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="card">
                                                <div class="card-header"><i class="fas fa-receipt mr-1"></i> Tóm tắt thanh toán</div>
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between mb-1">
                                                        <span>Giá phòng</span><span id="price_room">{{ number_format($booking->base_room_price) }} VND</span>
                                                    </div>
                                                    <div class="d-flex justify-content-between mb-1 text-success">
                                                        <span>Khuyến mại (chỉ phòng)</span><span id="price_discount">- 0 VND</span>
                                                    </div>
                                                    <hr class="my-2">
                                                    <div class="d-flex justify-content-between fw-bold">
                                                        <span>Cần thanh toán</span><span id="price_final">{{ number_format($booking->total_booking_price) }} VND</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="payment-options">
                                <ul class="nav nav-tabs" id="paymentTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="credit-card-tab" data-bs-toggle="tab" data-bs-target="#credit-card" type="button" role="tab" aria-controls="credit-card" aria-selected="true">
                                            <i class="fas fa-credit-card mr-2"></i>
                                            Thẻ tín dụng/ghi nợ
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank" type="button" role="tab" aria-controls="bank" aria-selected="false">
                                            <i class="fas fa-university mr-2"></i>
                                            Chuyển khoản ngân hàng
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="paymentTabContent">
                                    {{-- Thẻ tín dụng/ghi nợ --}}
                                    <div class="tab-pane fade show active" id="credit-card" role="tabpanel">
                                        <div class="payment-method-item">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="method" id="creditCardPay" value="credit_card">
                                                <label class="form-check-label" for="creditCardPay">
                                                    <i class="fas fa-credit-card text-primary me-2"></i>
                                                    Thẻ tín dụng/ghi nợ
                                                </label>
                                            </div>
                                            <div class="payment-details mt-2">
                                                <ul>
                                                    <li>Thanh toán bằng thẻ Visa, Mastercard, American Express</li>
                                                    <li>Thanh toán an toàn và bảo mật</li>
                                                    <li>Xác nhận thanh toán ngay lập tức</li>
                                                    <li>Hỗ trợ thẻ nội địa và quốc tế</li>
                                                </ul>
                                                
                                                <div class="mt-3">
                                                     <button type="button" id="creditCardButton" class="btn btn-primary btn-lg">
                                                        <i class="fas fa-credit-card mr-2"></i>
                                                        Thanh toán bằng thẻ
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    {{-- Chuyển khoản ngân hàng --}}
                                    <div class="tab-pane fade" id="bank" role="tabpanel">
                                        <div class="payment-method-item">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="method" id="bankTransferPay" value="bank_transfer">
                                                <label class="form-check-label" for="bankTransferPay">
                                                    <i class="fas fa-university text-primary me-2"></i>
                                                    Chuyển khoản ngân hàng
                                                </label>
                                            </div>
                                            <div class="payment-details mt-2">
                                                <ul>
                                                    <li>Chuyển khoản trực tiếp đến tài khoản ngân hàng</li>
                                                    <li>Hỗ trợ nhiều ngân hàng: Vietcombank, BIDV, Techcombank</li>
                                                    <li>Quét mã QR hoặc copy thông tin tài khoản</li>
                                                    <li>Xác nhận thanh toán trong vòng 30 phút</li>
                                                </ul>
                                                
                                                <div class="mt-3">
                                                     <button type="button" id="bankTransferButton" class="btn btn-primary btn-lg">
                                                        <i class="fas fa-university mr-2"></i>
                                                        Chuyển khoản ngân hàng
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sidebar: Thông tin booking --}}
                <div class="col-md-4">
                    <div class="card shadow-sm rounded-lg p-4 h-100">
                        <h5 class="card-title text-dark mb-4">
                            <i class="fas fa-info-circle text-primary mr-2"></i>
                            Thông tin đặt phòng
                        </h5>
                        
                        @if(isset($booking))
                            <div id="booking-summary">
                                <h6 class="text-dark mb-3">Tóm tắt đặt phòng</h6>
                                
                                <!-- Ảnh phòng -->
                                <div class="mb-3">
                                    @php
                                        $imageUrl = null;
                                        if (isset($booking->room) && $booking->room && $booking->room->primaryImage) {
                                            $imageUrl = $booking->room->primaryImage->full_image_url;
                                        } elseif (isset($booking->room) && $booking->room && $booking->room->firstImage) {
                                            $imageUrl = $booking->room->firstImage->full_image_url;
                                        }
                                        if (!$imageUrl) {
                                            $imageUrl = asset('client/images/image_1.jpg'); // neutral placeholder
                                        }
                                    @endphp
                                    <img src="{{ $imageUrl }}" 
                                         alt="Ảnh phòng {{ $booking->room->roomType->name ?? 'Phòng' }}" 
                                         class="img-fluid rounded shadow-sm" 
                                         style="max-height: 200px; width: 100%; object-fit: cover;">
                                </div>
                                <div>Dịch vụ & phụ phí: <strong>{{ number_format($booking->surcharge + $booking->extra_services_total + $booking->total_services_price) }} VNĐ</strong></div>
                                <div class="mb-3"><strong>{{ $booking->room->roomType->name }}</strong></div>
                                <div class="row mb-3 align-items-center">
                                    <div class="col-5">
                                        <p class="mb-1 text-muted small">Nhận phòng</p>
                                        <strong class="text-dark">{{ \Carbon\Carbon::parse($booking->check_in_date)->translatedFormat('D, d \t\há\n\g m Y') }}</strong><br>
                                        <span class="small text-muted">Từ 14:00</span>
                                    </div>
                                    <div class="col-2 text-center">
                                        <i class="fas fa-arrow-right text-muted"></i>
                                    </div>
                                    <div class="col-5 text-right">
                                        <p class="mb-1 text-muted small">Trả phòng</p>
                                        <strong class="text-dark">{{ \Carbon\Carbon::parse($booking->check_out_date)->translatedFormat('D, d \t\há\n\g m Y') }}</strong><br>
                                        <span class="small text-muted">Trước 12:00</span>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="mb-2">
                                    <p class="font-weight-bold">(1x) {{ $booking->room->roomType->name }}</p>
                                    <ul class="list-unstyled mt-2 mb-0 text-muted small">
                                        <li><i class="fas fa-users mr-1 text-secondary"></i> {{ $booking->room->roomType->capacity }} khách</li>
                                        <li><i class="fas fa-utensils mr-1 text-secondary"></i> Gồm bữa sáng</li>
                                        <li><i class="fas fa-wifi mr-1 text-secondary"></i> Wifi miễn phí</li>
                                    </ul>
                                </div>
                                <div class="mt-3">
                                    <p class="font-weight-bold">Yêu cầu đặc biệt (nếu có)</p>
                                    <p class="text-muted small">
                                        @php
                                            $customerNotes = $booking->notes()->where('type', 'customer')->get();
                                        @endphp
                                        @if($customerNotes->count() > 0)
                                            @foreach($customerNotes as $note)
                                                {{ $note->content }}
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </p>
                                </div>
                                <hr class="my-3">
                                <div class="mb-1">
                                    <p class="mb-1 text-muted small">Tên khách</p>
                                    <strong class="text-dark">{{ $booking->user->name }}</strong>
                                </div>
                                <hr class="my-3">
                                <div class="mb-1">
                                    <p class="mb-1 text-muted small">Chi tiết người liên lạc</p>
                                    <strong class="text-dark">{{ $booking->user->name }}</strong>
                                    <p class="text-dark mb-0"><i class="fas fa-phone-alt mr-1"></i> {{ $booking->user->phone ?? '+84XXXXXXXXX' }}</p>
                                    <p class="text-dark mb-0"><i class="fas fa-envelope mr-1"></i> {{ $booking->user->email }}</p>
                                </div>
                                <div class="mt-4 p-3 text-white rounded" style="background-color: #72c02c;">
                                    <p class="mb-0 text-center">Sự lựa chọn tuyệt vời cho kỳ nghỉ của bạn!</p>
                                </div>
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                <p>Không tìm thấy thông tin đặt phòng</p>
                                <a href="{{ route('booking.confirm') }}" class="btn btn-primary">
                                    <i class="fas fa-plus mr-2"></i>Tạo đặt phòng mới
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const creditCardButton = document.getElementById('creditCardButton');
        const bankTransferButton = document.getElementById('bankTransferButton');
        const promoRadios = document.querySelectorAll('input[name="promotion_id"]');
        const promoCodeInput = document.getElementById('promotion_code');
        const applyCodeBtn = document.getElementById('applyPromotionCode');
        const alertBox = document.getElementById('promotionAlert');
        const priceOriginal = {{ (int) $booking->total_booking_price }};
        let selectedPromotionId = {{ json_encode($promotionId ?? request()->get('promotion_id')) }};
        let selectedPromotionCode = {{ json_encode($promotionCode ?? request()->get('promotion_code')) }};
        let currentDiscount = 0;

        function setAlert(message, type = 'success') {
            if (!alertBox) return;
            alertBox.className = `alert alert-${type}`;
            alertBox.textContent = message;
        }

        function clearAlert() {
            if (!alertBox) return;
            alertBox.className = 'alert d-none';
            alertBox.textContent = '';
        }

        function updateSummary(discountAmount) {
            currentDiscount = Math.max(0, Math.round(discountAmount));
            const roomPrice = {{ (int) $booking->base_room_price }};
            const servicesPrice = {{ (int) ($booking->surcharge + $booking->extra_services_total + $booking->total_services_price) }};
            const finalAmount = roomPrice - currentDiscount + servicesPrice;
            document.getElementById('price_discount').textContent = `- ${currentDiscount.toLocaleString('vi-VN')} VND`;
            document.getElementById('price_final').textContent = `${finalAmount.toLocaleString('vi-VN')} VND`;
        }

        async function previewPromotion() {
            try {
                const params = new URLSearchParams();
                if (selectedPromotionId) params.set('promotion_id', selectedPromotionId);
                if (selectedPromotionCode) params.set('promotion_code', selectedPromotionCode);
                const res = await fetch(`/api/payment/promotion-preview/{{ $booking->id }}?${params.toString()}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.success) {
                    updateSummary(data.discount_amount || 0);
                    setAlert('Đã áp dụng khuyến mại.', 'success');
                } else {
                    setAlert(data.message || 'Không thể áp dụng khuyến mại.', 'danger');
                    updateSummary(0);
                }
            } catch (e) {
                setAlert('Có lỗi khi xem trước khuyến mại.', 'danger');
                updateSummary(0);
            }
        }

        function buildUrl(base) {
            const params = new URLSearchParams();
            if (selectedPromotionId) params.set('promotion_id', selectedPromotionId);
            if (selectedPromotionCode) params.set('promotion_code', selectedPromotionCode);
            return params.toString() ? `${base}?${params.toString()}` : base;
        }

        // Chọn khuyến mại từ danh sách
        promoRadios.forEach(r => {
            r.addEventListener('change', function() {
                selectedPromotionId = this.value;
                selectedPromotionCode = null;
                clearAlert();
                previewPromotion();
            });
        });

        // Áp dụng mã khuyến mại
        if (applyCodeBtn) {
            applyCodeBtn.addEventListener('click', function() {
                const code = (promoCodeInput?.value || '').trim();
                if (!code) {
                    setAlert('Vui lòng nhập mã khuyến mại', 'warning');
                    return;
                }
                selectedPromotionCode = code;
                selectedPromotionId = null;
                previewPromotion();
            });
        }

        // Nếu có promotion từ URL, pre-select và preview ngay
        if (selectedPromotionId) {
            const pre = document.querySelector(`input[name="promotion_id"][value="${selectedPromotionId}"]`);
            if (pre) pre.checked = true;
            previewPromotion();
        } else if (selectedPromotionCode) {
            previewPromotion();
        }
        
        // Xử lý nút thanh toán thẻ tín dụng
        creditCardButton.addEventListener('click', function() {
            @if(isset($booking))
                const url = buildUrl(`/payment/credit-card/{{ $booking->id }}`);
                window.location.href = url;
            @else
                alert('Không tìm thấy thông tin đặt phòng. Vui lòng tạo đặt phòng trước.');
            @endif
        });

        // Xử lý nút chuyển khoản ngân hàng
        bankTransferButton.addEventListener('click', function() {
            @if(isset($booking))
                const url = buildUrl(`/payment/bank-transfer/{{ $booking->id }}`);
                window.location.href = url;
            @else
                alert('Không tìm thấy thông tin đặt phòng. Vui lòng tạo đặt phòng trước.');
            @endif
        });
    });
    </script>

    @section('styles')
    <style>
    .payment-method-item {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .payment-method-item:hover {
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
    }

    .payment-icons img {
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .payment-details ul {
        list-style: none;
        padding-left: 0;
    }

    .payment-details li {
        padding: 5px 0;
        color: #6c757d;
        font-size: 0.9rem;
    }

    .payment-details li:before {
        content: "✓";
        color: #28a745;
        font-weight: bold;
        margin-right: 8px;
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
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .payment-method-item {
            padding: 15px;
        }
        
        .btn-lg {
            padding: 10px 20px;
            font-size: 1rem;
        }
    }
    </style>
    @endsection
@endsection 