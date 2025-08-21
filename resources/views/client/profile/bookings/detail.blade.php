<div class="booking-detail-container">
    <!-- Header với ảnh phòng -->
    <div class="hero-section mb-5">
        <div class="hero-image-container">
                @if($booking->room && $booking->room->primaryImage)
                    <img src="{{ asset('storage/' . $booking->room->primaryImage->image_url) }}" 
                         alt="Ảnh phòng {{ $booking->room->name ?? 'Không xác định' }}" 
                     class="hero-image">
                @elseif($booking->room && $booking->room->firstImage)
                    <img src="{{ asset('storage/' . $booking->room->firstImage->image_url) }}" 
                         alt="Ảnh phòng {{ $booking->room->name ?? 'Không xác định' }}" 
                     class="hero-image">
                @else
                <div class="hero-placeholder">
                    <i class="fas fa-image fa-5x text-muted mb-3"></i>
                    <h4 class="text-muted">Chưa có ảnh phòng</h4>
                    </div>
                @endif
            
            <!-- Overlay với thông tin booking -->
            <div class="hero-overlay">
                <div class="hero-content">
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-8 col-md-10 mx-auto text-center">
                                <h1 class="hero-title mb-3">{{ $booking->room->roomType->name ?? 'Phòng khách sạn' }}</h1>
                                <div class="hero-info">
                                    <div class="hero-info-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span>{{ $booking->check_in_date->format('d/m/Y') }} - {{ $booking->check_out_date->format('d/m/Y') }}</span>
                                        <small>({{ round($booking->check_in_date->diffInDays($booking->check_out_date)) }} đêm)</small>
            </div>
                                    <div class="hero-info-item">
                                        <i class="fas fa-user"></i>
                                        <span>{{ $booking->user->name }}</span>
                                    </div>
                                    <div class="hero-info-item">
                                        <i class="fas fa-hotel"></i>
                                        <span>{{ $booking->booking_id }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="container px-4">
        <!-- Thông tin chính -->
        <div class="row mb-5">
            <div class="col-xl-8 col-lg-7">
                <div class="card border-0 shadow-lg">
                    <div class="card-header" style="background-color: #C9A888; color: white;">
                        <h4 class="mb-0"><i class="fas fa-calendar-check mr-2"></i>Thông Tin Đặt Phòng</h4>
                    </div>
                    <div class="card-body p-4">
                        @php 
                            $ci = $booking->check_in_date instanceof \Carbon\Carbon ? $booking->check_in_date : \Carbon\Carbon::parse($booking->check_in_date);
                            $co = $booking->check_out_date instanceof \Carbon\Carbon ? $booking->check_out_date : \Carbon\Carbon::parse($booking->check_out_date);
                            $ciDate = $ci ? $ci->copy()->startOfDay() : null;
                            $coDate = $co ? $co->copy()->startOfDay() : null;
                            $nights = ($ciDate && $coDate) ? (int) $ciDate->diffInDays($coDate) : 0;
                            $nightly = (int)($booking->room->roomType->price ?? $booking->room->price ?? 0);
                            $roomCost = max(0, $nights) * $nightly;
                            $surcharge = (float)($booking->surcharge ?? 0);
                            $roomChangeSurcharge = (float) $booking->roomChanges()->whereIn('status', ['approved', 'completed'])->sum('price_difference');
                            $guestSurcharge = max(0, $surcharge - $roomChangeSurcharge);
                            $svcFromAdmin = (float)($booking->total_services_price ?? 0);
                            $svcFromClient = (float)($booking->extra_services_total ?? 0);
                            $svcTotal = $svcFromAdmin + $svcFromClient; 
                            $totalDiscount = (float) $booking->payments()->where('status', '!=', 'failed')->sum('discount_amount');
                            if ($totalDiscount <= 0 && (float)($booking->promotion_discount ?? 0) > 0) {
                                $totalDiscount = (float) $booking->promotion_discount;
                            }
                            $totalBeforeDiscount = ($roomCost ?? 0) + ($guestSurcharge ?? 0) + ($roomChangeSurcharge ?? 0) + ($svcTotal ?? 0);
                            $finalAmount = $totalBeforeDiscount - ($totalDiscount ?? 0);
                        @endphp
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="info-block">
                                    <div class="kv-item"><span class="kv-label">Mã đặt phòng</span><span class="kv-value">{{ $booking->booking_id }}</span></div>
                                    <div class="kv-item"><span class="kv-label">Loại phòng</span><span class="kv-value">{{ $booking->room->roomType->name ?? 'Không xác định' }}</span></div>
                                    <div class="kv-item"><span class="kv-label">Ngày check-in</span><span class="kv-value">{{ $booking->check_in_date->format('d/m/Y') }}</span></div>
                                    <div class="kv-item"><span class="kv-label">Ngày check-out</span><span class="kv-value">{{ $booking->check_out_date->format('d/m/Y') }}</span></div>
                                    <div class="kv-item"><span class="kv-label">Số đêm</span><span class="kv-value">{{ $nights }} đêm</span></div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-block">
                                    <div class="kv-item"><span class="kv-label">Tiền phòng</span><span class="kv-value text-primary">{{ number_format($roomCost) }} VNĐ</span></div>
                                    <div class="kv-item"><span class="kv-label">Phụ phí</span><span class="kv-value">{{ number_format($guestSurcharge) }} VNĐ</span></div>
                                    <div class="kv-item"><span class="kv-label">Phụ thu đổi phòng</span><span class="kv-value">{{ number_format($roomChangeSurcharge) }} VNĐ</span></div>
                                    <div class="kv-item"><span class="kv-label">Dịch vụ (tổng)</span><span class="kv-value">{{ number_format($svcTotal) }} VNĐ</span></div>
                                </div>
                            </div>
                        </div>
                        <hr>
                        <div class="row g-4 align-items-center">
                            <div class="col-md-6">
                                <div class="info-block">
                                    <div class="kv-item"><span class="kv-label">Khuyến mại</span><span class="kv-value text-success">-{{ number_format(max(0,$totalDiscount)) }} VNĐ</span></div>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-right">
                                <div class="total-box d-inline-block px-4 py-3 bg-primary text-white rounded-3 shadow-sm">
                                    <div class="small opacity-75">Tổng cộng</div>
                                    <div class="h4 fw-bold mb-0">{{ number_format($finalAmount) }} VNĐ</div>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <span class="badge fs-6 px-3 py-2 badge-{{ $booking->status == 'pending' ? 'warning' : ($booking->status == 'confirmed' ? 'success' : ($booking->status == 'cancelled' ? 'danger' : 'primary')) }}">
                                <i class="fas fa-circle mr-2"></i>{{ $booking->status_text }}
                            </span>
                        </div>
                    </div>
            </div>
        </div>
        
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-lg h-100">
                <div class="card-header" style="background-color: #C9A888; color: white;">
                    <h4 class="mb-0"><i class="fas fa-user me-3"></i>Thông Tin Khách Hàng</h4>
                </div>
                <div class="card-body p-4">
                    <div class="info-block mb-4">
                        <div class="kv-item"><span class="kv-label">Họ tên</span><span class="kv-value">{{ $booking->user->name }}</span></div>
                        <div class="kv-item"><span class="kv-label">Email</span><span class="kv-value">{{ $booking->user->email }}</span></div>
                        <div class="kv-item"><span class="kv-label">Sức chứa phòng</span><span class="kv-value">{{ $booking->room && $booking->room->roomType ? $booking->room->roomType->capacity . ' người' : 'Chưa cung cấp' }}</span></div>
                        <div class="kv-item"><span class="kv-label">Số điện thoại</span><span class="kv-value">{{ $booking->user->phone ?? 'Chưa cung cấp' }}</span></div>
                        <div class="kv-item"><span class="kv-label">Ngày đặt</span><span class="kv-value">{{ $booking->created_at->format('d/m/Y H:i') }}</span></div>
                    </div>
                    
                    @php
                        $customerNotes = $booking->notes()->where('type', 'customer')->get();
                    @endphp
                    @if($customerNotes && $customerNotes->count() > 0)
                        <hr class="my-4">
                        <div class="info-item">
                            <label class="info-label">Ghi chú</label>
                            @foreach($customerNotes as $note)
                                <div class="note-item mb-3 p-3 bg-light rounded">
                                    <small class="text-muted d-block mb-2">{{ $note->created_at->format('d/m/Y H:i') }}</small>
                                    <div class="note-content">{{ $note->content }}</div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    </div>

    <div class="container px-4">
    <!-- Yêu cầu đổi phòng -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header" style="background-color: #C9A888; color: white;">
                    <h4 class="mb-0"><i class="fas fa-exchange-alt me-3"></i>Yêu cầu đổi phòng</h4>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-lg-6">
                            <div class="room-change-section text-center p-5">
                                <div class="mb-4">
                                    <i class="fas fa-exchange-alt fa-4x text-primary mb-4"></i>
                                    <h5 class="fw-bold mb-3">Yêu cầu đổi phòng</h5>
                                    <p class="text-muted mb-4 fs-6">
                                        Nếu bạn muốn đổi sang phòng khác, vui lòng gửi yêu cầu và chờ xét duyệt từ khách sạn.
                                    </p>
                                </div>
                                @php
                                    $hasPendingRequest = $booking->roomChanges()->where('status', 'pending')->exists();
                                @endphp
                                @if($hasPendingRequest)
                                    <div class="alert alert-warning border-0">
                                        <i class="fas fa-clock me-2"></i>
                                        <strong>Đang chờ duyệt:</strong> Bạn đã có yêu cầu đổi phòng đang chờ xét duyệt.
                                    </div>
                                @else
                                    <a href="{{ route('room-change.request', $booking->id) }}" class="btn btn-primary btn-lg px-5 py-3">
                                        <i class="fas fa-exchange-alt me-2"></i>Yêu cầu đổi phòng
                                    </a>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="room-change-history text-center p-5">
                                <div class="mb-4">
                                    <i class="fas fa-history fa-4x text-secondary mb-4"></i>
                                    <h5 class="fw-bold mb-3">Lịch sử đổi phòng</h5>
                                    <p class="text-muted mb-4 fs-6">
                                        Xem lịch sử các yêu cầu đổi phòng của booking này.
                                    </p>
                                </div>
                                <a href="{{ route('room-change.history', $booking->id) }}" class="btn btn-outline-primary btn-lg px-5 py-3">
                                    <i class="fas fa-history me-2"></i>Xem lịch sử
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Yêu cầu xuất hóa đơn VAT -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header" style="background-color: #C9A888; color: white;">
                    <h4 class="mb-0"><i class="fas fa-file-invoice-dollar me-3"></i>Hóa đơn VAT</h4>
                </div>
                <div class="card-body">
                    @php $vatInfo = (array)($booking->vat_invoice_info ?? []); @endphp
                    @if(!empty($vatInfo))
                        <div class="mb-2 small text-muted">Thông tin xuất hóa đơn do bạn cung cấp:</div>
                        <ul class="small mb-3">
                            <li><strong>Công ty:</strong> {{ $vatInfo['companyName'] ?? '' }}</li>
                            <li><strong>MST:</strong> {{ $vatInfo['taxCode'] ?? '' }}</li>
                            <li><strong>Địa chỉ:</strong> {{ $vatInfo['companyAddress'] ?? '' }}</li>
                            <li><strong>Email nhận HĐ:</strong> {{ $vatInfo['receiverEmail'] ?? '' }}</li>
                        </ul>
                        <div class="d-flex gap-2">
                            <form action="{{ route('client.vat-invoice.generate', $booking->id) }}" method="POST" class="me-2">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm">Tạo hóa đơn PDF</button>
                            </form>
                            <form action="{{ route('client.vat-invoice.send', $booking->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Gửi email hóa đơn</button>
                            </form>
                        </div>
                        @if($booking->vat_invoice_file_path)
                            <div class="mt-2">
                                <a href="{{ asset(str_starts_with($booking->vat_invoice_file_path,'public/') ? Storage::url(str_replace('public/','',$booking->vat_invoice_file_path)) : $booking->vat_invoice_file_path) }}" target="_blank" class="small">Xem hóa đơn đã tạo</a>
                            </div>
                        @endif
                    @else
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="needVatInvoice" onchange="toggleVatForm()" style="transform: scale(1.2);">
                                <label class="form-check-label fs-5 fw-bold" for="needVatInvoice" style="margin-left: 10px;">
                                    <i class="fas fa-receipt text-primary me-2"></i>Tôi cần xuất hóa đơn VAT
                                </label>
                            </div>
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-info-circle fs-5"></i>
                                <strong class="fs-6">Lưu ý:</strong> Giá tiền đã bao gồm VAT 10%, không thu thêm phí. Chỉ cần cung cấp thông tin công ty để xuất hóa đơn.
                            </div>
                        </div>
                        
                        <form action="{{ route('client.vat-invoice.request', $booking->id) }}" method="POST" id="vatForm" style="display: none;">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tên công ty <span class="text-danger">*</span></label>
                                    <input name="companyName" class="form-control" placeholder="Nhập tên công ty" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">MST <span class="text-danger">*</span></label>
                                    <input name="taxCode" class="form-control" placeholder="Nhập mã số thuế" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Địa chỉ công ty <span class="text-danger">*</span></label>
                                    <input name="companyAddress" class="form-control" placeholder="Nhập địa chỉ công ty" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Email nhận HĐ <span class="text-danger">*</span></label>
                                    <input name="receiverEmail" type="email" class="form-control" placeholder="email@company.com" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Người nhận</label>
                                    <input name="receiverName" class="form-control" placeholder="Tên người nhận hóa đơn">
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label fw-bold">Ghi chú</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="Ghi chú thêm (nếu có)"></textarea>
                                </div>
                            </div>
                            
                            @php
                                $ci = $booking->check_in_date;
                                $co = $booking->check_out_date;
                                $nights = $ci && $co ? $ci->copy()->startOfDay()->diffInDays($co->copy()->startOfDay()) : 0;
                                $nightly = (int)($booking->room->roomType->price ?? $booking->room->price ?? 0);
                                $roomCost = max(0, $nights) * $nightly;
                                $services = (float)($booking->extra_services_total ?? 0) + (float)($booking->total_services_price ?? 0);
                                $surcharge = (float)($booking->surcharge ?? 0);
                                $discount = (float) $booking->payments()->where('status','!=','failed')->sum('discount_amount');
                                
                                // Tổng cộng đã bao gồm VAT 10%
                                $grandTotal = $roomCost + $services + $surcharge - $discount;
                                
                                // Tính ngược lại: giá trước VAT = tổng cộng / (1 + VAT rate)
                                $vatRate = 0.1;
                                $subtotal = round($grandTotal / (1 + $vatRate));
                                $vatAmount = $grandTotal - $subtotal;
                            @endphp
                            
                            <div class="alert alert-info mt-3 mb-3">
                                <i class="fas fa-info-circle fs-5"></i>
                                <strong class="fs-6">Thông tin hóa đơn VAT:</strong><br>
                                <div class="mt-2">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="text-center p-2 bg-white rounded">
                                                <div class="fw-bold text-primary fs-6">Tổng cộng</div>
                                                <div class="fs-5 fw-bold">{{ number_format($grandTotal) }} VNĐ</div>
                                                <small class="text-success">Đã bao gồm VAT</small>
                            </div>
                </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-2 bg-white rounded">
                                                <div class="fw-bold text-secondary fs-6">Giá trước VAT</div>
                                                <div class="fs-5">{{ number_format($subtotal) }} VNĐ</div>
            </div>
        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-2 bg-white rounded">
                                                <div class="fw-bold text-info fs-6">VAT (10%)</div>
                                                <div class="fs-5">{{ number_format($vatAmount) }} VNĐ</div>
                                </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-2">
                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle me-1"></i>Giá đã bao gồm VAT, không thu thêm phí
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            @if($grandTotal >= 5000000)
                                <div class="alert alert-warning mt-3 mb-3">
                                    <i class="fas fa-exclamation-triangle fs-5"></i>
                                    <strong class="fs-6">Lưu ý quan trọng:</strong> Hóa đơn từ 5.000.000đ phải thanh toán bằng thẻ/tài khoản công ty hoặc chuyển khoản công ty theo quy định pháp luật.
                                </div>
                        @else
                                <div class="alert alert-success mt-3 mb-3">
                                    <i class="fas fa-check-circle fs-5"></i>
                                    <strong class="fs-6">Thông tin:</strong> Bạn có thể thanh toán cá nhân trước, sau đó chuyển khoản công ty để xuất hóa đơn VAT.
                                </div>
                        @endif
                            
                            <div class="alert alert-light mt-3 mb-3">
                                <i class="fas fa-receipt fs-5"></i>
                                <strong class="fs-6">Quy trình xuất hóa đơn VAT:</strong><br>
                                <div class="mt-2">
                                    <div class="row">
                                        <div class="col-md-3 text-center">
                                            <div class="p-2 bg-white rounded">
                                                <div class="fw-bold text-primary">1</div>
                                                <small>Cung cấp thông tin công ty</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="p-2 bg-white rounded">
                                                <div class="fw-bold text-primary">2</div>
                                                <small>Nhân viên tạo hóa đơn VAT</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="p-2 bg-white rounded">
                                                <div class="fw-bold text-primary">3</div>
                                                <small>Gửi hóa đơn qua email</small>
                                            </div>
                                        </div>
                                        <div class="col-md-3 text-center">
                                            <div class="p-2 bg-white rounded">
                                                <div class="fw-bold text-success">4</div>
                                                <small>Không thu thêm phí VAT</small>
                                            </div>
                                        </div>
                                    </div>
        </div>
    </div>

                            <div class="text-center">
                                <button class="btn btn-primary btn-lg px-5">
                                    <i class="fas fa-paper-plane me-2"></i>Gửi yêu cầu xuất hóa đơn VAT
                                </button>
                            </div>
                        </form>
                        
                        <script>
                        function toggleVatForm() {
                            const checkbox = document.getElementById('needVatInvoice');
                            const form = document.getElementById('vatForm');
                            form.style.display = checkbox.checked ? 'block' : 'none';
                        }
                        </script>
                    @endif
                </div>
                </div>
            </div>
        </div>
    <!-- Khuyến mại -->
    @if($booking->status == 'pending' || $booking->status == 'confirmed')
        <hr>
        <div class="row">
            <div class="col-12">
                @php
                    $appliedPromotion = null;
                    if ($booking->promotion_id) {
                        $appliedPromotion = [
                            'title' => $booking->promotion->title,
                            'code' => $booking->promotion->code,
                            'discount_amount' => $booking->promotion_discount
                        ];
                    }
                @endphp
                                        @if($appliedPromotion)
                            <x-promotion-form :booking="$booking" :appliedPromotion="$appliedPromotion" />
                        @endif
            </div>
        </div>
    @endif
    
    @php
        $roomType = $booking->room && $booking->room->roomType ? $booking->room->roomType : null;
        $review = \App\Models\RoomTypeReview::where('user_id', auth()->id())
            ->where('booking_id', $booking->id)
            ->first();
        $hasReviewed = !!$review;
    @endphp
    <!-- Đánh giá -->
    <div class="row mb-5">
            <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-star me-3"></i>Đánh Giá</h4>
                </div>
                    <div class="card-body">
                    @if($hasReviewed)
                        <div class="review-display">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <div class="rating-display">
                                    <div class="stars text-warning mb-2">
                                @for ($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }} fs-4"></i>
                                @endfor
                                        <span class="ms-3 fs-5 fw-bold">{{ $review->rating }}/5</span>
                            </div>
                            </div>
                                <div class="review-actions">
                                    <span class="badge fs-6 px-3 py-2 badge-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }}">
                                        <i class="fas fa-circle me-2"></i>{{ $review->status_text }}
                                </span>
                                @if ($review->status === 'pending')
                                        <div class="btn-group mt-2" role="group">
                                            <button type="button" class="btn btn-outline-primary btn-sm edit-review-btn" data-review-id="{{ $review->id }}" title="Chỉnh sửa đánh giá">
                                                <i class="fas fa-edit me-1"></i>Sửa
                                        </button>
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-review-btn" data-review-id="{{ $review->id }}" title="Xóa đánh giá">
                                                <i class="fas fa-trash me-1"></i>Xóa
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                            <div class="review-content p-3 bg-light rounded">
                        @if($review->comment)
                                    <p class="mb-2 fs-6">{{ $review->comment }}</p>
                        @else
                                    <p class="text-muted mb-2"><em>Không có bình luận</em></p>
                        @endif
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>Đánh giá ngày: {{ $review->created_at->format('d/m/Y H:i') }}
                                </small>
            </div>
        </div>
    @else
                        <div class="review-prompt">
                @if ($booking->status == 'completed' && $roomType)
                                <div class="alert alert-info border-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-info-circle fs-4 me-3 text-info"></i>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">Đánh giá dịch vụ</h6>
                                            <p class="mb-2">Bạn có thể đánh giá loại phòng này để giúp chúng tôi cải thiện dịch vụ.</p>
                                            <button class="btn btn-primary create-review-btn" data-room-type-id="{{ $roomType->id }}" data-booking-id="{{ $booking->id }}">
                                                <i class="fas fa-star me-2"></i>Viết đánh giá
                        </button>
                                        </div>
                                    </div>
                    </div>
                @elseif ($booking->status == 'completed' && !$roomType)
                                <div class="alert alert-warning border-0">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Không thể đánh giá:</strong> Thông tin phòng không đầy đủ.
                    </div>
                @else
                                <div class="alert alert-secondary border-0">
                                    <i class="fas fa-clock me-2"></i>
                                    <strong>Chưa thể đánh giá:</strong> Bạn chỉ có thể đánh giá sau khi hoàn thành đặt phòng.
                    </div>
                @endif
            </div>
    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Dịch vụ sử dụng -->
    @if($booking->extra_services_total > 0 || $booking->surcharge > 0)
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-concierge-bell me-3"></i>Dịch vụ sử dụng</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                    @if($booking->extra_services_total > 0)
                            <div class="service-section">
                                <h6 class="text-primary mb-3"><i class="fas fa-plus-circle me-2"></i>Dịch vụ bổ sung</h6>
                        @php
                            $extraSvcs = $booking->extra_services ?? [];
                            if (is_string($extraSvcs)) {
                                $decoded = json_decode($extraSvcs, true);
                                $extraSvcs = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
                            }
                            $svcNames = [];
                            try {
                                $ids = collect($extraSvcs)->pluck('id')->filter()->unique()->values()->all();
                                if (!empty($ids)) {
                                    $svcNames = \App\Models\ExtraService::whereIn('id', $ids)->pluck('name', 'id')->toArray();
                                }
                            } catch (\Throwable $e) { $svcNames = []; }
                        @endphp
                        @if(!empty($extraSvcs) && is_array($extraSvcs))
                            @foreach($extraSvcs as $service)
                                @php
                                    $sid = $service['id'] ?? null;
                                    $name = $sid && isset($svcNames[$sid]) ? $svcNames[$sid] : ($service['name'] ?? 'Dịch vụ');
                                    $subtotal = (float)($service['subtotal'] ?? 0);
                                @endphp
                                        <div class="service-item d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                            <span class="fw-bold">{{ $name }}</span>
                                            <span class="badge badge-primary">{{ number_format($subtotal) }} VNĐ</span>
                                </div>
                            @endforeach
                        @else
                                    <div class="service-item d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                        <span class="fw-bold">Dịch vụ bổ sung</span>
                                        <span class="badge badge-primary">{{ number_format($booking->extra_services_total) }} VNĐ</span>
                            </div>
                        @endif
                    </div>
                            @else
                            <div class="service-section text-center p-4">
                                <i class="fas fa-concierge-bell fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">Không có dịch vụ bổ sung</p>
                    </div>
                    @endif
                    </div>
                    
                        <div class="col-md-6">
                            <div class="service-section">
                    @php $hasCompletedPayment = $booking->hasSuccessfulPayment(); @endphp
                                @if(($booking->total_services_price ?? 0) > 0)
                                <div class="mb-3">
                                    <h6 class="text-warning mb-3"><i class="fas fa-tools me-2"></i>Dịch vụ (Admin thêm)</h6>
                                    @if(!$hasCompletedPayment)
                                        <div class="alert alert-warning border-0">
                                            <i class="fas fa-info-circle me-2"></i>
                                            Các dịch vụ admin thêm sẽ được thanh toán tại quầy
                            </div>
                        @endif
                                    <div class="service-item d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                        <span class="fw-bold">Dịch vụ admin</span>
                                        <span class="badge badge-warning text-dark">{{ number_format($booking->total_services_price) }} VNĐ</span>
                    </div>
                                </div>
                                @endif
                    
                    @if($booking->surcharge > 0)
                    <div class="mb-3">
                                    <h6 class="text-warning mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Phụ phí</h6>
                                    <div class="service-item d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded">
                                        <span class="fw-bold">Phụ phí khách vượt sức chứa</span>
                                        <span class="badge badge-warning text-dark">{{ number_format($booking->surcharge) }} VNĐ</span>
                        </div>
                    </div>
                    @endif
                    
                                @if(($booking->total_services_price ?? 0) == 0 && ($booking->surcharge ?? 0) == 0)
                                <div class="text-center p-4">
                                    <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                    <p class="text-muted mb-0">Không có phụ phí</p>
                        </div>
                                @endif
                    </div>
                </div>
            </div>
                    
                    <hr>
                    <div class="text-center">
                                                <div class="total-service d-inline-block p-3" style="background-color: #C9A888; color: white;">
                            <h6 class="mb-1 text-white">Tổng dịch vụ & phụ phí</h6>
                            <h4 class="mb-0 fw-bold">{{ number_format(($booking->extra_services_total ?? 0) + ($booking->total_services_price ?? 0) + ($booking->surcharge ?? 0)) }} VNĐ</h4>
        </div>
    </div>
    </div>
                            </div>
                    </div>
                </div>
    @endif

    <!-- Ghi chú đặt phòng -->
    <div class="row mb-5">
        <div class="col-12">
            <div class="card border-0 shadow-lg">
                                <div class="card-header" style="background-color: #C9A888; color: white;">
                    <h4 class="mb-0"><i class="fas fa-sticky-note me-3"></i>Ghi chú đặt phòng</h4>
            </div>
                <div class="card-body p-4">
                    <x-booking-notes :booking="$booking" :showAddButton="true" :showSearch="true" />
                    </div>
                </div>
            </div>
        </div>
    </div>

    </div>
    </div>
<style>
.kv-label::after { content: ":"; margin-left: 4px; }
</style>
