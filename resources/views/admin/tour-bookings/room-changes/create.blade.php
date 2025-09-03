@extends('admin.layouts.admin-master')

@section('title', 'Tạo yêu cầu đổi phòng Tour')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Tạo yêu cầu đổi phòng Tour #{{ $tourBooking->booking_id }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('staff.admin.tour-bookings.room-changes.index', $tourBooking->id) }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <!-- Thông tin tour booking -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Thông tin Tour</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Mã booking:</strong> {{ $tourBooking->booking_id }}</p>
                                    <p><strong>Tên tour:</strong> {{ $tourBooking->tour_name }}</p>
                                    <p><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($tourBooking->check_in_date)->format('d/m/Y') }}</p>
                                    <p><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($tourBooking->check_out_date)->format('d/m/Y') }}</p>
                                    <p><strong>Số đêm:</strong> {{ \Carbon\Carbon::parse($tourBooking->check_in_date)->diffInDays(\Carbon\Carbon::parse($tourBooking->check_out_date)) }} đêm</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-bed"></i> Phòng đã gán</h6>
                                </div>
                                <div class="card-body">
                                    @if(count($assignedRooms) > 0)
                                        @foreach($assignedRooms as $room)
                                            <span class="badge badge-primary mr-1 mb-1">{{ $room->room_number }} ({{ $room->roomType->name }})</span>
                                        @endforeach
                                    @else
                                        <p class="text-muted">Chưa có phòng nào được gán</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form tạo yêu cầu đổi phòng -->
                    <form method="POST" action="{{ route('staff.admin.tour-bookings.room-changes.store', $tourBooking->id) }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="from_room_id" class="text-danger"><i class="fas fa-arrow-left"></i> Từ phòng (hiện tại):</label>
                                    <select name="from_room_id" id="from_room_id" class="form-control @error('from_room_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn phòng hiện tại --</option>
                                        @foreach($assignedRooms as $room)
                                            <option value="{{ $room->id }}" data-room-type="{{ $room->roomType->id }}" data-price="{{ $room->roomType->price }}">
                                                {{ $room->room_number }} - {{ $room->roomType->name }} ({{ number_format($room->roomType->price) }} VNĐ/đêm)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('from_room_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="to_room_id" class="text-success"><i class="fas fa-arrow-right"></i> Đến phòng (mong muốn):</label>
                                    <select name="to_room_id" id="to_room_id" class="form-control @error('to_room_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn phòng mong muốn --</option>
                                        @foreach($availableRooms as $room)
                                            <option value="{{ $room->id }}" data-room-type="{{ $room->roomType->id }}" data-price="{{ $room->roomType->price }}">
                                                {{ $room->room_number }} - {{ $room->roomType->name }} ({{ number_format($room->roomType->price) }} VNĐ/đêm)
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('to_room_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Hiển thị chênh lệch giá -->
                        <div id="price-difference" class="alert alert-info" style="display: none;">
                            <h6><i class="fas fa-calculator"></i> Chênh lệch giá:</h6>
                            <div id="price-details"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="reason"><i class="fas fa-exclamation-triangle"></i> Lý do đổi phòng: <span class="text-danger">*</span></label>
                                    <select name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror" required>
                                        <option value="">-- Chọn lý do --</option>
                                        <option value="Phòng không đúng yêu cầu">Phòng không đúng yêu cầu</option>
                                        <option value="Phòng có vấn đề kỹ thuật">Phòng có vấn đề kỹ thuật</option>
                                        <option value="Khách hàng yêu cầu">Khách hàng yêu cầu</option>
                                        <option value="Nâng cấp phòng">Nâng cấp phòng</option>
                                        <option value="Lý do khác">Lý do khác</option>
                                    </select>
                                    @error('reason')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_note"><i class="fas fa-comment"></i> Ghi chú thêm:</label>
                                    <textarea name="customer_note" id="customer_note" class="form-control @error('customer_note') is-invalid @enderror" rows="3" placeholder="Ghi chú thêm về yêu cầu đổi phòng...">{{ old('customer_note') }}</textarea>
                                    @error('customer_note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i> Gửi yêu cầu đổi phòng
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromRoomSelect = document.getElementById('from_room_id');
    const toRoomSelect = document.getElementById('to_room_id');
    const priceDifferenceDiv = document.getElementById('price-difference');
    const priceDetailsDiv = document.getElementById('price-details');
    
    const nights = {{ \Carbon\Carbon::parse($tourBooking->check_in_date)->diffInDays(\Carbon\Carbon::parse($tourBooking->check_out_date)) }};
    
    function calculatePriceDifference() {
        const fromOption = fromRoomSelect.selectedOptions[0];
        const toOption = toRoomSelect.selectedOptions[0];
        
        if (fromOption && toOption && fromOption.value && toOption.value) {
            const fromPrice = parseInt(fromOption.dataset.price);
            const toPrice = parseInt(toOption.dataset.price);
            const difference = (toPrice - fromPrice) * nights;
            
            priceDifferenceDiv.style.display = 'block';
            
            if (difference > 0) {
                priceDetailsDiv.innerHTML = `
                    <div class="text-warning">
                        <strong><i class="fas fa-arrow-up"></i> Cần thanh toán thêm: +${difference.toLocaleString()} VNĐ</strong>
                        <br><small>(${toPrice.toLocaleString()} - ${fromPrice.toLocaleString()}) × ${nights} đêm</small>
                    </div>
                `;
            } else if (difference < 0) {
                priceDetailsDiv.innerHTML = `
                    <div class="text-success">
                        <strong><i class="fas fa-arrow-down"></i> Được hoàn tiền: ${difference.toLocaleString()} VNĐ</strong>
                        <br><small>(${toPrice.toLocaleString()} - ${fromPrice.toLocaleString()}) × ${nights} đêm</small>
                    </div>
                `;
            } else {
                priceDetailsDiv.innerHTML = `
                    <div class="text-info">
                        <strong><i class="fas fa-equals"></i> Không có chênh lệch giá</strong>
                        <br><small>Cùng loại phòng</small>
                    </div>
                `;
            }
        } else {
            priceDifferenceDiv.style.display = 'none';
        }
    }
    
    fromRoomSelect.addEventListener('change', calculatePriceDifference);
    toRoomSelect.addEventListener('change', calculatePriceDifference);
    
    // Tính toán ban đầu nếu có giá trị cũ
    calculatePriceDifference();
});
</script>
@endsection
