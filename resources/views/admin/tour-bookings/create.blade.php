@extends('admin.layouts.admin-master')

@section('title', 'Tạo Tour Booking mới')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col">
                <h1 class="page-title">Tạo Tour Booking mới</h1>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.tour-bookings.index') }}">Tour Bookings</a></li>
                        <li class="breadcrumb-item active">Tạo mới</li>
                    </ol>
                </nav>
            </div>
            <div class="col-auto">
                <a href="{{ route('admin.tour-bookings.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus text-success me-2"></i>
                        Thông tin Tour Booking
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.tour-bookings.store') }}" method="POST" id="createTourBookingForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_id" class="form-label">Khách hàng <span class="text-danger">*</span></label>
                                    <select class="form-select @error('user_id') is-invalid @enderror" id="user_id" name="user_id" required>
                                        <option value="">Chọn khách hàng</option>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                                {{ $user->name }} ({{ $user->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('user_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tour_name" class="form-label">Tên Tour <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('tour_name') is-invalid @enderror" 
                                           id="tour_name" name="tour_name" value="{{ old('tour_name') }}" 
                                           placeholder="Nhập tên tour..." required>
                                    @error('tour_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_in_date" class="form-label">Ngày Check-in <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('check_in_date') is-invalid @enderror" 
                                           id="check_in_date" name="check_in_date" 
                                           value="{{ old('check_in_date') }}" 
                                           min="{{ date('Y-m-d') }}" required>
                                    @error('check_in_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="check_out_date" class="form-label">Ngày Check-out <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('check_out_date') is-invalid @enderror" 
                                           id="check_out_date" name="check_out_date" 
                                           value="{{ old('check_out_date') }}" required>
                                    @error('check_out_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="total_guests" class="form-label">Tổng số khách <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('total_guests') is-invalid @enderror" 
                                           id="total_guests" name="total_guests" 
                                           value="{{ old('total_guests') }}" min="1" 
                                           placeholder="Nhập số khách..." required>
                                    @error('total_guests')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                        <option value="pending" {{ old('status') === 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                                        <option value="confirmed" {{ old('status') === 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="special_requests" class="form-label">Yêu cầu đặc biệt</label>
                            <textarea class="form-control @error('special_requests') is-invalid @enderror" 
                                      id="special_requests" name="special_requests" rows="3" 
                                      placeholder="Nhập yêu cầu đặc biệt nếu có...">{{ old('special_requests') }}</textarea>
                            @error('special_requests')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Room Selections -->
                        <div class="mb-4">
                            <h6 class="fw-bold mb-3">
                                <i class="fas fa-bed text-primary me-2"></i>
                                Chọn phòng
                            </h6>
                            
                            <div id="roomSelections">
                                <div class="room-selection-item border rounded p-3 mb-3">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label class="form-label">Loại phòng <span class="text-danger">*</span></label>
                                                <select class="form-select room-type-select" name="room_selections[0][room_type_id]" required>
                                                    <option value="">Chọn loại phòng</option>
                                                    @foreach($roomTypes as $roomType)
                                                        <option value="{{ $roomType->id }}" data-price="{{ $roomType->price }}">
                                                            {{ $roomType->name }} - {{ number_format($roomType->price, 0, ',', '.') }} VNĐ/đêm
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control room-quantity" 
                                                       name="room_selections[0][quantity]" 
                                                       value="1" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label class="form-label">Số khách/phòng <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control room-guests" 
                                                       name="room_selections[0][guests_per_room]" 
                                                       value="2" min="1" required>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="mb-3">
                                                <label class="form-label">&nbsp;</label>
                                                <button type="button" class="btn btn-danger btn-sm w-100 remove-room" 
                                                        onclick="removeRoom(this)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <button type="button" class="btn btn-success btn-sm" onclick="addRoom()">
                                <i class="fas fa-plus"></i> Thêm loại phòng
                            </button>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="{{ route('admin.tour-bookings.index') }}" class="btn btn-secondary me-md-2">
                                <i class="fas fa-times"></i> Hủy
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Tạo Tour Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Hướng dẫn -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle text-info me-2"></i>
                        Hướng dẫn
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6 class="fw-bold">Tạo Tour Booking:</h6>
                        <ol class="small">
                            <li>Chọn khách hàng từ danh sách</li>
                            <li>Nhập tên tour và thông tin cơ bản</li>
                            <li>Chọn ngày check-in và check-out</li>
                            <li>Nhập tổng số khách</li>
                            <li>Chọn loại phòng và số lượng</li>
                            <li>Nhập số khách mỗi phòng</li>
                            <li>Thêm yêu cầu đặc biệt nếu cần</li>
                        </ol>
                    </div>
                    
                    <div class="mb-3">
                        <h6 class="fw-bold">Lưu ý:</h6>
                        <ul class="small text-muted">
                            <li>Ngày check-out phải sau ngày check-in</li>
                            <li>Tổng số khách phải bằng tổng (số lượng × số khách/phòng)</li>
                            <li>Giá phòng được tính tự động dựa trên số đêm</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Thống kê nhanh -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-calculator text-warning me-2"></i>
                        Thống kê nhanh
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h4 class="text-primary mb-1" id="totalGuestsDisplay">0</h4>
                                <small class="text-muted">Tổng khách</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <h4 class="text-success mb-1" id="totalRoomsDisplay">0</h4>
                            <small class="text-muted">Tổng phòng</small>
                        </div>
                    </div>
                    <hr>
                    <div class="text-center">
                        <h4 class="text-info mb-1" id="totalNightsDisplay">0</h4>
                        <small class="text-muted">Số đêm</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
let roomIndex = 1;

// Add new room selection
function addRoom() {
    const roomSelections = document.getElementById('roomSelections');
    const newRoom = document.createElement('div');
    newRoom.className = 'room-selection-item border rounded p-3 mb-3';
    
    newRoom.innerHTML = `
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label class="form-label">Loại phòng <span class="text-danger">*</span></label>
                    <select class="form-select room-type-select" name="room_selections[${roomIndex}][room_type_id]" required>
                        <option value="">Chọn loại phòng</option>
                        @foreach($roomTypes as $roomType)
                            <option value="{{ $roomType->id }}" data-price="{{ $roomType->price }}">
                                {{ $roomType->name }} - {{ number_format($roomType->price, 0, ',', '.') }} VNĐ/đêm
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Số lượng <span class="text-danger">*</span></label>
                    <input type="number" class="form-control room-quantity" 
                           name="room_selections[${roomIndex}][quantity]" 
                           value="1" min="1" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label class="form-label">Số khách/phòng <span class="text-danger">*</span></label>
                    <input type="number" class="form-control room-guests" 
                           name="room_selections[${roomIndex}][guests_per_room]" 
                           value="2" min="1" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="button" class="btn btn-danger btn-sm w-100 remove-room" 
                            onclick="removeRoom(this)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
    
    roomSelections.appendChild(newRoom);
    roomIndex++;
    
    // Add event listeners to new room
    addRoomEventListeners(newRoom);
}

// Remove room selection
function removeRoom(button) {
    if (document.querySelectorAll('.room-selection-item').length > 1) {
        button.closest('.room-selection-item').remove();
        updateStatistics();
    }
}

// Add event listeners to room elements
function addRoomEventListeners(roomElement) {
    const quantityInput = roomElement.querySelector('.room-quantity');
    const guestsInput = roomElement.querySelector('.room-guests');
    
    quantityInput.addEventListener('input', updateStatistics);
    guestsInput.addEventListener('input', updateStatistics);
}

// Update statistics
function updateStatistics() {
    let totalGuests = 0;
    let totalRooms = 0;
    
    document.querySelectorAll('.room-selection-item').forEach(room => {
        const quantity = parseInt(room.querySelector('.room-quantity').value) || 0;
        const guests = parseInt(room.querySelector('.room-guests').value) || 0;
        
        totalGuests += quantity * guests;
        totalRooms += quantity;
    });
    
    document.getElementById('totalGuestsDisplay').textContent = totalGuests;
    document.getElementById('totalRoomsDisplay').textContent = totalRooms;
    
    // Calculate total nights
    const checkInDate = document.getElementById('check_in_date').value;
    const checkOutDate = document.getElementById('check_out_date').value;
    
    if (checkInDate && checkOutDate) {
        const checkIn = new Date(checkInDate);
        const checkOut = new Date(checkOutDate);
        const timeDiff = checkOut.getTime() - checkIn.getTime();
        const daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        
        if (daysDiff > 0) {
            document.getElementById('totalNightsDisplay').textContent = daysDiff;
        } else {
            document.getElementById('totalNightsDisplay').textContent = '0';
        }
    }
}

// Validate check-out date must be after check-in date
document.getElementById('check_out_date').addEventListener('change', function() {
    const checkInDate = document.getElementById('check_in_date').value;
    const checkOutDate = this.value;
    
    if (checkInDate && checkOutDate && checkOutDate <= checkInDate) {
        this.setCustomValidity('Ngày check-out phải sau ngày check-in');
        this.classList.add('is-invalid');
    } else {
        this.setCustomValidity('');
        this.classList.remove('is-invalid');
        updateStatistics();
    }
});

// Validate check-in date must be before check-out date
document.getElementById('check_in_date').addEventListener('change', function() {
    const checkInDate = this.value;
    const checkOutDate = document.getElementById('check_out_date').value;
    
    if (checkInDate && checkOutDate && checkOutDate <= checkInDate) {
        document.getElementById('check_out_date').setCustomValidity('Ngày check-out phải sau ngày check-in');
        document.getElementById('check_out_date').classList.add('is-invalid');
    } else {
        document.getElementById('check_out_date').setCustomValidity('');
        document.getElementById('check_out_date').classList.remove('is-invalid');
        updateStatistics();
    }
});

// Add event listeners to initial room
document.addEventListener('DOMContentLoaded', function() {
    addRoomEventListeners(document.querySelector('.room-selection-item'));
    updateStatistics();
});

// Form validation
document.getElementById('createTourBookingForm').addEventListener('submit', function(e) {
    const totalGuests = parseInt(document.getElementById('total_guests').value) || 0;
    let calculatedGuests = 0;
    
    document.querySelectorAll('.room-selection-item').forEach(room => {
        const quantity = parseInt(room.querySelector('.room-quantity').value) || 0;
        const guests = parseInt(room.querySelector('.room-guests').value) || 0;
        calculatedGuests += quantity * guests;
    });
    
    if (totalGuests !== calculatedGuests) {
        e.preventDefault();
        alert('Tổng số khách phải bằng tổng (số lượng × số khách/phòng)');
        return false;
    }
});
</script>
@endsection
