@extends('client.layouts.master')

@section('title', 'Xác nhận đặt phòng')

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
                            <span>Xác nhận đặt phòng</span>
                        </p>
                        <h3 class="mb-4 bread">Xác nhận thông tin đặt phòng</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="ftco-section bg-light" style="font-family: 'Segoe UI', sans-serif;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-10 col-xl-9">
                    <div class="card shadow-sm rounded-lg">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0 text-white">
                                <i class="fas fa-check-circle mr-2"></i>
                                Xác nhận thông tin đặt phòng
                            </h5>
                        </div>
                        <div class="card-body p-4">
 

                            <form id="booking-confirm-form" action="{{ route('ajax-booking') }}" method="POST">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-8 booking-form-col">
                                        <h6 class="text-dark mb-3">Thông tin đặt phòng</h6>
                                        
                                        <div class="form-group mb-3">
                                            <label for="room_type_id">Loại phòng <span class="text-danger">*</span></label>
                                            <select name="room_type_id" id="room_type_id" class="form-control" required>
                                                <option value="">-- Chọn loại phòng --</option>
                                                @foreach($roomTypes as $type)
                                                    <option value="{{ $type->id }}" data-price="{{ $type->price }}" data-capacity="{{ $type->capacity ?? 1 }}" 
                                                             {{ (isset($bookingData['room_type_id']) && $bookingData['room_type_id'] == $type->id) ? 'selected' : '' }}>
                                                        {{ $type->name }} - {{ number_format($type->price) }}đ/đêm (Tối đa {{ $type->capacity ?? 1 }} khách)
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @if(isset($bookingData['promotion_id']))
                                            <input type="hidden" name="promotion_id" value="{{ (int) $bookingData['promotion_id'] }}">
                                        @endif

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="check_in_date">Nhận phòng <span class="text-danger">*</span></label>
                                                    <input type="date" name="check_in_date" id="check_in_date" class="form-control" required
                                                        min="{{ date('Y-m-d') }}" 
                                                        value="{{ isset($bookingData['check_in_date']) ? $bookingData['check_in_date'] : date('Y-m-d') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="check_out_date">Trả phòng <span class="text-danger">*</span></label>
                                                    <input type="date" name="check_out_date" id="check_out_date" class="form-control" required
                                                        min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                                        value="{{ isset($bookingData['check_out_date']) ? $bookingData['check_out_date'] : date('Y-m-d', strtotime('+1 day')) }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <div class="row">
                                                <div class="col-4">
                                                    <label for="adults" class="small mb-1">Người lớn</label>
                                                    <select name="adults" id="adults" class="form-control" required></select>
                                                </div>
                                                <div class="col-4">
                                                    <label for="children" class="small mb-1">Trẻ em (6-11 tuổi)</label>
                                                    <select name="children" id="children" class="form-control">
                                                        @for ($i = 0; $i <= 2; $i++)
                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                                <div class="col-4">
                                                    <label for="infants" class="small mb-1">Em bé (0-5 tuổi)</label>
                                                    <select name="infants" id="infants" class="form-control">
                                                        @for ($i = 0; $i <= 2; $i++)
                                                            <option value="{{ $i }}">{{ $i }}</option>
                                                        @endfor
                                                    </select>
                                                </div>
                                            </div>
                                            <div id="guest-fee-note" class="mt-2"></div>
                                            <input type="hidden" name="guests" id="guests_hidden" value="{{ isset($bookingData['guests']) ? $bookingData['guests'] : 1 }}">
                                            <input type="hidden" name="total_booking_price" id="total_booking_price" value="0">
                                            <input type="hidden" name="surcharge" id="surcharge" value="0">
                                            <input type="hidden" name="extra_services" id="extra_services" value="[]">
                                            <input type="hidden" name="extra_services_total" id="extra_services_total" value="0">
                                        </div>

                                        
                                        
                                        @if(isset($extraServices) && count($extraServices) > 0)
                                        <div class="form-group mb-3">
                                            <label class="d-block mb-2">Lựa chọn thêm cho kỳ nghỉ của bạn</label>
                                            <div id="extra-services-list" class="border rounded p-2">
                                                @foreach($extraServices as $svc)
                                                    <div class="py-2 border-bottom">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <div class="mr-2" style="min-width:0">
                                                                <div class="font-weight-600 text-dark">{{ $svc->name }}</div>
                                                                @if(!empty($svc->description))
                                                                    <div class="small text-muted text-truncate">{{ $svc->description }}</div>
                                                                @endif
                                                                <div>
                                                                    <span class="small text-muted ml-1">
                                                                        @php $pa = (int)($svc->price_adult ?? 0); $pc = (int)($svc->price_child ?? 0); @endphp
                                                                        @if($svc->charge_type === 'per_person')
                                                                            NL {{ number_format($pa) }}đ @if($pc>0)/ TE {{ number_format($pc) }}đ @endif
                                                                        @else
                                                                            {{ number_format($pa) }}đ
                                                                        @endif
                                                                    </span>
                                                                </div>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input svc-check" id="svc_chk_{{ $svc->id }}"
                                                                           data-service-id="{{ $svc->id }}"
                                                                           data-charge-type="{{ $svc->charge_type }}"
                                                                           data-service-name="{{ $svc->name }}"
                                                                           data-price-adult="{{ (int)($svc->price_adult ?? 0) }}"
                                                                           data-price-child="{{ (int)($svc->price_child ?? 0) }}">
                                                                    <label class="custom-control-label" for="svc_chk_{{ $svc->id }}"></label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Config block (hidden until checked) -->
                                                        <div class="svc-config mt-2" data-service-id="{{ $svc->id }}" style="display:none;">
                                                            @php $ct = strtolower($svc->charge_type); @endphp
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    @if($ct === 'per_person' || $ct === 'per_service')
                                                                        <div class="form-group mb-2">
                                                                            <label class="d-block small mb-1">Chọn số lượng khách sử dụng dịch vụ</label>
                                                                        </div>
                                                                        <div class="form-group mb-0 pl-2">
                                                                            <div class="d-flex align-items-center mb-2">
                                                                                <label class="small mb-0 mr-2" style="width:90px">Người lớn</label>
                                                                                <select class="form-control form-control-sm svc-adults" data-service-id="{{ $svc->id }}" style="max-width:120px">
                                                                                    <option value="0">0</option>
                                                                                </select>
                                                                            </div>
                                                                            <div class="d-flex align-items-center">
                                                                                <label class="small mb-0 mr-2" style="width:90px">Trẻ em</label>
                                                                                <select class="form-control form-control-sm svc-children" data-service-id="{{ $svc->id }}" style="max-width:120px">
                                                                                    <option value="0">0</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                 </div>
                                                                 <div class="col-md-6">
                                                                    <div class="form-group mb-0 svc-quantity-wrap" data-service-id="{{ $svc->id }}" data-charge-type="{{ $ct }}" style="display:none;">
                                                                        @if($ct === 'per_service')
                                                                        @elseif($ct === 'per_day' || $ct === 'per_hour')
                                                                            <div class="form-row">
                                                                                <div class="col-6">
                                                                                    <label class="small">Số lượng</label>
                                                                                    <input type="number" class="form-control form-control-sm svc-quantity" data-service-id="{{ $svc->id }}" min="1" value="1" style="max-width:140px">
                                                                                </div>
                                                                                <div class="col-6">
                                                                                    <label class="small">Số ngày</label>
                                                                                    <input type="number" class="form-control form-control-sm svc-days" data-service-id="{{ $svc->id }}" min="1" value="1" style="max-width:140px">
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                        @if($ct === 'per_night')
                                                                            <div class="small text-muted">Tính theo số đêm lưu trú</div>
                                                                        @endif
                                                                        @if($ct === 'per_person')
                                                                            <div class="small text-muted">Áp dụng cho cả kỳ nghỉ dưỡng của bạn</div>
                                                                        @endif
                                                                    </div>
                                                                 </div>
                                                             </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="mt-2 small text-muted" id="extra-services-note"></div>
                                        </div>
                                        @endif

                                        <!-- Mã khuyến mại -->
                                        <div class="form-group mb-3">
                                            <label for="promotion_code" class="mb-2">
                                                <i class="fas fa-tag text-success mr-2"></i>
                                                Mã giảm giá
                                            </label>
                                            <div class="input-group">
                                                <input type="text" name="promotion_code" id="promotion_code" 
                                                       class="form-control" placeholder="Nhập mã giảm giá (nếu có)"
                                                       value="{{ isset($bookingData['promotion_code']) ? $bookingData['promotion_code'] : '' }}">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-success" id="apply-promotion-btn">
                                                        <i class="fas fa-check"></i> Áp dụng
                                                    </button>
                                                </div>
                                            </div>
                                            <div id="promotion-result" class="mt-2"></div>
                                            <input type="hidden" name="promotion_discount" id="promotion_discount" value="0">
                                            <input type="hidden" name="final_price" id="final_price" value="0">
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="phone">Số điện thoại <span class="text-danger">*</span></label>
                                            <input type="text" name="phone" id="phone" class="form-control" 
                                                   value="{{ isset($bookingData['phone']) ? $bookingData['phone'] : ($user->phone ?? '') }}" required>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label for="notes">Ghi chú</label>
                                            <textarea name="notes" id="notes" cols="30" rows="3" class="form-control" 
                                                      placeholder="Yêu cầu đặc biệt (nếu có)">{{ isset($bookingData['notes']) ? $bookingData['notes'] : '' }}</textarea>
                                        </div>
                                    </div>

                                    <div class="col-md-4 booking-summary-col">
                                        <h6 class="text-dark mb-3">Tóm tắt</h6>
                                        
                                        <!-- Ảnh phòng preview -->
                                        <div class="form-group mb-3">
                                            <div id="room-image-preview" class="room-image-container">
                                                <div class="bg-light rounded d-flex justify-content-center align-items-center" 
                                                     style="height: 150px;">
                                                    <div class="text-center text-muted">
                                                        <i class="fas fa-image fa-2x mb-2"></i>
                                                        <p class="small mb-0">Chọn loại phòng để xem ảnh</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="booking-summary">
                                            <p class="text-muted">Vui lòng điền thông tin để xem tóm tắt</p>
                                        </div>
                                    </div>
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

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        <i class="fas fa-check mr-2"></i>
                                        Xác nhận đặt phòng
                                    </button>
                                </div>
                            </form>

                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const bookingForm = document.getElementById('booking-confirm-form');
        const bookingSummary = document.getElementById('booking-summary');
        const roomTypeSelect = document.getElementById('room_type_id');
        const roomImagePreview = document.getElementById('room-image-preview');
        const adultsSelect = document.getElementById('adults');
        const childrenSelect = document.getElementById('children');
        const infantsSelect = document.getElementById('infants');
        const guestsHidden = document.getElementById('guests_hidden');
        const guestFeeNote = document.getElementById('guest-fee-note');
        const checkInInput = document.getElementById('check_in_date');
        const checkOutInput = document.getElementById('check_out_date');
        let currentBooking = null;
        let lastChanged = null; // 'adults' | 'children' | 'infants'

        function formatCurrency(num) {
            try { return new Intl.NumberFormat('vi-VN', { maximumFractionDigits: 0 }).format(num); } catch (e) { return num; }
        }

        // Populate per-service adults/children dropdowns according to current main selection
        function populateServicePeopleOptionsFor(id) {
            const maxA = Math.max(0, parseInt(document.getElementById('adults')?.value) || 0);
            const maxC = Math.max(0, parseInt(document.getElementById('children')?.value) || 0);
            const list = document.getElementById('extra-services-list');
            if (!list) return;
            const selA = list.querySelector(`.svc-adults[data-service-id="${id}"]`);
            const selC = list.querySelector(`.svc-children[data-service-id="${id}"]`);
            if (selA) {
                const curA = parseInt(selA.value) || 0;
                selA.innerHTML = '';
                for (let i=0;i<=maxA;i++){ const opt=document.createElement('option'); opt.value=String(i); opt.textContent=String(i); selA.appendChild(opt);}            
                selA.value = String(Math.min(curA, maxA));
            }
            if (selC) {
                const curC = parseInt(selC.value) || 0;
                selC.innerHTML = '';
                for (let i=0;i<=maxC;i++){ const opt=document.createElement('option'); opt.value=String(i); opt.textContent=String(i); selC.appendChild(opt);}            
                selC.value = String(Math.min(curC, maxC));
            }
        }

        function populateAllServicePeopleOptions() {
            const list = document.getElementById('extra-services-list');
            if (!list) return;
            list.querySelectorAll('.svc-adults').forEach(el => {
                const id = el.getAttribute('data-service-id');
                if (id) populateServicePeopleOptionsFor(id);
            });
        }

        // Tự động điền số lượng khách cho dịch vụ
        function autoFillServicePeopleFor(id) {
            const list = document.getElementById('extra-services-list');
            if (!list) return;
            
            const adultsInput = list.querySelector(`.svc-adults[data-service-id="${id}"]`);
            const childrenInput = list.querySelector(`.svc-children[data-service-id="${id}"]`);
            
            if (adultsInput) {
                const maxAdults = parseInt(document.getElementById('adults')?.value) || 0;
                adultsInput.value = String(maxAdults);
            }
            
            if (childrenInput) {
                const maxChildren = parseInt(document.getElementById('children')?.value) || 0;
                childrenInput.value = String(maxChildren);
            }
        }

        function formatDateVN(isoDate) {
            if (!isoDate) return '';
            const d = new Date(isoDate);
            const dd = String(d.getDate()).padStart(2, '0');
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const yyyy = d.getFullYear();
            return `${dd}/${mm}/${yyyy}`;
        }

        function getCapacity() {
            const opt = roomTypeSelect.options[roomTypeSelect.selectedIndex];
            const cap = opt ? parseInt(opt.getAttribute('data-capacity')) : 1;
            return isNaN(cap) ? 1 : Math.max(1, cap);
        }

        function populateAdultsOptions() {
            const capacity = getCapacity();
            const prev = parseInt(adultsSelect.value) || 1;
            adultsSelect.innerHTML = '';
            // Chỉ cho phép thêm tối đa 1 người lớn vượt sức chứa nếu capacity <= 3
            const extraAllowed = capacity <= 3 ? 1 : 0;
            // Giới hạn theo sức chứa phòng
            const capacityLimit = capacity + extraAllowed;
            const maxAdults = capacityLimit;
            for (let i = 1; i <= maxAdults; i++) {
                const opt = document.createElement('option');
                opt.value = i;
                opt.textContent = i;
                if (i === Math.min(prev, maxAdults)) opt.selected = true;
                adultsSelect.appendChild(opt);
            }
        }

        function diffNights() {
            const ci = new Date(checkInInput.value);
            const co = new Date(checkOutInput.value);
            const ms = co - ci;
            const nights = Math.max(1, Math.round(ms / (1000*60*60*24)));
            return nights;
        }

        // Ensure per-day service 'days' inputs do not exceed the stay length (nights + 1)
        function syncPerDayMaxDays() {
            const list = document.getElementById('extra-services-list');
            if (!list) return;
            const stayDays = diffNights();
            list.querySelectorAll('.svc-days').forEach(inp => {
                inp.max = String(stayDays);
                const v = parseInt(inp.value) || 1;
                if (v > stayDays) inp.value = String(stayDays);
                if (v < 1) inp.value = '1';
            });
        }

        function calcExtraServicesTotal(adults, children, nights) {
            const list = document.getElementById('extra-services-list');
            if (!list) return { total: 0, items: [] };
            const checks = list.querySelectorAll('.svc-check');
            let total = 0; let items = [];
            checks.forEach(chk => {
                if (!chk.checked) return;
                const id = parseInt(chk.getAttribute('data-service-id'));
                const charge = (chk.getAttribute('data-charge-type') || '').toLowerCase();
                const pa = Math.max(0, parseInt(chk.getAttribute('data-price-adult')) || 0);
                const pc = Math.max(0, parseInt(chk.getAttribute('data-price-child')) || 0);

                // Tự động áp dụng dịch vụ cho tất cả khách khi được chọn
                let selAdults = 0, selChildren = 0;
                // For per_person and per_service, require explicit selection via per-service inputs
                if (charge === 'per_person' || charge === 'per_service') {
                    const inpA = list.querySelector(`.svc-adults[data-service-id="${id}"]`);
                    const inpC = list.querySelector(`.svc-children[data-service-id="${id}"]`);
                    selAdults = Math.max(0, parseInt(inpA?.value) || 0);
                    selChildren = Math.max(0, parseInt(inpC?.value) || 0);
                } else {
                    const inpA = list.querySelector(`.svc-adults[data-service-id="${id}"]`);
                    const inpC = list.querySelector(`.svc-children[data-service-id="${id}"]`);
                    selAdults = Math.max(0, parseInt(inpA?.value) || 0);
                    selChildren = Math.max(0, parseInt(inpC?.value) || 0);
                }

                // Quantity for per_service/per_day (treat legacy 'per_hour' like 'per_day')
                let quantity = 1;
                if (charge === 'per_service' || charge === 'per_day' || charge === 'per_hour') {
                    const q = list.querySelector(`.svc-quantity[data-service-id="${id}"]`);
                    quantity = Math.max(1, parseInt(q?.value) || 1);
                }
                // Number of days for per_day (or legacy per_hour treated as per_day)
                let daysVal = 1;
                if (charge === 'per_day' || charge === 'per_hour') {
                    const dInput = list.querySelector(`.svc-days[data-service-id="${id}"]`);
                    daysVal = Math.max(1, parseInt(dInput?.value) || 1);
                }

                let sub = 0;
                switch (charge) {
                    case 'per_person':
                        // Only calculate after user selects people
                        if ((selAdults + selChildren) > 0) {
                            sub = selAdults * pa + selChildren * pc;
                        } else {
                            sub = 0;
                        }
                        break;
                    case 'per_night':
                        // Per night (per room)
                        sub = pa * nights;
                        break;
                    case 'per_day':
                        // Per day: price * days * quantity
                        sub = pa * daysVal * quantity;
                        break;
                    case 'per_hour':
                        // Legacy support: treat as per_day
                        sub = pa * daysVal * quantity;
                        break;
                    case 'per_service':
                        // Only calculate after user selects people
                        if ((selAdults + selChildren) > 0) {
                            sub = pa * quantity;
                        } else {
                            sub = 0;
                        }
                        break;
                    default:
                        sub = pa * quantity;
                        break;
                }
                total += sub;
                items.push({ id, apply: 'all', adults_used: selAdults, children_used: selChildren, quantity: (charge==='per_service'||charge==='per_day'||charge==='per_hour')?quantity:null, days: (charge==='per_day'||charge==='per_hour')?daysVal:null, charge_type: charge, price_adult: pa, price_child: pc, subtotal: sub });
            });
            return { total, items };
        }

        function updateGuestsAndFees() {
            const capacity = getCapacity();
            let adults = parseInt(adultsSelect.value) || 1;
            let children = parseInt(childrenSelect.value) || 0;
            const infants = parseInt(infantsSelect.value) || 0;

            // Giới hạn tổng khách theo sức chứa để gửi backend: capacity + (capacity <= 3 ? 1 : 0)
            const extraAllowed = capacity <= 3 ? 1 : 0;
            const capacityLimit = capacity + extraAllowed;

            // guests sent to backend = adults + children (em bé free, không tính)
            guestsHidden.value = Math.min(adults + children, capacityLimit);

            // Phụ phí người lớn: chỉ cho phép tối đa +1 người lớn nếu sức chứa <= 3, ngược lại không cho phép thêm
            const extraAdultsAllowed = capacity <= 3 ? 1 : 0;
            const extraAdults = Math.max(0, Math.min(adults - capacity, extraAdultsAllowed));

            // Logic phụ phí trẻ em:
            // - Nếu người lớn đang ở mức tối đa cho phép (capacity + extraAllowed), thu phụ phí cho TẤT CẢ trẻ em đã chọn.
            // - Ngược lại: chỉ thu phụ phí phần trẻ em vượt quá chỗ trống còn lại trong capacity.
            const adultsMaxAllowed = capacity + extraAllowed;
            let childSurchargeCount = 0;
            if (adults >= adultsMaxAllowed) {
                childSurchargeCount = children;
            } else {
                const remaining = Math.max(0, capacity - Math.min(adults, capacity));
                childSurchargeCount = Math.max(0, children - remaining);
            }

            const surchargePerChild = 75000;    // phụ phí trẻ em
            const surchargePerAdult = 150000; // phụ phí người lớn
            const adultSurchargeTotal = extraAdults * surchargePerAdult;
            const childSurchargeTotal = childSurchargeCount * surchargePerChild;
            const surchargeTotal = adultSurchargeTotal + childSurchargeTotal;

            const roomPrice = parseInt(roomTypeSelect.options[roomTypeSelect.selectedIndex]?.getAttribute('data-price')) || 0;
            const nights = diffNights();
            // Phụ thu theo đêm
            const adultSurchargePerNight = extraAdults * surchargePerAdult;
            const childSurchargePerNight = childSurchargeCount * surchargePerChild;
            const totalPerNight = roomPrice + adultSurchargePerNight + childSurchargePerNight;
            // Giá phòng theo đêm đã bao gồm phụ thu * số đêm
            const baseTotal = totalPerNight * nights;

            // Extra services
            // Sync per-day max days before calculating extras
            syncPerDayMaxDays();
            const extras = calcExtraServicesTotal(adults, children, nights);
            const extrasTotal = extras.total;
            const extrasInput = document.getElementById('extra_services');
            const extrasTotalInput = document.getElementById('extra_services_total');
            if (extrasInput) extrasInput.value = JSON.stringify(extras.items);
            if (extrasTotalInput) extrasTotalInput.value = String(extrasTotal);

            const grandTotal = baseTotal + extrasTotal;
            // Cập nhật hidden để backend nhận đúng tổng tiền, giúp đồng bộ với trang bank-transfer
            const totalInput = document.getElementById('total_booking_price');
            if (totalInput) totalInput.value = String(grandTotal);
            // Cập nhật hidden phụ thu (tổng phụ thu theo toàn bộ kỳ lưu trú)
            const surchargeInput = document.getElementById('surcharge');
            const totalSurcharge = (adultSurchargePerNight + childSurchargePerNight) * nights;
            if (surchargeInput) surchargeInput.value = String(totalSurcharge);
            // Chuỗi ngày tháng hiển thị trong khung (đưa vào trong .border)
            const days = nights + 1; // ví dụ: 3 ngày 2 đêm
            const rangeText = `${formatDateVN(checkInInput.value)} - ${formatDateVN(checkOutInput.value)} (${days} ngày ${nights} đêm )`;

            // Hiển thị ghi chú/phụ thu theo định dạng: 
            // "X Người lớn - Y Trẻ em - Z Em bé" + các dòng phụ thu "/đêm" + tổng "/đêm"
            let detailHtml = `
                <div class="summary-date text-muted mb-2">${rangeText}
                <div class="d-flex justify-content-between align-items-center text-secondary text-nowrap summary-row"><span><strong>${adults}</strong> Người lớn - <strong>${children}</strong> Trẻ em - <strong>${infants}</strong> Em bé</span></div>`;
            if (adultSurchargePerNight > 0) {
                detailHtml += `<div class="d-flex justify-content-between align-items-center text-secondary text-nowrap summary-row"><span>Phụ thu người lớn:</span><span class="ml-3">${formatCurrency(adultSurchargePerNight)} VNĐ /đêm</span></div>`;
            }
            if (childSurchargePerNight > 0) {
                detailHtml += `<div class="d-flex justify-content-between align-items-center text-secondary text-nowrap summary-row"><span>Phụ thu trẻ em:</span><span class="ml-3">${formatCurrency(childSurchargePerNight)} VNĐ /đêm</span></div>`;
            }
            // Tổng theo đêm hiển thị ngay trong khối, và đóng khối lại để đảm bảo bố cục
            detailHtml += `<hr class="my-2"><div class="d-flex justify-content-between align-items-center text-dark text-nowrap price-per-night"><strong class="ml-auto">${formatCurrency(totalPerNight)} VNĐ /đêm</strong></div></div>`;
            // Theo yêu cầu: dùng block này thay thế phần tóm tắt bên phải
            guestFeeNote.innerHTML = '';

            // Cập nhật nhanh phần tóm tắt nếu đang hiển thị chỗ trống
            if (bookingSummary && roomPrice > 0) {
                // Dựng danh sách chi tiết dịch vụ đã chọn
                let servicesDetails = '';
                if (extras.items && extras.items.length > 0) {
                    servicesDetails = '<div class="mt-2">';
                    servicesDetails += '<div class="mb-1 text-muted small">Dịch vụ đã chọn</div>';
                    extras.items.forEach(it => {
                        const chkEl = document.getElementById(`svc_chk_${it.id}`);
                        const svcName = (chkEl && chkEl.getAttribute('data-service-name')) ? chkEl.getAttribute('data-service-name') : `Dịch vụ #${it.id}`;
                        const parts = [];
                        if (it.charge_type === 'per_person') {
                            if ((it.adults_used || 0) > 0) parts.push(`${it.adults_used} NL`);
                            if ((it.children_used || 0) > 0) parts.push(`${it.children_used} TE`);
                        }
                        if (it.charge_type === 'per_day' || it.charge_type === 'per_hour') {
                            if ((it.quantity || 0) > 0) parts.push(`x${it.quantity}`);
                        }
                        if (it.charge_type === 'per_service') {
                            if ((it.quantity || 0) > 0) parts.push(`x${it.quantity}`);
                        }
                        if (it.charge_type === 'per_night') {
                            parts.push(`${nights} đêm`);
                        }
                        const detail = parts.length ? ` (${parts.join(' - ')})` : '';
                        servicesDetails += `
                            <div class="d-flex justify-content-between align-items-center text-nowrap">
                                <span>${svcName}${detail}</span>
                                <strong>${formatCurrency(it.subtotal)} VNĐ</strong>
                            </div>
                        `;
                    });
                    // Tổng dịch vụ
                    servicesDetails += `<div class=\"d-flex justify-content-between align-items-center text-nowrap\"><span class=\"font-weight-600\">Tổng dịch vụ</span><strong>${formatCurrency(extrasTotal)} VNĐ</strong></div>`;
                    servicesDetails += '</div>';
                }

                bookingSummary.innerHTML = `
                    ${detailHtml}
                    <hr>
                    ${servicesDetails}
                    <div class="d-flex justify-content-between align-items-center text-nowrap total-line"><span>Tổng cộng</span><strong style="color:#f59e0b">${formatCurrency(grandTotal)} VNĐ</strong></div>
                `;
            }
        }

        // Xử lý hiển thị ảnh phòng khi chọn loại phòng
        roomTypeSelect.addEventListener('change', function() {
            const selectedRoomTypeId = this.value;
            // cập nhật lại lựa chọn người lớn theo sức chứa của loại phòng
            populateAdultsOptions();
            updateGuestsAndFees();
            if (selectedRoomTypeId) {
                // Hiển thị loading
                roomImagePreview.innerHTML = `
                    <div class="bg-light rounded d-flex justify-content-center align-items-center" style="height: 150px;">
                        <div class="text-center text-muted">
                            <i class="fas fa-spinner fa-spin fa-2x mb-2"></i>
                            <p class="small mb-0">Đang tải ảnh...</p>
                        </div>
                    </div>
                `;
                
                // Gọi API để lấy ảnh phòng
                fetch(`/api/room-type/${selectedRoomTypeId}/image`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.image_url) {
                            roomImagePreview.innerHTML = `
                                <img src="${data.image_url}" 
                                     alt="Ảnh phòng" 
                                     class="img-fluid rounded shadow-sm" 
                                     style="height: 150px; width: 100%; object-fit: cover;">
                            `;
                        } else {
                            roomImagePreview.innerHTML = `
                                <div class="bg-light rounded d-flex justify-content-center align-items-center" style="height: 150px;">
                                    <div class="text-center text-muted">
                                        <i class="fas fa-image fa-2x mb-2"></i>
                                        <p class="small mb-0">Chưa có ảnh phòng</p>
                                    </div>
                                </div>
                            `;
                        }
                    })
                    .catch(error => {
                        console.error('Error loading room image:', error);
                        roomImagePreview.innerHTML = `
                            <div class="bg-light rounded d-flex justify-content-center align-items-center" style="height: 150px;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <p class="small mb-0">Chưa có ảnh phòng</p>
                                </div>
                            </div>
                        `;
                    });
            } else {
                // Reset về trạng thái ban đầu
                roomImagePreview.innerHTML = `
                    <div class="bg-light rounded d-flex justify-content-center align-items-center" style="height: 150px;">
                        <div class="text-center text-muted">
                            <i class="fas fa-image fa-2x mb-2"></i>
                            <p class="small mb-0">Chọn loại phòng để xem ảnh</p>
                        </div>
                    </div>
                `;
            }
        });
        
        // Load ảnh phòng nếu đã có loại phòng được chọn
        // Khởi tạo dropdown người lớn theo sức chứa và set mặc định
        populateAdultsOptions();
        // Nếu có loại phòng được chọn thì load ảnh + cập nhật phí
        if (roomTypeSelect.value) {
            roomTypeSelect.dispatchEvent(new Event('change'));
        } else {
            updateGuestsAndFees();
        }
        // Populate service people dropdowns initially
        populateAllServicePeopleOptions();

        // Lắng nghe thay đổi để cập nhật phí và tổng
        adultsSelect.addEventListener('change', function(){ lastChanged = 'adults'; populateAllServicePeopleOptions(); updateGuestsAndFees(); });
        childrenSelect.addEventListener('change', function(){ lastChanged = 'children'; populateAllServicePeopleOptions(); updateGuestsAndFees(); });
        infantsSelect.addEventListener('change', function(){ lastChanged = 'infants'; updateGuestsAndFees(); });
        checkInInput.addEventListener('change', updateGuestsAndFees);
        checkOutInput.addEventListener('change', updateGuestsAndFees);
        // Extra services listeners & behaviors
        const svcList = document.getElementById('extra-services-list');
        // Hàm này không còn cần thiết vì đã xóa checkbox svc-apply

        if (svcList) {
            // Toggle config visibility when checking a service
            svcList.addEventListener('change', function(e){
                const t = e.target;
                if (t && t.classList.contains('svc-check')) {
                    const id = t.getAttribute('data-service-id');
                    const cfg = svcList.querySelector(`.svc-config[data-service-id="${id}"]`);
                    const qtyWrap = svcList.querySelector(`.svc-quantity-wrap[data-service-id="${id}"]`);
                    if (t.checked) {
                        if (cfg) cfg.style.display = '';
                        // Show quantity wrap only if it exists (per_service/per_hour)
                        if (qtyWrap) qtyWrap.style.display = '';
                        // Populate selects for this service
                        populateServicePeopleOptionsFor(id);
                        // Sync per-day days max on show
                        syncPerDayMaxDays();
                        // Tự động điền số lượng khách bằng với số khách đã chọn
                        autoFillServicePeopleFor(id);
                    } else {
                        if (cfg) cfg.style.display = 'none';
                        if (qtyWrap) {
                            const q = qtyWrap.querySelector(`.svc-quantity[data-service-id="${id}"]`);
                            if (q) q.value = '1';
                            const d = qtyWrap.querySelector(`.svc-days[data-service-id="${id}"]`);
                            if (d) d.value = '1';
                            qtyWrap.style.display = (qtyWrap.getAttribute('data-charge-type')==='per_night' || qtyWrap.getAttribute('data-charge-type')==='per_person') ? 'none' : 'none';
                        }
                    }
                    updateGuestsAndFees();
                }
                // Bỏ logic xử lý svc-apply vì đã xóa checkbox này
                // Handle per-service people dropdown changes
                if (t && (t.classList.contains('svc-adults') || t.classList.contains('svc-children'))) {
                    const id = t.getAttribute('data-service-id');
                    const chk = document.getElementById(`svc_chk_${id}`);
                    if (chk && !chk.checked) chk.checked = true;
                    // Clamp to current max based on main selection
                    if (t.classList.contains('svc-adults')) {
                        const maxA = Math.max(0, parseInt(document.getElementById('adults')?.value) || 0);
                        const v = Math.max(0, Math.min(maxA, parseInt(t.value)||0));
                        t.value = String(v);
                    }
                    if (t.classList.contains('svc-children')) {
                        const maxC = Math.max(0, parseInt(document.getElementById('children')?.value) || 0);
                        const v = Math.max(0, Math.min(maxC, parseInt(t.value)||0));
                        t.value = String(v);
                    }
                    updateGuestsAndFees();
                }
            });

            // Inputs inside config
            svcList.addEventListener('input', function(e){
                const t = e.target;
                if (!t) return;
                if (t.classList.contains('svc-adults') || t.classList.contains('svc-children') || t.classList.contains('svc-quantity') || t.classList.contains('svc-days')) {
                    // ensure checkbox is checked when editing
                    const id = t.getAttribute('data-service-id');
                    const chk = document.getElementById(`svc_chk_${id}`);
                    if (chk && !chk.checked) chk.checked = true;
                    // clamp to max
                    if (t.classList.contains('svc-adults')) {
                        const maxA = Math.max(0, parseInt(document.getElementById('adults')?.value) || 0);
                        if ((parseInt(t.value)||0) > maxA) t.value = String(maxA);
                        if ((parseInt(t.value)||0) < 0) t.value = '0';
                    }
                    if (t.classList.contains('svc-children')) {
                        const maxC = Math.max(0, parseInt(document.getElementById('children')?.value) || 0);
                        if ((parseInt(t.value)||0) > maxC) t.value = String(maxC);
                        if ((parseInt(t.value)||0) < 0) t.value = '0';
                    }
                    if (t.classList.contains('svc-quantity')) {
                        if ((parseInt(t.value)||0) < 1) t.value = '1';
                    }
                    if (t.classList.contains('svc-days')) {
                        const stayDays = diffNights() + 1;
                        if ((parseInt(t.value)||0) < 1) t.value = '1';
                        if ((parseInt(t.value)||0) > stayDays) t.value = String(stayDays);
                    }
                    updateGuestsAndFees();
                }
            });
        }

        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            // Đảm bảo các hidden inputs (tổng tiền, phụ thu, tổng khách) đã được cập nhật mới nhất
            updateGuestsAndFees();
            const formData = new FormData(bookingForm);
            
            // Disable submit button
            const submitButton = bookingForm.querySelector('button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';
            
            fetch(bookingForm.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Lưu booking hiện tại
                    currentBooking = data.booking;
                    
                    // Lưu vào localStorage
                    localStorage.setItem(`booking_${data.booking.id}`, JSON.stringify(data.booking));
                    
                    // Render lại tóm tắt booking
                    let b = data.booking;
                    let u = data.user;
                    let r = data.roomType;
                    let roomImage = data.roomImage || null;
                    
                    let imageHtml = '';
                    if (roomImage) {
                        imageHtml = `<img src="${roomImage}" alt="Ảnh phòng" class="img-fluid rounded shadow-sm" style="max-height: 200px; width: 100%; object-fit: cover;">`;
                    } else {
                        imageHtml = `
                            <div class="bg-light rounded d-flex justify-content-center align-items-center" style="height: 200px;">
                                <div class="text-center text-muted">
                                    <i class="fas fa-image fa-2x mb-2"></i>
                                    <p class="small">Chưa có ảnh phòng</p>
                                </div>
                            </div>
                        `;
                    }
                    
                    bookingSummary.innerHTML = `
                        <div class="mb-3">
                            ${imageHtml}
                        </div>
                        <div class="mb-3"><strong>${r ? r.name : ''}</strong></div>
                        <div class="row mb-3 align-items-center">
                            <div class="col-5">
                                <p class="mb-1 text-muted small">Nhận phòng</p>
                                <strong class="text-dark">${b.check_in_date}</strong><br>
                                <span class="small text-muted">Từ 14:00</span>
                            </div>
                            <div class="col-2 text-center">
                                <i class="fas fa-arrow-right text-muted"></i>
                            </div>
                            <div class="col-5 text-right">
                                <p class="mb-1 text-muted small">Trả phòng</p>
                                <strong class="text-dark">${b.check_out_date}</strong><br>
                                <span class="small text-muted">Trước 12:00</span>
                            </div>
                        </div>
                        <hr class="my-3">
                        <div class="mb-2">
                            <p class="font-weight-bold">(1x) ${r ? r.name : ''}</p>
                            <ul class="list-unstyled mt-2 mb-0 text-muted small">
                                <li><i class="fas fa-users mr-1 text-secondary"></i> ${r ? r.capacity : ''} khách</li>
                                <li><i class="fas fa-utensils mr-1 text-secondary"></i> Gồm bữa sáng</li>
                                <li><i class="fas fa-wifi mr-1 text-secondary"></i> Wifi miễn phí</li>
                            </ul>
                        </div>
                        <div class="mt-3">
                            <p class="font-weight-bold">Yêu cầu đặc biệt (nếu có)</p>
                            <p class="text-muted small">-</p>
                        </div>
                        <hr class="my-3">
                        <div class="mb-1">
                            <p class="mb-1 text-muted small">Tên khách</p>
                            <strong class="text-dark">${u.name}</strong>
                        </div>
                        <hr class="my-3">
                        <div class="mb-1">
                            <p class="mb-1 text-muted small">Chi tiết người liên lạc</p>
                            <strong class="text-dark">${u.name}</strong>
                            <p class="text-dark mb-0"><i class="fas fa-phone-alt mr-1"></i> ${u.phone ?? '+84XXXXXXXXX'}</p>
                            <p class="text-dark mb-0"><i class="fas fa-envelope mr-1"></i> ${u.email}</p>
                        </div>
                        <div class="mt-4 p-3 text-white rounded" style="background-color: #72c02c;">
                            <p class="mb-0 text-center">Sự lựa chọn tuyệt vời cho kỳ nghỉ của bạn!</p>
                        </div>
                    `;
                    
                    // Hiển thị thông báo thành công và chuyển hướng
                    alert('Đặt phòng thành công! Bạn có 30 phút để hoàn tất thanh toán.');
                    
                    // Chuyển đến trang payment-method
                    // Chuyển đến trang payment-method, truyền tiếp promotion_id nếu có
                    const url = new URL(`/payment-method/${data.booking.id}`, window.location.origin);
                    @if(isset($bookingData['promotion_id']))
                        url.searchParams.set('promotion_id', '{{ (int) $bookingData['promotion_id'] }}');
                    @endif
                    window.location.href = url.pathname + (url.search ? '?' + url.searchParams.toString() : '');
                } else {
                    alert('Có lỗi khi lưu đặt phòng: ' + (data.message || 'Lỗi không xác định'));
                    resetSubmitButton();
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Có lỗi xảy ra khi lưu đặt phòng!');
                resetSubmitButton();
            });
        });

        function resetSubmitButton() {
            const submitButton = bookingForm.querySelector('button[type="submit"]');
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="fas fa-check mr-2"></i>Xác nhận đặt phòng';
        }

        // Xử lý mã khuyến mại
        document.getElementById('apply-promotion-btn').addEventListener('click', function() {
            const promotionCode = document.getElementById('promotion_code').value.trim();
            if (!promotionCode) {
                alert('Vui lòng nhập mã khuyến mại!');
                return;
            }

            // Lấy tổng tiền hiện tại
            const totalPrice = parseFloat(document.getElementById('total_booking_price').value) || 0;
            if (totalPrice <= 0) {
                alert('Vui lòng chọn phòng và ngày để tính toán giá trước khi áp dụng mã khuyến mại!');
                return;
            }

            // Disable button và hiển thị loading
            const button = this;
            const originalText = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang kiểm tra...';

            // Gọi API kiểm tra mã khuyến mại
            fetch('/promotion/check', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    code: promotionCode,
                    amount: totalPrice
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Cập nhật UI
                    document.getElementById('promotion_discount').value = data.discount_amount;
                    document.getElementById('final_price').value = data.final_price;
                    
                    // Hiển thị kết quả
                    document.getElementById('promotion-result').innerHTML = `
                        <div class="alert alert-success py-2 px-3 mb-0">
                            <i class="fas fa-check-circle mr-2"></i>
                            <strong>Mã khuyến mại hợp lệ!</strong><br>
                            <small>Giảm giá: <span class="text-success font-weight-bold">-${data.discount_formatted}</span><br>
                            Giá cuối: <span class="text-danger font-weight-bold">${data.final_price_formatted} VNĐ</span></small>
                        </div>
                    `;

                    // Cập nhật tổng tiền hiển thị
                    updateTotalPriceDisplay();
                } else {
                    // Hiển thị lỗi
                    document.getElementById('promotion-result').innerHTML = `
                        <div class="alert alert-danger py-2 px-3 mb-0">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            ${data.message}
                        </div>
                    `;
                    
                    // Reset hidden fields
                    document.getElementById('promotion_discount').value = 0;
                    document.getElementById('final_price').value = 0;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('promotion-result').innerHTML = `
                    <div class="alert alert-danger py-2 px-3 mb-0">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Có lỗi xảy ra khi kiểm tra mã khuyến mại!
                    </div>
                `;
            })
            .finally(() => {
                // Restore button
                button.disabled = false;
                button.innerHTML = originalText;
            });
        });

        // Hàm cập nhật hiển thị tổng tiền
        function updateTotalPriceDisplay() {
            const totalPrice = parseFloat(document.getElementById('total_booking_price').value) || 0;
            const discount = parseFloat(document.getElementById('promotion_discount').value) || 0;
            const finalPrice = parseFloat(document.getElementById('final_price').value) || totalPrice;
            
            // Cập nhật hiển thị trong booking summary nếu có
            const summaryElement = document.querySelector('#booking-summary .summary-row:last-child');
            if (summaryElement && discount > 0) {
                summaryElement.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Tổng cộng:</span>
                        <div class="text-right">
                            <div class="text-muted text-decoration-line-through small">${numberFormat(totalPrice)} VNĐ</div>
                            <div class="text-danger font-weight-bold">${numberFormat(finalPrice)} VNĐ</div>
                        </div>
                    </div>
                `;
            }
        }

        // Hàm format số
        function numberFormat(num) {
            return new Intl.NumberFormat('vi-VN').format(num);
        }
    });
    </script>

    @push('styles')
    <link rel="stylesheet" href="{{ asset('client/css/pages/booking-confirm.css') }}">
    @endpush
@endsection 