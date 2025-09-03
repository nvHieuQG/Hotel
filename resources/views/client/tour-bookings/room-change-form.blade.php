@extends('client.layouts.master')

@section('title', 'Đổi phòng Tour')

@section('body_class', 'page-room-change')
@section('content')
<div class="container mt-4" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); min-height: 100vh; padding: 20px;">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg" style="border: none; border-radius: 15px;">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 15px 15px 0 0;">
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
                            <div class="card border-info" style="background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);">
                                <div class="card-body">
                                    <h6 class="text-primary"><i class="fas fa-info-circle"></i> Thông tin Tour:</h6>
                                    <p class="text-dark"><strong>Tour:</strong> {{ $tourBooking->tour_name }}</p>
                                    <p class="text-dark"><strong>Check-in:</strong> {{ \Carbon\Carbon::parse($tourBooking->check_in_date)->format('d/m/Y') }}</p>
                                    <p class="text-dark"><strong>Check-out:</strong> {{ \Carbon\Carbon::parse($tourBooking->check_out_date)->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-warning" style="background: linear-gradient(135deg, #fff3e0 0%, #fce4ec 100%);">
                                <div class="card-body">
                                    <h6 class="text-warning"><i class="fas fa-bed"></i> Phòng hiện tại:</h6>
                                    @foreach($tourBooking->tourBookingRooms as $tbr)
                                        @if($tbr->assigned_rooms->count() > 0)
                                            @foreach($tbr->assigned_rooms as $room)
                                                <div class="mb-2">
                                                    <span class="badge badge-success" style="font-size: 14px; padding: 8px 12px;">{{ $room->room_number }}</span>
                                                    <small class="text-dark">({{ $room->roomType->name }})</small>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted">Chưa có phòng nào được gán</p>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('tour-room-changes.store', $tourBooking->id) }}">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="from_room_id" class="text-danger"><i class="fas fa-arrow-left"></i> Từ phòng (hiện tại):</label>
                                    <select name="from_room_id" id="from_room_id" class="form-control @error('from_room_id') is-invalid @enderror" required>
                                        <option value="">-- Chọn phòng hiện tại --</option>
                                        @foreach($tourBooking->tourBookingRooms as $tbr)
                                            @if($tbr->assigned_rooms->count() > 0)
                                                @foreach($tbr->assigned_rooms as $room)
                                                    <option value="{{ $room->id }}" data-room-type="{{ $room->room_type_id }}" data-price="{{ $room->roomType->price }}">
                                                        {{ $room->room_number }} - {{ $room->roomType->name }} ({{ number_format($room->roomType->price) }} VNĐ/đêm)
                                                    </option>
                                                @endforeach
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('from_room_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Hệ thống sẽ tự đề xuất phòng cùng loại còn trống để đổi.</small>
                                </div>
                            </div>
                        </div>


                        <div class="form-group">
                            <label for="reason">Lý do đổi phòng:</label>
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

                        <div class="form-group text-center" style="margin-top: 30px;">
                            <button type="submit" class="btn btn-primary btn-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; padding: 12px 30px; border-radius: 25px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);">
                                <i class="fas fa-paper-plane"></i>
                                Gửi yêu cầu đổi phòng
                            </button>
                            <a href="{{ route('tour-booking.show', $tourBooking->id) }}" class="btn btn-secondary btn-lg ml-2" style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); border: none; padding: 12px 30px; border-radius: 25px; box-shadow: 0 4px 15px rgba(108, 117, 125, 0.4);">
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

@push('styles')
<style>
    /* Dark theme for this page */
    body.page-room-change {
        background: #000000 !important;
        color: #ffffff !important;
    }
    body.page-room-change .card {
        background-color: #111318;
        color: #ffffff;
        border-color: #1f2430;
    }
    body.page-room-change .card-header {
        color: #ffffff;
    }
    body.page-room-change .form-control,
    body.page-room-change select,
    body.page-room-change textarea {
        background-color: #1a1f29;
        border: 1px solid #2b3240;
        color: #ffffff;
    }
    body.page-room-change .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        background-color: #1a1f29;
        color: #ffffff;
    }
    body.page-room-change label,
    body.page-room-change h6,
    body.page-room-change p,
    body.page-room-change small,
    body.page-room-change .text-muted { color: #e6e6e6 !important; }
    body.page-room-change .alert-info { color: #0c5460; }
    body.page-room-change .badge-success { background-color: #2e7d32; }

    .form-control {
        border-radius: 10px;
        border: 2px solid #e9ecef;
        padding: 12px 15px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }
    
    .alert-info {
        background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
        border: none;
        border-radius: 10px;
        color: #0c5460;
    }
    
    .badge {
        border-radius: 20px;
        font-weight: 500;
    }
    
    .card {
        transition: transform 0.3s ease;
    }
    
    .card:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

{{-- Loại bỏ JS động để tránh lỗi – form submit qua route thuần --}}
