@extends('client.layouts.master')

@section('title', 'Yêu cầu đổi phòng')

@section('content')
<div class="hero-wrap" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center" data-scrollax-parent="true">
            <div class="col-md-9 ftco-animate text-center" data-scrollax=" properties: { translateY: '70%' }">
                <p class="breadcrumbs" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                    <span class="mr-2"><a href="{{ route('user.bookings') }}">Đặt phòng</a></span>
                    <span>Yêu cầu đổi phòng</span>
                </p>
                <h1 class="mb-3 bread" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">Yêu cầu đổi phòng</h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">Yêu cầu đổi phòng - Booking #{{ $booking->booking_id }}</h4>
                    </div>
                    <div class="card-body">
                        <!-- Thông tin booking hiện tại -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Thông tin booking hiện tại:</h6>
                                <p><strong>Phòng:</strong>({{ $booking->room->roomType->name }})</p>
                                <p><strong>Check-in:</strong> {{ $booking->check_in_date->format('d/m/Y') }}</p>
                                <p><strong>Check-out:</strong> {{ $booking->check_out_date->format('d/m/Y') }}</p>
                                <p><strong>Giá:</strong> {{ number_format($booking->price, 0, ',', '.') }} VNĐ</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Trạng thái:</h6>
                                <span class="badge badge-{{ $booking->status === 'confirmed' ? 'success' : ($booking->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ $booking->status_text }}
                                </span>
                            </div>
                        </div>

                        <hr>

                        <!-- Form yêu cầu đổi phòng -->
                        <form action="{{ route('room-change.store', $booking->id) }}" method="POST" id="roomChangeForm">
                            @csrf
                            
                            <div class="form-group">
                                <label for="new_room_type_id">Chọn loại phòng mới:</label>
                                <select name="new_room_type_id" id="new_room_type_id" class="form-control" required>
                                    <option value="">-- Chọn loại phòng --</option>
                                    @foreach($availableRoomTypes as $roomType)
                                        <option value="{{ $roomType->id }}" 
                                                data-price="{{ $roomType->price ?? 0 }}"
                                                data-available-rooms="{{ $roomType->rooms->count() }}">
                                            {{ $roomType->name }} 
                                            @if($roomType->id === $booking->room->room_type_id)
                                                (Cùng loại - {{ $roomType->rooms->count() }} phòng trống)
                                            @else
                                                ({{ $roomType->rooms->count() }} phòng trống)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('new_room_type_id')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="reason">Lý do đổi phòng:</label>
                                <select name="reason" id="reason" class="form-control">
                                    <option value="">-- Chọn lý do --</option>
                                    <option value="Không hài lòng với phòng hiện tại">Không hài lòng với phòng hiện tại</option>
                                    <option value="Muốn nâng cấp phòng">Muốn nâng cấp phòng</option>
                                    <option value="Phòng có sự cố">Phòng có sự cố</option>
                                    <option value="Muốn đổi view">Muốn đổi view</option>
                                    <option value="Muốn đổi tầng">Muốn đổi tầng</option>
                                    <option value="Lý do khác">Lý do khác</option>
                                </select>
                                @error('reason')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label for="customer_note">Ghi chú thêm (tùy chọn):</label>
                                <textarea name="customer_note" id="customer_note" class="form-control" rows="3" 
                                          placeholder="Mô tả chi tiết lý do đổi phòng..."></textarea>
                                @error('customer_note')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Hiển thị chênh lệch giá -->
                            <div id="priceDifferenceInfo" class="alert" style="display: none;">
                                <h6>Thông tin chênh lệch giá:</h6>
                                <div id="priceDifferenceText"></div>
                            </div>

                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-paper-plane"></i> Gửi yêu cầu đổi phòng
                                </button>
                                <a href="{{ route('booking.detail', $booking->id) }}" class="btn btn-secondary">
                                    <i class="fa fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
$(document).ready(function() {
    // Xử lý khi chọn loại phòng mới
    $('#new_room_type_id').change(function() {
        const newRoomTypeId = $(this).val();
        if (newRoomTypeId) {
            // Gọi API để tính toán chênh lệch giá
            $.ajax({
                url: '{{ route("room-change.calculate-price", $booking->id) }}',
                method: 'POST',
                data: {
                    new_room_type_id: newRoomTypeId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    const priceDiff = response.price_difference;
                    const priceDiffFormatted = response.price_difference_formatted;
                    
                    let alertClass = 'alert-info';
                    let message = '';
                    
                    if (response.is_expensive) {
                        alertClass = 'alert-warning';
                        message = `Phòng mới đắt hơn: +${priceDiffFormatted}`;
                    } else if (response.is_cheaper) {
                        alertClass = 'alert-success';
                        message = `Phòng mới rẻ hơn: ${priceDiffFormatted}`;
                    } else {
                        alertClass = 'alert-info';
                        message = `Không có chênh lệch giá`;
                    }
                    
                    $('#priceDifferenceInfo')
                        .removeClass('alert-warning alert-success alert-info')
                        .addClass(alertClass)
                        .show();
                    $('#priceDifferenceText').html(message);
                },
                error: function() {
                    $('#priceDifferenceInfo').hide();
                }
            });
        } else {
            $('#priceDifferenceInfo').hide();
        }
    });
});
</script>
@endpush
@endsection 