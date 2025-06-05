@extends('admin.layouts.admin-master')

@section('title', 'Chỉnh sửa đặt phòng')

@section('header', 'Chỉnh sửa đặt phòng')

@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold">Phòng #{{ $booking->booking_id }}</h6>
        <div>
            <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-sm btn-info">
                <i class="fas fa-eye"></i> Xem chi tiết
            </a>
            <a href="{{ route('admin.bookings.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="user_id" class="form-label">Khách hàng</label>
                    <select name="user_id" id="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                        <option value="">-- Chọn khách hàng --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ (old('user_id', $booking->user_id) == $user->id) ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="room_id" class="form-label">Phòng</label>
                    <select name="room_id" id="room_id" class="form-select @error('room_id') is-invalid @enderror" required>
                        <option value="">-- Chọn phòng --</option>
                        @foreach($rooms as $room)
                            <option value="{{ $room->id }}" {{ (old('room_id', $booking->room_id) == $room->id) ? 'selected' : '' }}>
                                {{ $room->name }} - {{ number_format($room->price, 0, ',', '.') }} VNĐ/đêm
                            </option>
                        @endforeach
                    </select>
                    @error('room_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="check_in_date" class="form-label">Ngày nhận phòng</label>
                    <input type="date" name="check_in_date" id="check_in_date" class="form-control @error('check_in_date') is-invalid @enderror" value="{{ old('check_in_date', $booking->check_in_date->format('Y-m-d')) }}" required>
                    @error('check_in_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label for="check_out_date" class="form-label">Ngày trả phòng</label>
                    <input type="date" name="check_out_date" id="check_out_date" class="form-control @error('check_out_date') is-invalid @enderror" value="{{ old('check_out_date', $booking->check_out_date->format('Y-m-d')) }}" required>
                    @error('check_out_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="pending" {{ (old('status', $booking->status) == 'pending') ? 'selected' : '' }}>Chờ xác nhận</option>
                        <option value="confirmed" {{ (old('status', $booking->status) == 'confirmed') ? 'selected' : '' }}>Đã xác nhận</option>
                        <option value="completed" {{ (old('status', $booking->status) == 'completed') ? 'selected' : '' }}>Hoàn thành</option>
                        <option value="cancelled" {{ (old('status', $booking->status) == 'cancelled') ? 'selected' : '' }}>Đã hủy</option>
                        <option value="no-show" {{ (old('status', $booking->status) == 'no-show') ? 'selected' : '' }}>Không đến</option>
                    </select>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Tổng tiền hiện tại</label>
                    <div class="form-control bg-light">{{ number_format($booking->price, 0, ',', '.') }} VNĐ</div>
                    <small class="text-muted">Tổng tiền sẽ được tính lại nếu thay đổi phòng hoặc ngày</small>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Kiểm tra ngày trả phải sau ngày nhận
    document.getElementById('check_in_date').addEventListener('change', function() {
        const checkInDate = new Date(this.value);
        const checkOutInput = document.getElementById('check_out_date');
        const checkOutDate = new Date(checkOutInput.value);
        
        if (checkOutDate <= checkInDate) {
            const nextDay = new Date(checkInDate);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutInput.value = nextDay.toISOString().split('T')[0];
        }
    });
</script>
@endsection 