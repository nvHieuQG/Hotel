@extends('client.layouts.master')

@section('title', 'Đặt Phòng')

@section('content')
    <div class="hero-wrap" style="background-image: url('client/images/bg_1.jpg');">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
                <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                    <div class="text">
                        <p class="breadcrumbs mb-2"><span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                            <span>Đặt Phòng</span></p>
                        <h1 class="mb-4 bread">Đặt Phòng</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="ftco-section contact-section bg-light">
        <div class="container">
            <div class="row block-9">
                <div class="col-md-8 mx-auto d-flex">
                    <div class="bg-white p-5 contact-form w-100">
                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <h3 class="mb-4 text-center">Thông Tin Đặt Phòng</h3>

                        <form action="{{ route('booking') }}" method="post">
                            @csrf

                            <div class="form-group">
                                <label for="room_type_id">Chọn Phòng</label>
                                <select name="room_type_id" id="room_type_id" class="form-control" required>
                                    <option value="">-- Chọn loại phòng --</option>
                                    @foreach ($roomTypes as $type)
                                        <option value="{{ $type->id }}" data-price="{{ $type->price }}">
                                            {{ $type->name }} - {{ number_format($type->price) }}đ/đêm
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="check_in_date">Ngày Nhận Phòng (Check-in 14:00PM)</label>
                                        <input type="date" name="check_in_date" id="check_in_date" 
                                               class="form-control" required
                                               min="{{ date('Y-m-d') }}" 
                                               value="{{ old('check_in_date', date('Y-m-d')) }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="check_out_date">Ngày Trả Phòng (Check-out 12:00PM)</label>
                                        <input type="date" name="check_out_date" id="check_out_date" 
                                               class="form-control" required
                                               min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                               value="{{ old('check_out_date', date('Y-m-d', strtotime('+1 day'))) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="guests">Số Lượng Khách</label>
                                <select name="guests" id="guests" class="form-control" required>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}" {{ old('guests') == $i ? 'selected' : '' }}>
                                            {{ $i }} {{ $i > 1 ? 'người' : 'người' }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <!-- Thông tin người đặt phòng -->
                            <div class="card mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-user-edit"></i> Thông Tin Người Đặt Phòng</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Thông Tin Khách Hàng</label>
                                        <input type="text" class="form-control" disabled
                                            value="{{ $user->name }} ({{ $user->email }})">
                                    </div>

                                    <div class="form-group">
                                        <label for="phone">Số Điện Thoại</label>
                                        <input type="text" name="phone" id="phone" class="form-control"
                                            value="{{ $user->phone ?? old('phone') }}" required>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="booker_full_name">Họ Tên Đầy Đủ</label>
                                                <input type="text" name="booker_full_name" id="booker_full_name" 
                                                       class="form-control" value="{{ $user->name }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="booker_id_number">Số Căn Cước/CMND</label>
                                                <input type="text" name="booker_id_number" id="booker_id_number" 
                                                       class="form-control" value="{{ old('booker_id_number') }}" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="booker_phone">Số Điện Thoại</label>
                                                <input type="text" name="booker_phone" id="booker_phone" 
                                                       class="form-control" value="{{ $user->phone ?? old('booker_phone') }}" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="booker_email">Email</label>
                                                <input type="email" name="booker_email" id="booker_email" 
                                                       class="form-control" value="{{ $user->email }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Thông tin khách lưu trú -->
                            <div class="card mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-users"></i> Thông Tin Khách Lưu Trú</h5>
                                    <small>Thông tin này sẽ được sử dụng để làm giấy đăng ký tạm chú tạm vắng</small>
                                </div>
                                <div class="card-body">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" id="same_as_booker" checked>
                                        <label class="form-check-label" for="same_as_booker">
                                            Khách lưu trú giống người đặt phòng
                                        </label>
                                    </div>

                                    <div id="guest-info-section">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="guest_full_name">Họ Tên Đầy Đủ <span class="text-danger">*</span></label>
                                                    <input type="text" name="guest_full_name" id="guest_full_name" 
                                                           class="form-control" value="{{ $user->name }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="guest_id_number">Số Căn Cước/CMND <span class="text-danger">*</span></label>
                                                    <input type="text" name="guest_id_number" id="guest_id_number" 
                                                           class="form-control" value="{{ old('guest_id_number') }}" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="guest_birth_date">Ngày Sinh <span class="text-danger">*</span></label>
                                                    <input type="date" name="guest_birth_date" id="guest_birth_date" 
                                                           class="form-control" value="{{ old('guest_birth_date') }}" required>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="guest_gender">Giới Tính <span class="text-danger">*</span></label>
                                                    <select name="guest_gender" id="guest_gender" class="form-control" required>
                                                        <option value="">-- Chọn giới tính --</option>
                                                        <option value="Nam" {{ old('guest_gender') == 'Nam' ? 'selected' : '' }}>Nam</option>
                                                        <option value="Nữ" {{ old('guest_gender') == 'Nữ' ? 'selected' : '' }}>Nữ</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label for="guest_nationality">Quốc Tịch <span class="text-danger">*</span></label>
                                                    <input type="text" name="guest_nationality" id="guest_nationality" 
                                                           class="form-control" value="{{ old('guest_nationality', 'Việt Nam') }}" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="guest_permanent_address">Địa Chỉ Thường Trú <span class="text-danger">*</span></label>
                                            <textarea name="guest_permanent_address" id="guest_permanent_address" 
                                                      class="form-control" rows="2" required>{{ old('guest_permanent_address') }}</textarea>
                                        </div>

                                        <div class="form-group">
                                            <label for="guest_current_address">Địa Chỉ Tạm Trú</label>
                                            <textarea name="guest_current_address" id="guest_current_address" 
                                                      class="form-control" rows="2">{{ old('guest_current_address') }}</textarea>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="guest_phone">Số Điện Thoại</label>
                                                    <input type="text" name="guest_phone" id="guest_phone" 
                                                           class="form-control" value="{{ $user->phone ?? old('guest_phone') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="guest_email">Email</label>
                                                    <input type="email" name="guest_email" id="guest_email" 
                                                           class="form-control" value="{{ $user->email ?? old('guest_email') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="guest_purpose_of_stay">Mục Đích Lưu Trú</label>
                                                    <select name="guest_purpose_of_stay" id="guest_purpose_of_stay" class="form-control">
                                                        <option value="">-- Chọn mục đích --</option>
                                                        <option value="Du lịch" {{ old('guest_purpose_of_stay') == 'Du lịch' ? 'selected' : '' }}>Du lịch</option>
                                                        <option value="Công tác" {{ old('guest_purpose_of_stay') == 'Công tác' ? 'selected' : '' }}>Công tác</option>
                                                        <option value="Thăm thân" {{ old('guest_purpose_of_stay') == 'Thăm thân' ? 'selected' : '' }}>Thăm thân</option>
                                                        <option value="Khác" {{ old('guest_purpose_of_stay') == 'Khác' ? 'selected' : '' }}>Khác</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="guest_vehicle_number">Biển Số Xe (nếu có)</label>
                                                    <input type="text" name="guest_vehicle_number" id="guest_vehicle_number" 
                                                           class="form-control" value="{{ old('guest_vehicle_number') }}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="guest_notes">Ghi Chú Thêm</label>
                                            <textarea name="guest_notes" id="guest_notes" 
                                                      class="form-control" rows="3">{{ old('guest_notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="notes">Ghi Chú Đặt Phòng</label>
                                <textarea name="notes" id="notes" cols="30" rows="3" class="form-control">{{ old('notes') }}</textarea>
                            </div>

                            <div class="price-calculation bg-light p-3 my-4" style="display: none;">
                                <h5>Chi Tiết Thanh Toán</h5>
                                <div class="d-flex justify-content-between">
                                    <span>Giá phòng:</span>
                                    <span id="room-price">0đ</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Số đêm:</span>
                                    <span id="nights-count">0</span>
                                </div>
                                <div class="d-flex justify-content-between font-weight-bold mt-2">
                                    <span>Tổng tiền:</span>
                                    <span id="total-price">0đ</span>
                                </div>
                            </div>

                            <div class="form-group text-center">
                                <button type="submit" class="btn btn-primary py-3 px-5">Đặt Phòng</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roomSelect = document.getElementById('room_type_id');
            const checkInDateInput = document.getElementById('check_in_date');
            const checkOutDateInput = document.getElementById('check_out_date');
            const priceCalculation = document.querySelector('.price-calculation');
            const roomPriceElement = document.getElementById('room-price');
            const nightsCountElement = document.getElementById('nights-count');
            const totalPriceElement = document.getElementById('total-price');

            // Xử lý checkbox "giống người đặt phòng"
            const sameAsBookerCheckbox = document.getElementById('same_as_booker');
            const guestInfoSection = document.getElementById('guest-info-section');
            
            function toggleGuestInfo() {
                if (sameAsBookerCheckbox.checked) {
                    // Copy thông tin từ người đặt phòng sang khách
                    document.getElementById('guest_full_name').value = document.getElementById('booker_full_name').value;
                    document.getElementById('guest_id_number').value = document.getElementById('booker_id_number').value;
                    document.getElementById('guest_phone').value = document.getElementById('booker_phone').value;
                    document.getElementById('guest_email').value = document.getElementById('booker_email').value;
                    
                    // Disable các trường thông tin khách
                    const guestFields = guestInfoSection.querySelectorAll('input, select, textarea');
                    guestFields.forEach(field => {
                        field.disabled = true;
                    });
                } else {
                    // Enable các trường thông tin khách
                    const guestFields = guestInfoSection.querySelectorAll('input, select, textarea');
                    guestFields.forEach(field => {
                        field.disabled = false;
                    });
                }
            }

            // Xử lý sự kiện checkbox
            sameAsBookerCheckbox.addEventListener('change', toggleGuestInfo);
            
            // Xử lý sự kiện thay đổi thông tin người đặt phòng
            document.getElementById('booker_full_name').addEventListener('input', function() {
                if (sameAsBookerCheckbox.checked) {
                    document.getElementById('guest_full_name').value = this.value;
                }
            });
            
            document.getElementById('booker_id_number').addEventListener('input', function() {
                if (sameAsBookerCheckbox.checked) {
                    document.getElementById('guest_id_number').value = this.value;
                }
            });
            
            document.getElementById('booker_phone').addEventListener('input', function() {
                if (sameAsBookerCheckbox.checked) {
                    document.getElementById('guest_phone').value = this.value;
                }
            });
            
            document.getElementById('booker_email').addEventListener('input', function() {
                if (sameAsBookerCheckbox.checked) {
                    document.getElementById('guest_email').value = this.value;
                }
            });

            // Khởi tạo trạng thái ban đầu
            toggleGuestInfo();

            function updatePriceCalculation() {
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];
                const checkInDate = new Date(checkInDateInput.value);
                const checkOutDate = new Date(checkOutDateInput.value);

                if (roomSelect.value && checkInDateInput.value && checkOutDateInput.value) {
                    const roomPrice = parseFloat(selectedOption.dataset.price);
                    const timeDiff = checkOutDate - checkInDate;
                    const nights = Math.ceil(timeDiff / (1000 * 3600 * 24));

                    if (nights > 0) {
                        const totalPrice = roomPrice * nights;

                        roomPriceElement.textContent = formatCurrency(roomPrice);
                        nightsCountElement.textContent = nights;
                        totalPriceElement.textContent = formatCurrency(totalPrice);

                        priceCalculation.style.display = 'block';
                    } else {
                        priceCalculation.style.display = 'none';
                    }
                } else {
                    priceCalculation.style.display = 'none';
                }
            }

            function formatCurrency(amount) {
                return new Intl.NumberFormat('vi-VN', {
                    maximumFractionDigits: 0
                }).format(amount) + ' VNĐ';
            }

            // Event listeners
            roomSelect.addEventListener('change', updatePriceCalculation);
            checkInDateInput.addEventListener('change', updatePriceCalculation);
            checkOutDateInput.addEventListener('change', updatePriceCalculation);

            // Validation cho ngày check-out
            checkInDateInput.addEventListener('change', function() {
                const checkInDate = new Date(this.value);
                const nextDay = new Date(checkInDate);
                nextDay.setDate(nextDay.getDate() + 1);
                
                checkOutDateInput.min = nextDay.toISOString().split('T')[0];
                
                if (checkOutDateInput.value && new Date(checkOutDateInput.value) <= checkInDate) {
                    checkOutDateInput.value = nextDay.toISOString().split('T')[0];
                }
                
                updatePriceCalculation();
            });

            // Validation cho số căn cước
            function validateIdNumber(input) {
                const idNumber = input.value.replace(/\s/g, '');
                const idPattern = /^[0-9]{9,12}$/;
                
                if (idNumber && !idPattern.test(idNumber)) {
                    input.setCustomValidity('Số căn cước phải có 9-12 chữ số');
                } else {
                    input.setCustomValidity('');
                }
            }

            document.getElementById('booker_id_number').addEventListener('input', function() {
                validateIdNumber(this);
            });
            
            document.getElementById('guest_id_number').addEventListener('input', function() {
                validateIdNumber(this);
            });

            // Validation cho số điện thoại
            function validatePhone(input) {
                const phone = input.value.replace(/\s/g, '');
                const phonePattern = /^[0-9]{10,11}$/;
                
                if (phone && !phonePattern.test(phone)) {
                    input.setCustomValidity('Số điện thoại phải có 10-11 chữ số');
                } else {
                    input.setCustomValidity('');
                }
            }

            document.getElementById('booker_phone').addEventListener('input', function() {
                validatePhone(this);
            });
            
            document.getElementById('guest_phone').addEventListener('input', function() {
                validatePhone(this);
            });

            // Khởi tạo tính giá
            updatePriceCalculation();
        });
    </script>
@endpush
