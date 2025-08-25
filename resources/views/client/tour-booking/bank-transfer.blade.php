@extends('client.layouts.master')

@section('title', 'Chuyển khoản ngân hàng - Tour Booking')

@section('content')
<div class="hero-wrap" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}'); font-family: 'Segoe UI', sans-serif;">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-items-end justify-content-center">
            <div class="col-md-9 text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <p class="breadcrumbs mb-2">
                        <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                        <span class="mr-2"><a href="{{ route('tour-booking.show', $tourBooking->id) }}">Tour Booking</a></span>
                        <span>Chuyển khoản ngân hàng</span>
                    </p>
                    <h3 class="mb-4 bread">Chuyển khoản ngân hàng</h3>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section bg-light" style="font-family: 'Segoe UI', sans-serif;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Thông tin đặt phòng -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #C9A888; color: white;">
                        <h5 class="mb-0">Thông tin đặt phòng Tour</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mã đặt phòng:</strong> {{ $tourBooking->booking_id }}</p>
                                <p><strong>Tên Tour:</strong> {{ $tourBooking->tour_name }}</p>
                                <p><strong>Tổng số khách:</strong> {{ $tourBooking->total_guests }} người</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Check-in:</strong> {{ $tourBooking->check_in_date->format('d/m/Y') }}</p>
                                <p><strong>Check-out:</strong> {{ $tourBooking->check_out_date->format('d/m/Y') }}</p>
                                <p><strong>Số tiền:</strong> <span class="text-primary font-weight-bold">{{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin thanh toán -->
                <div class="card mb-4">
                    <div class="card-header" style="background-color: #C9A888; color: white;">
                        <h5 class="mb-0">Thông tin thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mã giao dịch:</strong> {{ $tempPaymentData['transaction_id'] }}</p>
                                <p><strong>Phương thức:</strong> Chuyển khoản ngân hàng</p>
                                <p><strong>Trạng thái:</strong> <span class="badge badge-warning">Chờ thanh toán</span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Số tiền:</strong> {{ number_format($tempPaymentData['amount'], 0, ',', '.') }} VNĐ</p>
                                @if($tempPaymentData['discount_amount'] > 0)
                                    <p><strong>Giảm giá:</strong> {{ number_format($tempPaymentData['discount_amount'], 0, ',', '.') }} VNĐ</p>
                                @endif
                                <p><strong>Ngày tạo:</strong> {{ \Carbon\Carbon::parse($tempPaymentData['created_at'])->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Thông tin tài khoản ngân hàng -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0"><i class="fas fa-university"></i> Thông tin tài khoản ngân hàng</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Ngân hàng:</strong> {{ $bankInfo['bank_name'] }}</p>
                                <p class="mb-1"><strong>Số tài khoản:</strong> {{ $bankInfo['account_number'] }}</p>
                                <p class="mb-1"><strong>Chủ tài khoản:</strong> {{ $bankInfo['account_holder'] }}</p>
                            </div>
                            <div class="col-md-6">
                                <p class="mb-1"><strong>Chi nhánh:</strong> {{ $bankInfo['branch'] }}</p>
                                <p class="mb-1"><strong>Swift code:</strong> {{ $bankInfo['swift_code'] }}</p>
                                <p class="mb-1"><strong>Nội dung:</strong> {{ $tourBooking->booking_id }}</p>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="copyBankInfo()">
                                <i class="fas fa-copy"></i> Sao chép thông tin
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Hướng dẫn chuyển khoản -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-info-circle"></i> Hướng dẫn chuyển khoản</h6>
                    </div>
                    <div class="card-body">
                        <ol class="mb-0">
                            @foreach($bankInfo['instructions'] as $instruction)
                                <li>{{ $instruction }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>

                <!-- Nút quay lại -->
                <div class="text-center mt-4">
                    <a href="{{ route('tour-booking.payment', $tourBooking->booking_id) }}" class="btn btn-secondary btn-lg px-5">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>

                <!-- Báo đã thanh toán -->
                <div class="card mt-4 shadow-sm rounded-lg">
                    <div class="card-header bg-success text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-check-circle mr-2"></i>
                            Báo đã thanh toán
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Hướng dẫn:</strong>
                            <ol class="mb-0 mt-2">
                                <li>Thực hiện chuyển khoản theo thông tin bên trái</li>
                                <li>Bấm nút "Báo đã thanh toán" bên dưới</li>
                                <li>Admin sẽ xác nhận trong thời gian sớm nhất</li>
                            </ol>
                        </div>
                        
                        <div class="text-center">
                            <button type="button" class="btn btn-success btn-lg btn-block" onclick="confirmTourPayment()">
                                <i class="fas fa-check-circle mr-2"></i>
                                Báo đã thanh toán
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Lưu ý -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Lưu ý quan trọng</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $bankInfo['note'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>

function copyBankInfo() {
    // Copy thông tin ngân hàng
    const bankInfo = document.getElementById('bankInfo');
    const textArea = document.createElement('textarea');
    textArea.value = bankInfo.textContent;
    document.body.appendChild(textArea);
    textArea.select();
    document.execCommand('copy');
    document.body.removeChild(textArea);
    
    // Hiển thị thông báo
    alert('Đã copy thông tin ngân hàng!');
}

function confirmTourPayment() {
    // Hiển thị form xác nhận
    const confirmForm = `
        <div class="modal fade" id="confirmTourPaymentModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Xác nhận thông tin chuyển khoản Tour</h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="confirmTourPaymentForm" action="{{ route('tour-booking.bank-transfer.confirm', $tourBooking->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="bank_name">Ngân hàng đã chuyển khoản <span class="text-danger">*</span></label>
                                <select name="bank_name" id="bank_name" class="form-control" required>
                                    <option value="">-- Chọn ngân hàng --</option>
                                    <option value="Vietcombank">Vietcombank</option>
                                    <option value="BIDV">BIDV</option>
                                    <option value="Techcombank">Techcombank</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="transfer_amount">Số tiền đã chuyển <span class="text-danger">*</span></label>
                                <input type="number" name="transfer_amount" id="transfer_amount" 
                                       class="form-control" value="{{ (int) $tempPaymentData['amount'] }}" required>
                            </div>

                            <div class="form-group">
                                <label for="transfer_date">Ngày chuyển khoản <span class="text-danger">*</span></label>
                                <input type="date" name="transfer_date" id="transfer_date" 
                                       class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="form-group">
                                <label for="receipt_image">Ảnh biên lai chuyển khoản</label>
                                <input type="file" name="receipt_image" id="receipt_image" 
                                       class="form-control-file" accept="image/*">
                                <small class="form-text text-muted">Chụp ảnh biên lai chuyển khoản để chúng tôi xác nhận nhanh hơn</small>
                            </div>

                            <div class="form-group">
                                <label for="customer_note">Ghi chú (nếu có)</label>
                                <textarea name="customer_note" id="customer_note" rows="3" 
                                          class="form-control" placeholder="Ghi chú thêm về giao dịch chuyển khoản..."></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" form="confirmTourPaymentForm" class="btn btn-success">
                            <i class="fas fa-paper-plane mr-2"></i>
                            Xác nhận chuyển khoản
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    // Thêm modal vào body nếu chưa có
    if (!document.getElementById('confirmTourPaymentModal')) {
        document.body.insertAdjacentHTML('beforeend', confirmForm);
    }

    // Hiển thị modal
    $('#confirmTourPaymentModal').modal('show');

    // Validate form khi submit
    document.getElementById('confirmTourPaymentForm').addEventListener('submit', function(e) {
        const bankName = document.getElementById('bank_name').value;
        const transferAmount = document.getElementById('transfer_amount').value;
        const transferDate = document.getElementById('transfer_date').value;

        if (!bankName || !transferAmount || !transferDate) {
            e.preventDefault();
            alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
            return;
        }

        if (parseFloat(transferAmount) !== {{ (int) $tempPaymentData['amount'] }}) {
            e.preventDefault();
            alert('Số tiền chuyển khoản phải bằng số tiền cần thanh toán!');
            return;
        }
    });
}
</script>

<style>
.form-control {
    border-radius: 0.375rem;
    border: 1px solid #ced4da;
    padding: 0.75rem;
    font-size: 1rem;
}

.form-control:focus {
    border-color: #C9A888;
    box-shadow: 0 0 0 0.2rem rgba(201, 168, 136, 0.25);
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection
