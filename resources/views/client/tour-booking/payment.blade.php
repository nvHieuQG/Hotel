@extends('client.layouts.master')

@section('title', 'Thanh toán Tour Booking')

@section('content')
<section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate pb-5 text-center">
                <h1 class="mb-3 bread">Thanh toán Tour Booking</h1>
                <p class="breadcrumbs">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ <i class="ion-ios-arrow-forward"></i></a></span>
                    <span class="mr-2"><a href="{{ route('tour-booking.index') }}">Tour Bookings <i class="ion-ios-arrow-forward"></i></a></span>
                    <span>Thanh toán</span>
                </p>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Thông tin tour booking -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Thông tin Tour Booking</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mã đặt phòng:</strong> {{ $tourBooking->booking_id }}</p>
                                <p><strong>Tên Tour:</strong> {{ $tourBooking->tour_name }}</p>
                                <p><strong>Tổng số khách:</strong> {{ $tourBooking->total_guests }} người</p>
                                <p><strong>Tổng số phòng:</strong> {{ $tourBooking->total_rooms }} phòng</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Check-in:</strong> {{ $tourBooking->check_in_date->format('d/m/Y') }}</p>
                                <p><strong>Check-out:</strong> {{ $tourBooking->check_out_date->format('d/m/Y') }}</p>
                                <p><strong>Số đêm:</strong> {{ $tourBooking->total_nights }} đêm</p>
                                <p><strong>Tổng tiền:</strong> <span class="text-primary font-weight-bold">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chi tiết phòng -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Chi tiết phòng đã đặt</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Loại phòng</th>
                                        <th>Số lượng</th>
                                        <th>Số khách/phòng</th>
                                        <th>Giá/phòng</th>
                                        <th>Tổng tiền</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($tourBooking->tourBookingRooms as $tourBookingRoom)
                                        <tr>
                                            <td>{{ $tourBookingRoom->roomType->name }}</td>
                                            <td>{{ $tourBookingRoom->quantity }}</td>
                                            <td>{{ $tourBookingRoom->guests_per_room }}</td>
                                            <td>{{ number_format($tourBookingRoom->price_per_room, 0, ',', '.') }} VNĐ</td>
                                            <td>{{ number_format($tourBookingRoom->total_price, 0, ',', '.') }} VNĐ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="table-info">
                                    <tr>
                                        <td colspan="4" class="text-right"><strong>Tổng tiền:</strong></td>
                                        <td><strong>{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</strong></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="card">
                    <div class="card-header">
                        <h5>Chọn phương thức thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="payment-method-item mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                                                <label class="form-check-label" for="credit_card">
                                                    <i class="fas fa-credit-card text-primary"></i>
                                                    <strong>Thẻ tín dụng/ghi nợ</strong>
                                                </label>
                                            </div>
                                            <small class="text-muted">Thanh toán an toàn qua cổng thanh toán</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="payment-method-item mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio" name="payment_method" id="bank_transfer" value="bank_transfer">
                                                <label class="form-check-label" for="bank_transfer">
                                                    <i class="fas fa-university text-success"></i>
                                                    <strong>Chuyển khoản ngân hàng</strong>
                                                </label>
                                            </div>
                                            <small class="text-muted">Chuyển khoản trực tiếp đến tài khoản ngân hàng</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form thanh toán thẻ tín dụng -->
                        <div id="credit-card-form" class="payment-form">
                            <h6 class="mt-4 mb-3">Thông tin thẻ</h6>
                            <form id="creditCardForm" action="{{ route('tour-booking.credit-card-payment') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tour_booking_id" value="{{ $tourBooking->id }}">
                                <input type="hidden" name="amount" value="{{ $tourBooking->total_price }}">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="card_number">Số thẻ</label>
                                            <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456" maxlength="19" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="expiry_month">Tháng</label>
                                            <select id="expiry_month" name="expiry_month" class="form-control" required>
                                                <option value="">MM</option>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="expiry_year">Năm</label>
                                            <select id="expiry_year" name="expiry_year" class="form-control" required>
                                                <option value="">YYYY</option>
                                                @for($i = date('Y'); $i <= date('Y') + 10; $i++)
                                                    <option value="{{ $i }}">{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="cvv">CVV</label>
                                            <input type="text" id="cvv" name="cvv" class="form-control" placeholder="123" maxlength="4" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="card_holder">Tên chủ thẻ</label>
                                            <input type="text" id="card_holder" name="cardholder_name" class="form-control" placeholder="NGUYEN VAN A" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg px-5" id="processCreditCard">
                                        <i class="fas fa-lock"></i> Thanh toán {{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Thông tin chuyển khoản -->
                        <div id="bank-transfer-info" class="payment-form" style="display: none;">
                            <h6 class="mt-4 mb-3">Thông tin chuyển khoản</h6>
                            <form id="bankTransferForm" action="{{ route('tour-booking.bank-transfer-payment') }}" method="POST">
                                @csrf
                                <input type="hidden" name="tour_booking_id" value="{{ $tourBooking->id }}">
                                <input type="hidden" name="amount" value="{{ $tourBooking->total_price }}">
                                
                                <div class="alert alert-info">
                                    <h6>Thông tin tài khoản ngân hàng:</h6>
                                    <p class="mb-1"><strong>Ngân hàng:</strong> Vietcombank</p>
                                    <p class="mb-1"><strong>Số tài khoản:</strong> 1234567890</p>
                                    <p class="mb-1"><strong>Chủ tài khoản:</strong> KHACH SAN MARRON</p>
                                    <p class="mb-1"><strong>Nội dung:</strong> {{ $tourBooking->booking_id }}</p>
                                    <p class="mb-0"><strong>Số tiền:</strong> {{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</p>
                                </div>
                                
                                <div class="form-group">
                                    <label for="customer_note">Ghi chú (tùy chọn)</label>
                                    <textarea id="customer_note" name="customer_note" class="form-control" rows="3" placeholder="Nhập ghi chú nếu cần..."></textarea>
                                </div>
                                
                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-success btn-lg px-5" id="processBankTransfer">
                                        <i class="fas fa-university"></i> Xác nhận chuyển khoản
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const creditCardRadio = document.getElementById('credit_card');
    const bankTransferRadio = document.getElementById('bank_transfer');
    const creditCardForm = document.getElementById('credit-card-form');
    const bankTransferInfo = document.getElementById('bank-transfer-info');

    // Xử lý chuyển đổi phương thức thanh toán
    creditCardRadio.addEventListener('change', function() {
        creditCardForm.style.display = 'block';
        bankTransferInfo.style.display = 'none';
    });

    bankTransferRadio.addEventListener('change', function() {
        creditCardForm.style.display = 'none';
        bankTransferInfo.style.display = 'block';
    });

    // Format số thẻ
    document.getElementById('card_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\s/g, '').replace(/\D/g, '');
        value = value.replace(/(\d{4})/g, '$1 ').trim();
        e.target.value = value;
    });

    // Format CVV
    document.getElementById('cvv').addEventListener('input', function(e) {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // Xử lý form thẻ tín dụng
    document.getElementById('creditCardForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('processCreditCard');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        
        // Submit form
        this.submit();
    });

    // Xử lý form chuyển khoản
    document.getElementById('bankTransferForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = document.getElementById('processBankTransfer');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';
        
        // Submit form
        this.submit();
    });
});
</script>

<style>
.payment-method-item .card {
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.payment-method-item .card:hover {
    border-color: #007bff;
}

.payment-method-item input[type="radio"]:checked + label {
    color: #007bff;
}

.payment-method-item input[type="radio"]:checked ~ .card {
    border-color: #007bff;
    background-color: #f8f9fa;
}

.payment-form {
    border-top: 1px solid #dee2e6;
    padding-top: 20px;
}
</style>
@endsection 