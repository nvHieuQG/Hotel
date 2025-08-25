@extends('client.layouts.master')

@section('title', 'Xác nhận đặt phòng Tour')

@section('content')
<section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate pb-5 text-center">
                <h1 class="mb-3 bread">Xác nhận đặt phòng Tour</h1>
                <p class="breadcrumbs">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ <i class="ion-ios-arrow-forward"></i></a></span>
                    <span class="mr-2"><a href="{{ route('tour-booking.search') }}">Đặt Tour <i class="ion-ios-arrow-forward"></i></a></span>
                    <span>Xác nhận</span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3>Xác nhận thông tin đặt phòng Tour</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('tour-booking.store') }}" method="POST" id="confirmForm">
                            @csrf
                            
                            <!-- Thông tin tour -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">Thông tin Tour</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Tên Tour:</strong> {{ $tourData['tour_name'] }}
                                            <input type="hidden" name="tour_name" value="{{ $tourData['tour_name'] }}">
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Tổng số khách:</strong> {{ $tourData['total_guests'] }} người
                                            <input type="hidden" name="total_guests" value="{{ $tourData['total_guests'] }}">
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <strong>Check-in:</strong> {{ \Carbon\Carbon::parse($tourData['check_in_date'])->format('d/m/Y') }}
                                            <input type="hidden" name="check_in_date" value="{{ $tourData['check_in_date'] }}">
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Check-out:</strong> {{ \Carbon\Carbon::parse($tourData['check_out_date'])->format('d/m/Y') }}
                                            <input type="hidden" name="check_out_date" value="{{ $tourData['check_out_date'] }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Hidden fields for promotion -->
                            @php
                                $totalPrice = 0;
                                $totalNights = \Carbon\Carbon::parse($tourData['check_in_date'])->diffInDays(\Carbon\Carbon::parse($tourData['check_out_date']));
                                $totalRooms = 0;
                                
                                // Tính tổng tiền từ room selections
                                foreach($tourData['room_selections'] as $roomTypeId => $selection) {
                                    if($selection['quantity'] > 0) {
                                        $roomType = \App\Models\RoomType::find($roomTypeId);
                                        $pricePerNight = $roomType->price;
                                        $pricePerRoom = $pricePerNight * $totalNights;
                                        $totalForRoomType = $pricePerRoom * $selection['quantity'];
                                        $totalPrice += $totalForRoomType;
                                        $totalRooms += $selection['quantity'];
                                    }
                                }
                                
                                // Lấy danh sách promotion có thể áp dụng
                                $availablePromotions = \App\Models\Promotion::active()
                                    ->available()
                                    ->where('minimum_amount', '<=', $totalPrice)
                                    ->orderBy('discount_value', 'desc')
                                    ->get();
                            @endphp
                            
                            <input type="hidden" name="original_total_price" value="{{ $totalPrice }}">
                            <input type="hidden" name="total_price" value="{{ $totalPrice }}">
                            <input type="hidden" name="promotion_id" id="promotion_id" value="">
                            <input type="hidden" name="promotion_code" id="promotion_code" value="">
                            <input type="hidden" name="promotion_discount" id="promotion_discount" value="0">
                            <input type="hidden" name="final_price" id="final_price" value="{{ $totalPrice }}">

                            <!-- Chi tiết phòng đã chọn -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">Chi tiết phòng đã chọn</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Loại phòng</th>
                                                    <th>Số lượng</th>
                                                    <th>Số khách/phòng</th>
                                                    <th>Giá/phòng/đêm</th>
                                                    <th>Tổng tiền</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tourData['room_selections'] as $roomTypeId => $selection)
                                                    @if($selection['quantity'] > 0)
                                                        @php
                                                            $roomType = \App\Models\RoomType::find($roomTypeId);
                                                            $pricePerNight = $roomType->price;
                                                            $pricePerRoom = $pricePerNight * $totalNights;
                                                            $totalForRoomType = $pricePerRoom * $selection['quantity'];
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $roomType->name }}</td>
                                                            <td>{{ $selection['quantity'] }}</td>
                                                            <td>{{ $selection['guests_per_room'] }}</td>
                                                            <td>{{ number_format($pricePerRoom, 0, ',', '.') }} VNĐ</td>
                                                            <td>{{ number_format($totalForRoomType, 0, ',', '.') }} VNĐ</td>
                                                        </tr>
                                                        
                                                        <!-- Hidden inputs -->
                                                        <input type="hidden" name="room_selections[{{ $roomTypeId }}][room_type_id]" value="{{ $roomTypeId }}">
                                                        <input type="hidden" name="room_selections[{{ $roomTypeId }}][quantity]" value="{{ $selection['quantity'] }}">
                                                        <input type="hidden" name="room_selections[{{ $roomTypeId }}][guests_per_room]" value="{{ $selection['guests_per_room'] }}">
                                                    @endif
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-info">
                                                <tr>
                                                    <td colspan="4" class="text-right"><strong>Tổng tiền:</strong></td>
                                                    <td><strong>{{ number_format($totalPrice, 0, ',', '.') }} VNĐ</strong></td>
                                                </tr>
                                                <tr id="promotionRow" style="display: none;">
                                                    <td colspan="4" class="text-right text-success"><strong>Giảm giá:</strong></td>
                                                    <td class="text-success"><strong id="discountAmount">-0 VNĐ</strong></td>
                                                </tr>
                                                <tr id="finalPriceRow" style="display: none;">
                                                    <td colspan="4" class="text-right"><strong>Giá cuối:</strong></td>
                                                    <td><strong id="finalPriceDisplay">{{ number_format($totalPrice, 0, ',', '.') }} VNĐ</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Mã giảm giá -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">Mã giảm giá</h5>
                                    
                                    <!-- Thông báo quy tắc -->
                                    <div class="alert alert-info mb-3">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Quy tắc sử dụng mã giảm giá:</strong>
                                        <ul class="mb-0 mt-2">
                                            <li>Chỉ được áp dụng <strong>1 mã giảm giá duy nhất</strong> cho mỗi đơn hàng</li>
                                            <li>Khi chọn mã mới, mã cũ sẽ bị thay thế</li>
                                            <li>Mã giảm giá tùy chỉnh cũng sẽ thay thế mã đã chọn từ danh sách</li>
                                            <li>Bạn có thể thay đổi mã giảm giá bất cứ lúc nào trước khi xác nhận đặt phòng</li>
                                        </ul>
                                    </div>
                                    
                                    @if($availablePromotions->count() > 0)
                                        <div class="form-group">
                                            <label>Chọn mã giảm giá: <small class="text-muted">(Chỉ được chọn 1 mã duy nhất)</small></label>
                                            <div class="row">
                                                @foreach($availablePromotions as $promotion)
                                                    <div class="col-md-6 mb-2">
                                                        <div class="card promotion-card" 
                                                             data-promotion-id="{{ $promotion->id }}"
                                                             data-discount-amount="{{ $promotion->discount_value }}"
                                                             data-discount-type="{{ $promotion->discount_type }}"
                                                             data-minimum-amount="{{ $promotion->minimum_amount }}">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex justify-content-between align-items-center">
                                                                    <div>
                                                                        <h6 class="mb-1">{{ $promotion->title }}</h6>
                                                                        <small class="text-muted">{{ $promotion->description }}</small>
                                                                        <br>
                                                                                                                                                 <small class="text-info">
                                                                             @if($promotion->discount_type === 'percentage')
                                                                                 Giảm {{ $promotion->discount_value }}%
                                                                             @else
                                                                                 Giảm {{ number_format($promotion->discount_value, 0, ',', '.') }} VNĐ
                                                                             @endif
                                                                         </small>
                                                                        <br>
                                                                        <small class="text-warning">
                                                                            Tối thiểu: {{ number_format($promotion->minimum_amount, 0, ',', '.') }} VNĐ
                                                                        </small>
                                                                    </div>
                                                                    <div class="text-right">
                                                                        <button type="button" class="btn btn-sm btn-outline-primary select-promotion-btn">
                                                                            Chọn
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        
                                        <div id="selectedPromotionInfo" class="alert alert-success" style="display: none;">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <i class="fas fa-check-circle"></i>
                                                    <strong>Đã chọn mã giảm giá:</strong> <span id="selectedPromotionTitle"></span>
                                                    <br>
                                                    <strong>Giảm giá:</strong> <span id="selectedPromotionDiscount"></span>
                                                    <br>
                                                    <strong>Giá cuối:</strong> <span id="selectedPromotionFinalPrice"></span>
                                                </div>
                                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removePromotion()">
                                                    <i class="fas fa-times"></i> Xóa mã giảm giá
                                                </button>
                                            </div>
                                            <div class="mt-2">
                                                <small class="text-muted">
                                                    <i class="fas fa-info-circle"></i>
                                                    Bạn có thể thay đổi mã giảm giá bằng cách chọn mã khác từ danh sách bên trên.
                                                </small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-info">
                                            <i class="fas fa-info-circle"></i>
                                            Không có mã giảm giá nào khả dụng cho đơn hàng này.
                                        </div>
                                    @endif
                                    
                                    <!-- Input nhập mã tùy chỉnh -->
                                    <div class="form-group mt-3">
                                        <label>Hoặc nhập mã giảm giá tùy chỉnh: <small class="text-muted">(Sẽ thay thế mã đã chọn từ danh sách)</small></label>
                                        <div class="input-group">
                                            <input type="text" name="promotion_code" id="promotion_code" class="form-control" 
                                                   placeholder="Nhập mã giảm giá tùy chỉnh (nếu có)" value="{{ old('promotion_code') }}">
                                            <div class="input-group-append">
                                                <button type="button" class="btn btn-outline-primary" id="applyPromotionBtn">
                                                    <i class="fas fa-gift"></i> Áp dụng
                                                </button>
                                            </div>
                                        </div>
                                        <div id="promotionResult" class="mt-2" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Thông tin bổ sung -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">Thông tin bổ sung</h5>
                                    <div class="form-group">
                                        <label for="special_requests">Yêu cầu đặc biệt (không bắt buộc):</label>
                                        <textarea name="special_requests" id="special_requests" class="form-control" rows="3" 
                                                  placeholder="Nhập các yêu cầu đặc biệt nếu có..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Tóm tắt -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6>Tóm tắt đặt phòng:</h6>
                                        <ul class="mb-0">
                                            <li><strong>Tổng số phòng:</strong> {{ $totalRooms }} phòng</li>
                                            <li><strong>Tổng số khách:</strong> {{ $tourData['total_guests'] }} người</li>
                                            <li><strong>Số đêm:</strong> {{ $totalNights }} đêm</li>
                                            <li><strong>Tổng tiền:</strong> <span id="summaryTotalPrice">{{ number_format($totalPrice, 0, ',', '.') }} VNĐ</span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Nút điều hướng -->
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="{{ route('tour-booking.select-rooms') }}" class="btn btn-secondary btn-lg btn-block">
                                        <i class="fas fa-arrow-left"></i> Quay lại
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                                        <i class="fas fa-check"></i> Xác nhận đặt phòng
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Thông tin bổ sung -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Lưu ý quan trọng</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Vui lòng kiểm tra kỹ thông tin trước khi xác nhận</li>
                            <li>Đặt phòng sẽ được xử lý sau khi thanh toán thành công</li>
                            <li>Bạn có thể hủy đặt phòng trong vòng 24 giờ trước ngày check-in</li>
                            <li>Liên hệ hotline nếu cần hỗ trợ: <strong>1900-xxxx</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmForm = document.getElementById('confirmForm');
    const applyPromotionBtn = document.getElementById('applyPromotionBtn');
    const promotionCodeInput = document.getElementById('promotion_code');
    const promotionResult = document.getElementById('promotionResult');
    const promotionRow = document.getElementById('promotionRow');
    const finalPriceRow = document.getElementById('finalPriceRow');
    const discountAmount = document.getElementById('discountAmount');
    const finalPriceDisplay = document.getElementById('finalPriceDisplay');
    const promotionDiscountInput = document.getElementById('promotion_discount');
    const finalPriceInput = document.getElementById('final_price');
    const promotionIdInput = document.getElementById('promotion_id');
    const selectedPromotionInfo = document.getElementById('selectedPromotionInfo');
    const selectedPromotionTitle = document.getElementById('selectedPromotionTitle');
    const selectedPromotionDiscount = document.getElementById('selectedPromotionDiscount');
    const selectedPromotionFinalPrice = document.getElementById('selectedPromotionFinalPrice');
    const summaryTotalPrice = document.getElementById('summaryTotalPrice');
    
    const totalPrice = {{ $totalPrice }};
    let selectedPromotion = null;
    
    // Xử lý chọn promotion từ danh sách - CHỈ CHO PHÉP CHỌN 1 MÃ DUY NHẤT
    document.querySelectorAll('.select-promotion-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const promotionCard = this.closest('.promotion-card');
            const promotionId = promotionCard.dataset.promotionId;
            const discountAmount = parseFloat(promotionCard.dataset.discountAmount);
            const discountType = promotionCard.dataset.discountType;
            const minimumAmount = parseFloat(promotionCard.dataset.minimumAmount);
            
            // Kiểm tra điều kiện tối thiểu
            if (totalPrice < minimumAmount) {
                alert(`Đơn hàng phải có giá trị tối thiểu ${minimumAmount.toLocaleString('vi-VN')} VNĐ để áp dụng khuyến mại này.`);
                return;
            }
            
            // Nếu đã có mã giảm giá được chọn, hỏi xác nhận thay thế
            if (selectedPromotion) {
                const confirmReplace = confirm(`Bạn đã chọn mã giảm giá "${selectedPromotion.title}". Bạn có muốn thay thế bằng mã "${promotionCard.querySelector('h6').textContent}" không?`);
                if (!confirmReplace) {
                    return;
                }
            }
            
            // Tính toán giảm giá trực tiếp (không cần gọi API vì chưa có tour booking)
            let discountValue = 0;
            if (discountType === 'percentage') {
                discountValue = (totalPrice * discountAmount) / 100;
            } else {
                discountValue = discountAmount;
            }
            
            const finalPrice = totalPrice - discountValue;
            
            // Cập nhật UI với dữ liệu đã tính toán
            selectedPromotion = {
                id: promotionId,
                title: promotionCard.querySelector('h6').textContent,
                discountAmount: discountValue,
                finalPrice: finalPrice
            };
            
            // Hiển thị thông tin promotion đã chọn
            selectedPromotionTitle.textContent = selectedPromotion.title;
            selectedPromotionDiscount.textContent = `-${discountValue.toLocaleString('vi-VN')} VNĐ`;
            selectedPromotionFinalPrice.textContent = `${finalPrice.toLocaleString('vi-VN')} VNĐ`;
            selectedPromotionInfo.style.display = 'block';
            
            // Cập nhật bảng giá
            document.getElementById('discountAmount').textContent = `-${discountValue.toLocaleString('vi-VN')} VNĐ`;
            document.getElementById('finalPriceDisplay').textContent = `${finalPrice.toLocaleString('vi-VN')} VNĐ`;
            promotionRow.style.display = 'table-row';
            finalPriceRow.style.display = 'table-row';
            
            // Cập nhật hidden fields
            promotionDiscountInput.value = discountValue;
            finalPriceInput.value = finalPrice;
            promotionIdInput.value = promotionId;
            
            // Cập nhật total_price để sử dụng giá cuối sau giảm giá cho thanh toán
            document.querySelector('input[name="total_price"]').value = finalPrice;
            
            // Cập nhật tóm tắt
            summaryTotalPrice.textContent = `${finalPrice.toLocaleString('vi-VN')} VNĐ`;
            
            // Đổi trạng thái nút - CHỈ CÓ 1 NÚT "ĐÃ CHỌN"
            document.querySelectorAll('.select-promotion-btn').forEach(b => {
                b.textContent = 'Chọn';
                b.classList.remove('btn-success');
                b.classList.add('btn-outline-primary');
            });
            this.textContent = 'Đã chọn';
            this.classList.remove('btn-outline-primary');
            this.classList.add('btn-success');
            
            // Reset input nhập mã tùy chỉnh
            promotionCodeInput.value = '';
            promotionResult.style.display = 'none';
            
            // Hiển thị thông báo thành công
            if (selectedPromotion && selectedPromotion.id !== promotionId) {
                alert('Đã thay thế mã giảm giá thành công!');
            } else {
                alert('Áp dụng mã giảm giá thành công!');
            }
        });
    });
    
    // Function để xóa mã giảm giá
    window.removePromotion = function() {
        if (!selectedPromotion) {
            alert('Không có mã giảm giá nào để xóa.');
            return;
        }
        
        // Reset promotion trực tiếp (không cần gọi API)
        selectedPromotion = null;
        selectedPromotionInfo.style.display = 'none';
        
        // Reset bảng giá
        document.getElementById('discountAmount').textContent = '0 VNĐ';
        document.getElementById('finalPriceDisplay').textContent = `${totalPrice.toLocaleString('vi-VN')} VNĐ`;
        promotionRow.style.display = 'none';
        finalPriceRow.style.display = 'none';
        
        // Reset hidden fields
        promotionDiscountInput.value = 0;
        finalPriceInput.value = totalPrice;
        promotionIdInput.value = '';
        
        // Cập nhật total_price về giá gốc
        document.querySelector('input[name="total_price"]').value = totalPrice;
        
        // Cập nhật tóm tắt
        summaryTotalPrice.textContent = `${totalPrice.toLocaleString('vi-VN')} VNĐ`;
        
        // Reset tất cả nút
        document.querySelectorAll('.select-promotion-btn').forEach(btn => {
            btn.textContent = 'Chọn';
            btn.classList.remove('btn-success');
            btn.classList.add('btn-outline-primary');
        });
        
        // Reset input nhập mã tùy chỉnh
        promotionCodeInput.value = '';
        promotionResult.style.display = 'none';
        
        alert('Đã xóa mã giảm giá! Bạn có thể chọn mã khác từ danh sách hoặc nhập mã tùy chỉnh.');
    };
    
    // Xử lý áp dụng mã giảm giá tùy chỉnh
    applyPromotionBtn.addEventListener('click', function() {
        const code = promotionCodeInput.value.trim();
        if (!code) {
            alert('Vui lòng nhập mã giảm giá');
            return;
        }
        
        // Reset promotion đã chọn
        if (selectedPromotion) {
            const confirmReplace = confirm(`Bạn đã chọn mã giảm giá "${selectedPromotion.title}". Bạn có muốn thay thế bằng mã tùy chỉnh "${code}" không?`);
            if (!confirmReplace) {
                return;
            }
            
            selectedPromotion = null;
            selectedPromotionInfo.style.display = 'none';
            document.querySelectorAll('.select-promotion-btn').forEach(btn => {
                btn.textContent = 'Chọn';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-primary');
            });
        }
        
        // Gọi API để kiểm tra mã giảm giá
        fetch('{{ route("promotion.check") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                code: code,
                amount: totalPrice
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const discount = data.discount_amount;
                const finalPrice = totalPrice - discount;
                
                // Hiển thị kết quả
                promotionResult.innerHTML = `
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        <strong>Mã giảm giá hợp lệ!</strong> Giảm ${data.discount_formatted}
                    </div>
                `;
                promotionResult.style.display = 'block';
                
                // Hiển thị dòng giảm giá và giá cuối
                discountAmount.textContent = `-${data.discount_formatted}`;
                finalPriceDisplay.textContent = `${data.final_price_formatted} VNĐ`;
                promotionRow.style.display = 'table-row';
                finalPriceRow.style.display = 'table-row';
                
                // Cập nhật hidden fields
                promotionDiscountInput.value = discount;
                finalPriceInput.value = finalPrice;
                promotionIdInput.value = data.promotion_id || '';
                document.querySelector('input[name="promotion_code"]').value = code;
                
                // Cập nhật total_price để sử dụng giá cuối sau giảm giá
                document.querySelector('input[name="total_price"]').value = finalPrice;
                
                // Cập nhật tóm tắt
                summaryTotalPrice.textContent = `${data.final_price_formatted} VNĐ`;
                
            } else {
                promotionResult.innerHTML = `
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> 
                        <strong>Mã giảm giá không hợp lệ:</strong> ${data.message}
                    </div>
                `;
                promotionResult.style.display = 'block';
                
                // Ẩn dòng giảm giá và giá cuối
                promotionRow.style.display = 'none';
                finalPriceRow.style.display = 'none';
                
                // Reset hidden fields
                promotionDiscountInput.value = 0;
                finalPriceInput.value = totalPrice;
                promotionIdInput.value = '';
                document.querySelector('input[name="promotion_code"]').value = '';
                
                // Reset total_price về giá gốc
                document.querySelector('input[name="total_price"]').value = totalPrice;
                
                // Cập nhật tóm tắt
                summaryTotalPrice.textContent = `${totalPrice.toLocaleString('vi-VN')} VNĐ`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            promotionResult.innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> 
                    <strong>Lỗi:</strong> Không thể kiểm tra mã giảm giá
                </div>
            `;
            promotionResult.style.display = 'block';
        });
    });
    
    // Xử lý submit form
    confirmForm.addEventListener('submit', function(e) {
        if (!confirm('Bạn có chắc chắn muốn đặt phòng với thông tin này?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<style>
.table th {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}

.promotion-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
}

.promotion-card:hover {
    border-color: #007bff;
    box-shadow: 0 4px 8px rgba(0,123,255,0.2);
}

.promotion-card.selected {
    border-color: #28a745;
    background-color: #f8fff9;
}

.select-promotion-btn {
    min-width: 80px;
}
</style>
@endsection 