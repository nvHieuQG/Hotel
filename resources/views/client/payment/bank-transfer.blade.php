@extends('client.layouts.master')

@section('title', 'Thanh toán chuyển khoản')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Thanh toán chuyển khoản</h4>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Thông tin đặt phòng -->
                    <div class="mb-4">
                        <h6 class="alert-heading">Thông tin đặt phòng</h6>
                        <p class="mb-1"><strong>Mã đặt phòng:</strong> {{ $booking->booking_id }}</p>
                        
                        <!-- Form nhập số tiền thanh toán -->
                        <form id="paymentAmountForm" action="{{ route('payment.process-bank-transfer', $booking->id) }}" method="POST" class="mt-3" style="display:none">
                            @csrf
                            <div class="form-group">
                                <label for="payment_amount"><strong>Số tiền muốn thanh toán:</strong></label>
                                <div class="input-group">
                                    <input type="number" 
                                           class="form-control" 
                                           id="payment_amount" 
                                           name="amount"
                                           value="{{ $tempPaymentData['amount'] }}"
                                           min="{{ $tempPaymentData['min_amount'] }}"
                                           max="{{ $tempPaymentData['max_amount'] }}"
                                           step="1000"
                                           required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">VNĐ</span>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Số tiền tối thiểu: {{ number_format($tempPaymentData['min_amount']) }} VNĐ (20%) | 
                                    Số tiền tối đa: {{ number_format($tempPaymentData['max_amount']) }} VNĐ (100%)
                                </small>
                            </div>
                            
                            <div class="form-group">
                                <label for="promotion_code">Mã khuyến mại (nếu có)</label>
                                <input type="text" class="form-control" id="promotion_code" name="promotion_code" 
                                       placeholder="Nhập mã khuyến mại">
                            </div>
                            
                            
                        </form>

                        <div class="alert alert-info mt-3">
                            <i class="fas fa-info-circle mr-2"></i>
                            <strong>Lưu ý:</strong> Bạn có thể thanh toán từ {{ number_format($tempPaymentData['min_amount']) }} VNĐ (20%) đến {{ number_format($tempPaymentData['max_amount']) }} VNĐ (100%). 
                            Số tiền còn lại có thể thanh toán sau khi admin xác nhận giao dịch này.
                        </div>
                        
                        <!-- Chi tiết giá -->
                        <div class="mt-3">
                            <h6 class="alert-heading">Chi tiết giá</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Giá phòng:</strong> {{ number_format($booking->base_room_price) }} VNĐ</p>
                                    <p class="mb-1"><strong>Dịch vụ & phụ phí:</strong> {{ number_format($booking->surcharge + $booking->extra_services_total + $booking->total_services_price) }} VNĐ</p>
                                    @if(($tempPaymentData['discount_amount'] ?? 0) > 0)
                                        <p class="mb-1 text-success"><strong>Khuyến mại:</strong> -{{ number_format($tempPaymentData['discount_amount']) }} VNĐ</p>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Tổng cần thanh toán:</strong> <span class="text-danger font-weight-bold">{{ number_format($tempPaymentData['max_amount']) }} VNĐ</span></p>
                                    <p class="mb-0"><strong>Nội dung chuyển khoản:</strong> <code>Thanh toan dat phong {{ $booking->booking_id }}</code></p>
                                    <p class="mb-0 mt-2"><strong>Mã giao dịch của bạn:</strong> <code id="expected_tx">{{ $tempPaymentData['expected_transaction_id'] ?? '' }}</code></p>
                                    <small class="text-muted">Vui lòng ghi đúng mã này ở phần ghi chú/Nội dung khi chuyển khoản để đối chiếu.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin ngân hàng -->
                    <div class="mb-4">
                        <h6 class="alert-heading">Thông tin chuyển khoản</h6>
                        <div class="row">
                            @if(isset($bankInfo['banks']))
                                @foreach($bankInfo['banks'] as $index => $bank)
                                    <div class="col-md-6 mb-3">
                                        <div class="card border-primary">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary">{{ $bank['name'] }}</h6>
                                                <p class="mb-1"><strong>Tài khoản:</strong> {{ $bank['account_number'] }}</p>
                                                <p class="mb-1"><strong>Chủ tài khoản:</strong> {{ $bank['account_name'] }}</p>
                                                <p class="mb-1"><strong>Chi nhánh:</strong> {{ $bank['branch'] }}</p>
                                                <p class="mb-0"><strong>Nội dung:</strong> <code>{{ $bank['transfer_content'] }} {{ $booking->booking_id }}</code></p>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-md-6">
                                    <div class="card border-primary">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary">Ngân hàng BIDV</h6>
                                            <p class="mb-1"><strong>Tài khoản:</strong> 1234567890</p>
                                            <p class="mb-1"><strong>Chủ tài khoản:</strong> KHACH SAN ABC</p>
                                            <p class="mb-0"><strong>Nội dung:</strong> Thanh toan dat phong {{ $booking->booking_id }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <h6 class="card-title text-success">Ngân hàng Techcombank</h6>
                                            <p class="mb-1"><strong>Tài khoản:</strong> 0987654321</p>
                                            <p class="mb-1"><strong>Chủ tài khoản:</strong> KHACH SAN ABC</p>
                                            <p class="mb-0"><strong>Nội dung:</strong> Thanh toan dat phong {{ $booking->booking_id }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Hướng dẫn thanh toán -->
                    <div class="mb-4">
                        <h6 class="alert-heading">Hướng dẫn thanh toán</h6>
                        <ol>
                            <li>Nhập số tiền muốn thanh toán (tối thiểu 20%)</li>
                            <li>Nhấn "Tạo yêu cầu thanh toán"</li>
                            <li>Chọn ngân hàng và thực hiện chuyển khoản</li>
                            <li>Nhấn "Báo đã thanh toán" và điền thông tin giao dịch</li>
                        </ol>
                    </div>

                    <!-- Nút báo đã thanh toán -->
                    <div class="text-center">
                        <button type="button" id="reportPaidBtn" class="btn btn-success btn-lg" data-toggle="modal" data-target="#paymentConfirmationModal">
                            <i class="fas fa-check-circle mr-2"></i>Báo đã thanh toán
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal xác nhận thanh toán (1 bước) -->
<div class="modal fade" id="paymentConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="paymentConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentConfirmationModalLabel">Báo đã thanh toán</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('payment.confirm-bank-transfer', $booking->id) }}" method="POST" id="confirmPaymentForm">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="amount">Số tiền đã chuyển khoản <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount" name="amount" required 
                               value="{{ $tempPaymentData['amount'] }}"
                               min="{{ $tempPaymentData['min_amount'] }}"
                               max="{{ $tempPaymentData['max_amount'] }}"
                               step="1000">
                        <small class="form-text text-muted">Tối thiểu {{ number_format($tempPaymentData['min_amount']) }} VNĐ (20%), tối đa {{ number_format($tempPaymentData['max_amount']) }} VNĐ</small>
                    </div>
                    <div class="form-group">
                        <label for="transaction_id">Mã giao dịch (Transaction ID) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="transaction_id" name="transaction_id" required placeholder="Mã giao dịch ngân hàng"
                               value="{{ $tempPaymentData['expected_transaction_id'] ?? '' }}" readonly>
                    </div>
                    <div class="form-group">
                        <label for="bank_name">Ngân hàng đã chuyển khoản <span class="text-danger">*</span></label>
                        <select class="form-control" id="bank_name" name="bank_name" required>
                            <option value="">-- Chọn ngân hàng --</option>
                            <option value="Vietcombank">Vietcombank</option>
                            <option value="BIDV">BIDV</option>
                            <option value="Techcombank">Techcombank</option>
                            <option value="VietinBank">VietinBank</option>
                            <option value="Agribank">Agribank</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="customer_note">Ghi chú (không bắt buộc)</label>
                        <textarea class="form-control" id="customer_note" name="customer_note" rows="3" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-success">Gửi xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Bảo đảm nút luôn mở modal kể cả khi Bootstrap JS chưa bind data-toggle
    $('#reportPaidBtn').on('click', function(e) {
        e.preventDefault();
        var $modal = $('#paymentConfirmationModal');
        if (typeof $modal.modal === 'function') {
            $modal.modal('show');
        } else {
            $modal.removeClass('fade');
            $modal.show();
        }
    });

    // Fallback đóng modal khi không có Bootstrap JS
    $('#paymentConfirmationModal .close, #paymentConfirmationModal [data-dismiss="modal"]').on('click', function(e){
        var $modal = $('#paymentConfirmationModal');
        if (typeof $modal.modal === 'function') {
            $modal.modal('hide');
        } else {
            $modal.hide();
        }
    });

    // Đóng bằng phím ESC (fallback)
    $(document).on('keydown', function(e){
        if (e.key === 'Escape') {
            var $modal = $('#paymentConfirmationModal');
            if ($modal.is(':visible')) {
                if (typeof $modal.modal === 'function') $modal.modal('hide'); else $modal.hide();
            }
        }
    });

    // Validate modal 1-bước
    $('#confirmPaymentForm').on('submit', function(e) {
        var amount = parseInt($('#amount').val());
        var minAmount = parseInt($('#amount').attr('min'));
        var maxAmount = parseInt($('#amount').attr('max'));
        var transactionId = $('#transaction_id').val().trim();
        var bankName = $('#bank_name').val();

        // Nhắc khách kiểm tra lại mã giao dịch hiển thị
        var expected = $('#expected_tx').text().trim();
        if (expected && transactionId !== expected) {
            e.preventDefault();
            alert('Mã giao dịch không khớp. Vui lòng dùng mã: ' + expected);
            return false;
        }

        if (!amount || amount < minAmount) {
            e.preventDefault();
            alert('Số tiền tối thiểu: ' + minAmount.toLocaleString() + ' VNĐ');
            $('#amount').focus();
            return false;
        }
        if (amount > maxAmount) {
            e.preventDefault();
            alert('Số tiền tối đa: ' + maxAmount.toLocaleString() + ' VNĐ');
            $('#amount').focus();
            return false;
        }
        if (!transactionId) {
            e.preventDefault();
            alert('Vui lòng nhập mã giao dịch');
            $('#transaction_id').focus();
            return false;
        }
        if (!bankName) {
            e.preventDefault();
            alert('Vui lòng chọn ngân hàng');
            $('#bank_name').focus();
            return false;
        }

        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin mr-2"></i>Đang gửi...').prop('disabled', true);
    });
});
</script>
@endsection 