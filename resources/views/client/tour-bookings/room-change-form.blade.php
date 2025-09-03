@extends('client.layouts.master')

@section('title', 'Đổi phòng Tour')

@section('content')
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">
                        <i class="fas fa-exchange-alt"></i>
                        Yêu cầu đổi phòng Tour #{{ $tourBooking->booking_id }}
                    </h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6>Thông tin Tour:</h6>
                            <p><strong>Tour:</strong> {{ $tourBooking->tour_name }}</p>
                            <p><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($tourBooking->check_in_date)->format('d/m/Y') }}</p>
                            <p><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($tourBooking->check_out_date)->format('d/m/Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <h6>Phòng hiện tại:</h6>
                            @foreach($tourBooking->tourBookingRooms as $tbr)
                                @if(!empty($tbr->assigned_room_ids))
                                    @foreach($tbr->assigned_room_ids as $roomId)
                                        @php $room = \App\Models\Room::find($roomId); @endphp
                                        @if($room)
                                            <div class="mb-2">
                                                <span class="badge badge-primary">{{ $room->room_number }}</span>
                                                <small class="text-muted">({{ $room->roomType->name }})</small>
                                            </div>
                                        @endif
                                    @endforeach
                                @endif
                            @endforeach
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tour-room-changes.store', $tourBooking->id) }}">
                        @csrf
                        
                        <div class="form-group">
                            <label for="from_room_id">Chọn phòng muốn đổi:</label>
                            <select name="from_room_id" id="from_room_id" class="form-control @error('from_room_id') is-invalid @enderror" required>
                                <option value="">-- Chọn phòng --</option>
                                @foreach($tourBooking->tourBookingRooms as $tbr)
                                    @if(!empty($tbr->assigned_room_ids))
                                        @foreach($tbr->assigned_room_ids as $roomId)
                                            @php $room = \App\Models\Room::find($roomId); @endphp
                                            @if($room)
                                                <option value="{{ $room->id }}" data-room-type="{{ $room->room_type_id }}">
                                                    {{ $room->room_number }} - {{ $room->roomType->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            </select>
                            @error('from_room_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="to_room_id">Chọn phòng muốn chuyển đến:</label>
                            <select name="to_room_id" id="to_room_id" class="form-control @error('to_room_id') is-invalid @enderror" required>
                                <option value="">-- Chọn phòng --</option>
                            </select>
                            @error('to_room_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="reason">Lý do đổi phòng:</label>
                            <select name="reason" id="reason" class="form-control @error('reason') is-invalid @enderror">
                                <option value="">-- Chọn lý do --</option>
                                <option value="upgrade">Nâng cấp phòng</option>
                                <option value="downgrade">Hạ cấp phòng</option>
                                <option value="preference">Sở thích cá nhân</option>
                                <option value="other">Khác</option>
                            </select>
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="customer_note">Ghi chú thêm:</label>
                            <textarea name="customer_note" id="customer_note" class="form-control @error('customer_note') is-invalid @enderror" rows="3" placeholder="Mô tả chi tiết lý do đổi phòng..."></textarea>
                            @error('customer_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i>
                                <strong>Lưu ý:</strong> Yêu cầu đổi phòng sẽ được xem xét bởi quản trị viên. 
                                Nếu có chênh lệch giá, bạn sẽ được thông báo để thanh toán bổ sung hoặc hoàn tiền.
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-paper-plane"></i>
                                Gửi yêu cầu đổi phòng
                            </button>
                            <a href="{{ route('client.tour-bookings.show', $tourBooking->id) }}" class="btn btn-secondary btn-lg ml-2">
                                <i class="fas fa-arrow-left"></i>
                                Quay lại
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const fromRoomSelect = document.getElementById('from_room_id');
    const toRoomSelect = document.getElementById('to_room_id');

    fromRoomSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const roomTypeId = selectedOption.getAttribute('data-room-type');
        
        // Clear previous options
        toRoomSelect.innerHTML = '<option value="">-- Chọn phòng --</option>';
        
        if (roomTypeId) {
            // Fetch available rooms of the same type
            fetch(`/api/rooms/available-by-type/${roomTypeId}?check_in={{ $tourBooking->check_in_date }}&check_out={{ $tourBooking->check_out_date }}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.rooms) {
                        data.rooms.forEach(room => {
                            const option = document.createElement('option');
                            option.value = room.id;
                            option.textContent = `${room.room_number} - ${room.room_type.name}`;
                            toRoomSelect.appendChild(option);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error fetching rooms:', error);
                });
        }
    });
});
</script>
@endpush
