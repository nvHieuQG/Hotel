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
                <div class="col-md-8">
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
                                    <div class="col-md-8">
                                        <h6 class="text-dark mb-3">Thông tin đặt phòng</h6>
                                        
                                        <div class="form-group mb-3">
                                            <label for="room_type_id">Loại phòng <span class="text-danger">*</span></label>
                                            <select name="room_type_id" id="room_type_id" class="form-control" required>
                                                <option value="">-- Chọn loại phòng --</option>
                                                @foreach($roomTypes as $type)
                                                    <option value="{{ $type->id }}" data-price="{{ $type->price }}" 
                                                            {{ (isset($bookingData['room_type_id']) && $bookingData['room_type_id'] == $type->id) ? 'selected' : '' }}>
                                                        {{ $type->name }} - {{ number_format($type->price) }}đ/đêm
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

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
                                            <label for="guests">Số khách <span class="text-danger">*</span></label>
                                            <select name="guests" id="guests" class="form-control" required>
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <option value="{{ $i }}" 
                                                            {{ (isset($bookingData['guests']) && $bookingData['guests'] == $i) ? 'selected' : '' }}>
                                                        {{ $i }} người
                                                    </option>
                                                @endfor
                                            </select>
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

                                    <div class="col-md-4">
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
        let currentBooking = null;

        // Xử lý hiển thị ảnh phòng khi chọn loại phòng
        roomTypeSelect.addEventListener('change', function() {
            const selectedRoomTypeId = this.value;
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
        if (roomTypeSelect.value) {
            roomTypeSelect.dispatchEvent(new Event('change'));
        }

        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
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
                    window.location.href = `/payment-method/${data.booking.id}`;
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
    });
    </script>

    @section('styles')
    <style>
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
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
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .btn-lg {
                padding: 10px 20px;
                font-size: 1rem;
            }
        }
    </style>
    @endsection
@endsection 