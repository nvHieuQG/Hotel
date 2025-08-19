@extends('client.layouts.master')

@section('title', 'Xác nhận đặt phòng Tour')

@section('content')
<section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate pb-5 text-center">
                <h1 class="mb-3 bread">Xác nhận đặt phòng Tour</h1>
                <p class="breadcrumbs">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ <i class="ion-ios-arrow-forward"></i></a></span>
                    <span class="mr-2"><a href="{{ route('tour-booking.search') }}">Đặt Tour <i class="ion-ios-arrow-forward"></i></a></span>
                    <span>Xác nhận</span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3>Xác nhận thông tin đặt phòng Tour</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('tour-booking.store') }}" method="POST" id="confirmForm">
                            @csrf
                            
                            <!-- Thông tin tour -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">Thông tin Tour</h5>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <strong>Tên Tour:</strong> {{ $tourData['tour_name'] }}
                                            <input type="hidden" name="tour_name" value="{{ $tourData['tour_name'] }}">
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Tổng số khách:</strong> {{ $tourData['total_guests'] }} người
                                            <input type="hidden" name="total_guests" value="{{ $tourData['total_guests'] }}">
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <strong>Check-in:</strong> {{ \Carbon\Carbon::parse($tourData['check_in_date'])->format('d/m/Y') }}
                                            <input type="hidden" name="check_in_date" value="{{ $tourData['check_in_date'] }}">
                                        </div>
                                        <div class="col-md-6">
                                            <strong>Check-out:</strong> {{ \Carbon\Carbon::parse($tourData['check_out_date'])->format('d/m/Y') }}
                                            <input type="hidden" name="check_out_date" value="{{ $tourData['check_out_date'] }}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Chi tiết phòng đã chọn -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">Chi tiết phòng đã chọn</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Loại phòng</th>
                                                    <th>Số lượng</th>
                                                    <th>Số khách/phòng</th>
                                                    <th>Giá/phòng/đêm</th>
                                                    <th>Tổng tiền</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $totalPrice = 0;
                                                    $totalNights = \Carbon\Carbon::parse($tourData['check_in_date'])->diffInDays(\Carbon\Carbon::parse($tourData['check_out_date']));
                                                @endphp
                                                
                                                @foreach($tourData['room_selections'] as $roomTypeId => $selection)
                                                    @if($selection['quantity'] > 0)
                                                        @php
                                                            $roomType = \App\Models\RoomType::find($roomTypeId);
                                                            $pricePerNight = $roomType->price;
                                                            $pricePerRoom = $pricePerNight * $totalNights;
                                                            $totalForRoomType = $pricePerRoom * $selection['quantity'];
                                                            $totalPrice += $totalForRoomType;
                                                        @endphp
                                                        <tr>
                                                            <td>{{ $roomType->name }}</td>
                                                            <td>{{ $selection['quantity'] }}</td>
                                                            <td>{{ $selection['guests_per_room'] }}</td>
                                                            <td>{{ number_format($pricePerRoom, 0, ',', '.') }} VNĐ</td>
                                                            <td>{{ number_format($totalForRoomType, 0, ',', '.') }} VNĐ</td>
                                                        </tr>
                                                        
                                                        <!-- Hidden inputs -->
                                                        <input type="hidden" name="room_selections[{{ $roomTypeId }}][room_type_id]" value="{{ $roomTypeId }}">
                                                        <input type="hidden" name="room_selections[{{ $roomTypeId }}][quantity]" value="{{ $selection['quantity'] }}">
                                                        <input type="hidden" name="room_selections[{{ $roomTypeId }}][guests_per_room]" value="{{ $selection['guests_per_room'] }}">
                                                    @endif
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-info">
                                                <tr>
                                                    <td colspan="4" class="text-right"><strong>Tổng tiền:</strong></td>
                                                    <td><strong>{{ number_format($totalPrice, 0, ',', '.') }} VNĐ</strong></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Thông tin bổ sung -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2">Thông tin bổ sung</h5>
                                    <div class="form-group">
                                        <label for="special_requests">Yêu cầu đặc biệt (không bắt buộc):</label>
                                        <textarea name="special_requests" id="special_requests" class="form-control" rows="3" 
                                                  placeholder="Nhập các yêu cầu đặc biệt nếu có..."></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Tóm tắt -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <div class="alert alert-info">
                                        <h6>Tóm tắt đặt phòng:</h6>
                                        <ul class="mb-0">
                                            <li><strong>Tổng số phòng:</strong> {{ $totalRooms }} phòng</li>
                                            <li><strong>Tổng số khách:</strong> {{ $tourData['total_guests'] }} người</li>
                                            <li><strong>Số đêm:</strong> {{ $totalNights }} đêm</li>
                                            <li><strong>Tổng tiền:</strong> {{ number_format($totalPrice, 0, ',', '.') }} VNĐ</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Nút điều hướng -->
                            <div class="row">
                                <div class="col-md-6">
                                    <a href="{{ route('tour-booking.select-rooms') }}" class="btn btn-secondary btn-lg btn-block">
                                        <i class="fas fa-arrow-left"></i> Quay lại
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-primary btn-lg btn-block">
                                        <i class="fas fa-check"></i> Xác nhận đặt phòng
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Thông tin bổ sung -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5>Lưu ý quan trọng</h5>
                    </div>
                    <div class="card-body">
                        <ul class="mb-0">
                            <li>Vui lòng kiểm tra kỹ thông tin trước khi xác nhận</li>
                            <li>Đặt phòng sẽ được xử lý sau khi thanh toán thành công</li>
                            <li>Bạn có thể hủy đặt phòng trong vòng 24 giờ trước ngày check-in</li>
                            <li>Liên hệ hotline nếu cần hỗ trợ: <strong>1900-xxxx</strong></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const confirmForm = document.getElementById('confirmForm');
    
    confirmForm.addEventListener('submit', function(e) {
        if (!confirm('Bạn có chắc chắn muốn đặt phòng với thông tin này?')) {
            e.preventDefault();
            return false;
        }
    });
});
</script>

<style>
.table th {
    background-color: #f8f9fa;
    border-color: #dee2e6;
}

.alert-info {
    background-color: #d1ecf1;
    border-color: #bee5eb;
    color: #0c5460;
}
</style>
@endsection 