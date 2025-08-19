@extends('client.layouts.master')

@section('title', 'Chọn phòng cho Tour')

@section('content')
<section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate pb-5 text-center">
                <h1 class="mb-3 bread">Chọn phòng cho Tour</h1>
                <p class="breadcrumbs">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ <i class="ion-ios-arrow-forward"></i></a></span>
                    <span class="mr-2"><a href="{{ route('tour-booking.search') }}">Đặt Tour <i class="ion-ios-arrow-forward"></i></a></span>
                    <span>Chọn phòng</span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <!-- Thông tin tour -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4>Thông tin Tour</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <strong>Tên Tour:</strong> {{ $tourName }}
                            </div>
                            <div class="col-md-3">
                                <strong>Tổng số khách:</strong> {{ $totalGuests }} người
                            </div>
                            <div class="col-md-3">
                                <strong>Check-in:</strong> {{ \Carbon\Carbon::parse($checkInDate)->format('d/m/Y') }}
                            </div>
                            <div class="col-md-3">
                                <strong>Check-out:</strong> {{ \Carbon\Carbon::parse($checkOutDate)->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form id="roomSelectionForm" action="{{ route('tour-booking.confirm') }}" method="POST">
            @csrf
            <input type="hidden" name="tour_name" value="{{ $tourName }}">
            <input type="hidden" name="check_in_date" value="{{ $checkInDate }}">
            <input type="hidden" name="check_out_date" value="{{ $checkOutDate }}">
            <input type="hidden" name="total_guests" value="{{ $totalGuests }}">
            <input type="hidden" name="total_price" id="totalPrice" value="0">

            <div class="row">
                <div class="col-md-8">
                    <!-- Danh sách loại phòng -->
                    <div class="card">
                        <div class="card-header">
                            <h4>Chọn loại phòng</h4>
                            <p class="text-muted">Vui lòng chọn loại phòng và số lượng phù hợp với số khách</p>
                        </div>
                        <div class="card-body">
                            @if(count($availableRoomTypes) > 0)
                                <div id="roomSelections">
                                    @foreach($availableRoomTypes as $roomType)
                                        <div class="room-type-item mb-4 p-3 border rounded">
                                            <div class="row align-items-center">
                                                <div class="col-md-3">
                                                    <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                                        <i class="fas fa-bed fa-3x text-muted"></i>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <h5>{{ $roomType->name }}</h5>
                                                    <p class="text-muted">{{ $roomType->description }}</p>
                                                    <div class="row">
                                                        <div class="col-6">
                                                            <small><strong>Sức chứa:</strong> {{ $roomType->capacity }} người</small>
                                                        </div>
                                                        <div class="col-6">
                                                            <small><strong>Có sẵn:</strong> {{ $roomType->available_rooms }} phòng</small>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2">
                                                        <strong class="text-primary">{{ number_format($roomType->price, 0, ',', '.') }} VNĐ</strong> / đêm
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Số lượng phòng:</label>
                                                        <input type="number" name="room_selections[{{ $roomType->id }}][quantity]" 
                                                               class="form-control room-quantity" min="0" max="{{ $roomType->available_rooms }}" value="0"
                                                               data-room-type-id="{{ $roomType->id }}"
                                                               data-price="{{ $roomType->price }}"
                                                               data-capacity="{{ $roomType->capacity }}">
                                                    </div>
                                                    <div class="form-group mt-2">
                                                        <label>Số khách/phòng:</label>
                                                        <input type="number" name="room_selections[{{ $roomType->id }}][guests_per_room]" 
                                                               class="form-control guests-per-room" min="1" max="{{ $roomType->capacity }}" value="1"
                                                               data-room-type-id="{{ $roomType->id }}">
                                                    </div>
                                                    <input type="hidden" name="room_selections[{{ $roomType->id }}][room_type_id]" value="{{ $roomType->id }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                                    <h5>Không có phòng phù hợp</h5>
                                    <p class="text-muted">Không tìm thấy phòng phù hợp cho yêu cầu của bạn.</p>
                                    <a href="{{ route('tour-booking.search') }}" class="btn btn-primary">Thử lại</a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Tóm tắt đặt phòng -->
                    <div class="card sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h5>Tóm tắt đặt phòng</h5>
                        </div>
                        <div class="card-body">
                            <div id="bookingSummary">
                                <p class="text-muted">Chưa có phòng nào được chọn</p>
                            </div>
                            
                            <hr>
                            
                            <div class="row">
                                <div class="col-6">
                                    <strong>Tổng tiền:</strong>
                                </div>
                                <div class="col-6 text-right">
                                    <strong class="text-primary" id="totalPriceDisplay">0 VNĐ</strong>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-6">
                                    <strong>Tổng phòng:</strong>
                                </div>
                                <div class="col-6 text-right">
                                    <span id="totalRoomsDisplay">0</span>
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-6">
                                    <strong>Tổng khách:</strong>
                                </div>
                                <div class="col-6 text-right">
                                    <span id="totalGuestsDisplay">0</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <button type="submit" class="btn btn-primary btn-block" id="confirmBtn" disabled>
                                    Tiếp tục
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const roomQuantities = document.querySelectorAll('.room-quantity');
    const guestsPerRoom = document.querySelectorAll('.guests-per-room');
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');
    const totalRoomsDisplay = document.getElementById('totalRoomsDisplay');
    const totalGuestsDisplay = document.getElementById('totalGuestsDisplay');
    const totalPriceInput = document.getElementById('totalPrice');
    const confirmBtn = document.getElementById('confirmBtn');
    const bookingSummary = document.getElementById('bookingSummary');

    function updateSummary() {
        let totalPrice = 0;
        let totalRooms = 0;
        let totalGuests = 0;
        let selectedRooms = [];

        roomQuantities.forEach((quantityInput, index) => {
            const quantity = parseInt(quantityInput.value) || 0;
            const guestsPerRoomValue = parseInt(guestsPerRoom[index].value) || 1;
            const roomTypeId = quantityInput.dataset.roomTypeId;
            const price = parseFloat(quantityInput.dataset.price);
            const capacity = parseInt(quantityInput.dataset.capacity);

            if (quantity > 0) {
                const nights = {{ \Carbon\Carbon::parse($checkInDate)->diffInDays(\Carbon\Carbon::parse($checkOutDate)) }};
                const roomPrice = price * nights;
                const totalRoomPrice = roomPrice * quantity;
                
                totalPrice += totalRoomPrice;
                totalRooms += quantity;
                totalGuests += quantity * guestsPerRoomValue;

                selectedRooms.push({
                    roomTypeId: roomTypeId,
                    quantity: quantity,
                    guestsPerRoom: guestsPerRoomValue,
                    pricePerRoom: roomPrice,
                    totalPrice: totalRoomPrice
                });
            }
        });

        // Cập nhật hiển thị
        totalPriceDisplay.textContent = totalPrice.toLocaleString('vi-VN') + ' VNĐ';
        totalRoomsDisplay.textContent = totalRooms;
        totalGuestsDisplay.textContent = totalGuests;
        totalPriceInput.value = totalPrice;

        // Cập nhật tóm tắt
        if (selectedRooms.length > 0) {
            let summaryHtml = '';
            selectedRooms.forEach(room => {
                summaryHtml += `
                    <div class="mb-2">
                        <small>${room.quantity} phòng × ${room.guestsPerRoom} khách</small><br>
                        <small class="text-muted">${room.pricePerRoom.toLocaleString('vi-VN')} VNĐ/phòng</small>
                    </div>
                `;
            });
            bookingSummary.innerHTML = summaryHtml;
        } else {
            bookingSummary.innerHTML = '<p class="text-muted">Chưa có phòng nào được chọn</p>';
        }

        // Kiểm tra điều kiện để enable/disable nút
        const expectedGuests = {{ $totalGuests }};
        if (totalRooms > 0 && totalGuests >= expectedGuests) {
            confirmBtn.disabled = false;
            confirmBtn.textContent = 'Tiếp tục';
        } else {
            confirmBtn.disabled = true;
            if (totalRooms === 0) {
                confirmBtn.textContent = 'Chọn phòng';
            } else if (totalGuests < expectedGuests) {
                confirmBtn.textContent = `Cần thêm ${expectedGuests - totalGuests} khách`;
            }
        }
    }

    // Thêm event listeners
    roomQuantities.forEach(input => {
        input.addEventListener('change', updateSummary);
    });

    guestsPerRoom.forEach(input => {
        input.addEventListener('change', updateSummary);
    });

    // Khởi tạo summary
    updateSummary();
});
</script>

<style>
.room-type-item {
    transition: all 0.3s ease;
}

.room-type-item:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.sticky-top {
    z-index: 1000;
}
</style>
@endsection 