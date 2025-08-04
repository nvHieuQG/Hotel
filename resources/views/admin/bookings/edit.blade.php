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
                    <label for="status" class="form-label">Trạng thái đặt phòng</label>
                    <select name="status" id="status" class="form-select @error('status') is-invalid @enderror" required>
                        <option value="{{ $booking->status }}" selected>{{ $booking->status_text }} (Hiện tại)</option>
                        @foreach($validNextStatuses as $status => $label)
                            <option value="{{ $status }}" {{ (old('status') == $status) ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">
                        <strong>Hướng dẫn chuyển trạng thái:</strong><br>
                        • <strong>Thứ tự đề xuất:</strong> Chờ xác nhận → Đã xác nhận → Đã nhận phòng → Đã trả phòng → Hoàn thành<br>
                        • <strong>Chuyển linh hoạt:</strong> Có thể chuyển sang bất kỳ trạng thái nào phía trước<br>
                        • <strong>Chuyển đặc biệt:</strong> Có thể chuyển sang "Đã hủy" hoặc "Khách không đến" ở bất kỳ trạng thái nào<br>
                        • <strong>Không được lùi:</strong> Không thể chuyển về trạng thái trước đó
                        <br><small class="text-muted">Trạng thái hiện tại: <strong>{{ $booking->status_text }}</strong></small>
                    </div>
                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    @if(empty($validNextStatuses))
                        <div class="alert alert-warning mt-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Lưu ý:</strong> Booking đã ở trạng thái cuối cùng. Chỉ có thể chuyển sang "Đã hủy" hoặc "Khách không đến".
                        </div>
                    @else
                        <div class="alert alert-success mt-2">
                            <i class="fas fa-check-circle"></i>
                            <strong>Thông tin:</strong> Có <strong>{{ count($validNextStatuses) }}</strong> trạng thái có thể chuyển đổi từ trạng thái hiện tại.
                            <br><small class="text-muted">Các trạng thái có thể chuyển: 
                                @foreach($validNextStatuses as $status => $label)
                                    <span class="badge bg-light text-dark me-1">{{ $label }}</span>
                                @endforeach
                            </small>
                        </div>
                    @endif
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Tổng tiền hiện tại</label>
                    <div class="form-control bg-light">{{ number_format($booking->price, 0, ',', '.') }} VNĐ</div>
                    <small class="text-muted">Tổng tiền sẽ được tính lại nếu thay đổi phòng hoặc ngày</small>
                </div>
            </div>
            
            <div class="row mb-3">
                <div class="col-md-12">
                    <label for="admin_notes" class="form-label">Ghi chú quản lý</label>
                    <textarea name="admin_notes" id="admin_notes" class="form-control @error('admin_notes') is-invalid @enderror" rows="3" placeholder="Ghi chú nội bộ cho quản lý (khách hàng không thấy)">{{ old('admin_notes', $booking->admin_notes) }}</textarea>
                    <div class="form-text">
                        Ghi chú nội bộ để theo dõi tình trạng đặt phòng, lý do thay đổi trạng thái, hoặc thông tin liên hệ với khách hàng.
                    </div>
                    @error('admin_notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Thông tin căn cước của khách -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-id-card me-1"></i>
                        Thông tin căn cước của khách hàng
                        @if($booking->hasCompleteIdentityInfo())
                            <span class="badge bg-success ms-2">Đầy đủ</span>
                        @else
                            <span class="badge bg-warning ms-2">Thiếu thông tin</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guest_full_name" class="form-label">Họ tên đầy đủ <span class="text-danger">*</span></label>
                            <input type="text" name="guest_full_name" id="guest_full_name" class="form-control @error('guest_full_name') is-invalid @enderror" value="{{ old('guest_full_name', $booking->guest_full_name) }}" placeholder="Nhập họ tên đầy đủ">
                            @error('guest_full_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest_id_number" class="form-label">Số căn cước công dân <span class="text-danger">*</span></label>
                            <input type="text" name="guest_id_number" id="guest_id_number" class="form-control @error('guest_id_number') is-invalid @enderror" value="{{ old('guest_id_number', $booking->guest_id_number) }}" placeholder="Nhập số CCCD/CMND">
                            @error('guest_id_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="guest_birth_date" class="form-label">Ngày sinh <span class="text-danger">*</span></label>
                            <input type="date" name="guest_birth_date" id="guest_birth_date" class="form-control @error('guest_birth_date') is-invalid @enderror" value="{{ old('guest_birth_date', $booking->guest_birth_date ? $booking->guest_birth_date->format('Y-m-d') : '') }}">
                            @error('guest_birth_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest_gender" class="form-label">Giới tính <span class="text-danger">*</span></label>
                            <select name="guest_gender" id="guest_gender" class="form-select @error('guest_gender') is-invalid @enderror">
                                <option value="">-- Chọn giới tính --</option>
                                <option value="male" {{ old('guest_gender', $booking->guest_gender) == 'male' ? 'selected' : '' }}>Nam</option>
                                <option value="female" {{ old('guest_gender', $booking->guest_gender) == 'female' ? 'selected' : '' }}>Nữ</option>
                                <option value="other" {{ old('guest_gender', $booking->guest_gender) == 'other' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('guest_gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest_nationality" class="form-label">Quốc tịch <span class="text-danger">*</span></label>
                            <input type="text" name="guest_nationality" id="guest_nationality" class="form-control @error('guest_nationality') is-invalid @enderror" value="{{ old('guest_nationality', $booking->guest_nationality) }}" placeholder="Ví dụ: Việt Nam">
                            @error('guest_nationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guest_permanent_address" class="form-label">Địa chỉ thường trú <span class="text-danger">*</span></label>
                            <textarea name="guest_permanent_address" id="guest_permanent_address" class="form-control @error('guest_permanent_address') is-invalid @enderror" rows="2" placeholder="Nhập địa chỉ thường trú đầy đủ">{{ old('guest_permanent_address', $booking->guest_permanent_address) }}</textarea>
                            @error('guest_permanent_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest_current_address" class="form-label">Địa chỉ tạm trú</label>
                            <textarea name="guest_current_address" id="guest_current_address" class="form-control @error('guest_current_address') is-invalid @enderror" rows="2" placeholder="Nhập địa chỉ tạm trú hiện tại">{{ old('guest_current_address', $booking->guest_current_address) }}</textarea>
                            @error('guest_current_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="guest_phone" class="form-label">Số điện thoại</label>
                            <input type="text" name="guest_phone" id="guest_phone" class="form-control @error('guest_phone') is-invalid @enderror" value="{{ old('guest_phone', $booking->guest_phone) }}" placeholder="Nhập số điện thoại">
                            @error('guest_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest_email" class="form-label">Email</label>
                            <input type="email" name="guest_email" id="guest_email" class="form-control @error('guest_email') is-invalid @enderror" value="{{ old('guest_email', $booking->guest_email) }}" placeholder="Nhập email">
                            @error('guest_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="guest_purpose_of_stay" class="form-label">Mục đích lưu trú</label>
                            <select name="guest_purpose_of_stay" id="guest_purpose_of_stay" class="form-select @error('guest_purpose_of_stay') is-invalid @enderror">
                                <option value="">-- Chọn mục đích --</option>
                                <option value="business" {{ old('guest_purpose_of_stay', $booking->guest_purpose_of_stay) == 'business' ? 'selected' : '' }}>Công tác</option>
                                <option value="tourism" {{ old('guest_purpose_of_stay', $booking->guest_purpose_of_stay) == 'tourism' ? 'selected' : '' }}>Du lịch</option>
                                <option value="family" {{ old('guest_purpose_of_stay', $booking->guest_purpose_of_stay) == 'family' ? 'selected' : '' }}>Thăm gia đình</option>
                                <option value="medical" {{ old('guest_purpose_of_stay', $booking->guest_purpose_of_stay) == 'medical' ? 'selected' : '' }}>Khám chữa bệnh</option>
                                <option value="study" {{ old('guest_purpose_of_stay', $booking->guest_purpose_of_stay) == 'study' ? 'selected' : '' }}>Học tập</option>
                                <option value="other" {{ old('guest_purpose_of_stay', $booking->guest_purpose_of_stay) == 'other' ? 'selected' : '' }}>Khác</option>
                            </select>
                            @error('guest_purpose_of_stay')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="guest_vehicle_number" class="form-label">Biển số xe (nếu có)</label>
                            <input type="text" name="guest_vehicle_number" id="guest_vehicle_number" class="form-control @error('guest_vehicle_number') is-invalid @enderror" value="{{ old('guest_vehicle_number', $booking->guest_vehicle_number) }}" placeholder="Nhập biển số xe">
                            @error('guest_vehicle_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="guest_notes" class="form-label">Ghi chú thêm</label>
                            <textarea name="guest_notes" id="guest_notes" class="form-control @error('guest_notes') is-invalid @enderror" rows="2" placeholder="Ghi chú thêm về khách hàng">{{ old('guest_notes', $booking->guest_notes) }}</textarea>
                            @error('guest_notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    @if(!$booking->hasCompleteIdentityInfo())
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Lưu ý:</strong> Thông tin căn cước chưa đầy đủ. Vui lòng điền đầy đủ các trường bắt buộc (có dấu *) để có thể tạo giấy đăng ký tạm chú tạm vắng.
                        </div>
                    @else
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Thông tin đầy đủ:</strong> Có thể tạo giấy đăng ký tạm chú tạm vắng cho khách hàng này.
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">Hủy</a>
            </div>
        </form>
    </div>
</div>

<!-- Ghi chú đặt phòng -->
@include('admin.bookings.partials.notes')
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

@push('styles')
<style>
    .card-header .badge {
        font-size: 0.75rem;
    }
    
    .form-label .text-danger {
        font-weight: bold;
    }
    
    .alert {
        border-radius: 0.5rem;
    }
    
    .alert i {
        margin-right: 0.5rem;
    }
    
    /* Responsive cho form thông tin căn cước */
    @media (max-width: 768px) {
        .col-md-4, .col-md-6 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush 