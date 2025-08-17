@extends('client.layouts.master')

@section('title', 'Chuyển khoản ngân hàng')

@section('content')
    <div class="hero-wrap" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text d-flex align-items-end justify-content-center">
                                            <div class="col-md-9 text-center d-flex align-items-end justify-content-center">
                                <div class="text">
                                    <p class="breadcrumbs mb-2">
                                        <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                                        <span class="mr-2"><a href="{{ route('payment-method', $booking->id) }}">Thanh toán</a></span>
                                        <span>Chuyển khoản ngân hàng</span>
                                    </p>
                                    <h3 class="mb-4 bread">Thông tin chuyển khoản</h3>
                                </div>
                            </div>
               
            </div>
        </div>
    </div>

    <section class="ftco-section bg-light">
        <div class="container">
            <div class="row">
                <div class="col-md-8">
                    <div class="card shadow-sm rounded-lg">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-university mr-2"></i>
                                Thông tin chuyển khoản ngân hàng
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Thông báo deadline thanh toán -->
                            <div class="alert alert-warning mb-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock mr-2"></i>
                                    <div>
                                        <strong>Thời gian thanh toán:</strong>
                                        <div class="text-danger font-weight-bold">30 phút</div>
                                        <small class="text-muted">Vui lòng hoàn tất thanh toán trong vòng 30 phút để giữ phòng</small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Thông tin đặt phòng -->
                            <div class="">
                                <h6 class="alert-heading">Thông tin đặt phòng</h6>
                                <p class="mb-1"><strong>Mã đặt phòng:</strong> {{ $booking->booking_id }}</p>
                                <p class="mb-1"><strong>Số tiền cần thanh toán:</strong> <span class="text-danger font-weight-bold">{{ number_format($payment->amount) }} VNĐ</span></p>
                                @if($payment->discount_amount > 0)
                                    <div class="mt-2 small">
                                        <div>Giá gốc: <strong>{{ number_format($booking->total_booking_price) }} VNĐ</strong></div>
                                        <div>Khuyến mại: <span class="text-success">-{{ number_format($payment->discount_amount) }} VNĐ</span></div>
                                        <div>Cần thanh toán: <strong class="text-primary">{{ number_format($payment->amount) }} VNĐ</strong></div>
                                    </div>
                                @endif
                                <p class="mb-0"><strong>Nội dung chuyển khoản:</strong> <code>Thanh toan dat phong {{ $booking->booking_id }}</code></p>
                            </div>

                            <!-- Danh sách ngân hàng -->
                            <div class="bank-list">
                                @foreach($bankInfo['banks'] as $index => $bank)
                                    <div class="bank-item mb-4 p-3 border rounded">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="bank-name">{{ $bank['name'] }}</h6>
                                                <div class="bank-details">
                                                    <p class="mb-1"><strong>Số tài khoản:</strong> <span class="text-primary">{{ $bank['account_number'] }}</span></p>
                                                    <p class="mb-1"><strong>Tên tài khoản:</strong> {{ $bank['account_name'] }}</p>
                                                    <p class="mb-1"><strong>Chi nhánh:</strong> {{ $bank['branch'] }}</p>
                                                    <p class="mb-0"><strong>Nội dung:</strong> <code>{{ $bank['transfer_content'] }} {{ $booking->booking_id }}</code></p>
                                                </div>
                                                <button class="btn btn-outline-primary btn-sm mt-2 copy-btn" 
                                                        data-clipboard-text="{{ $bank['account_number'] }}">
                                                    <i class="fas fa-copy mr-1"></i> Copy số tài khoản
                                                </button>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                                                <div class="qr-code-container">
                                    <img src="{{ $bank['qr_code'] }}" alt="QR Code {{ $bank['name'] }}" 
                                         class="img-fluid qr-code" style="max-width: 150px;">
                                    <p class="text-muted small mt-2">Quét mã QR để chuyển khoản</p>
                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Hướng dẫn -->
                            <div class="instructions mt-4">
                                <h6 class="text-dark mb-3">Hướng dẫn chuyển khoản:</h6>
                                <ol class="pl-3">
                                    @foreach($bankInfo['instructions'] as $instruction)
                                        <li class="mb-2">{{ $instruction }}</li>
                                    @endforeach
                                </ol>
                            </div>

                            <!-- Lưu ý -->
                            <div class="alert alert-warning mt-4">
                                <i class="fas-info-circle mr-2"></i>
                                {{ $bankInfo['note'] }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card shadow-sm rounded-lg">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-check-circle mr-2"></i>
                                Xác nhận chuyển khoản
                            </h6>
                        </div>
                        <div class="card-body">
                             <form action="{{ route('payment.bank-transfer.confirm', $booking->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="transaction_id" value="{{ $payment->transaction_id }}">
                                 @if($payment->promotion_id)
                                     <input type="hidden" name="promotion_id" value="{{ $payment->promotion_id }}">
                                 @endif
                                
                                <div class="form-group">
                                    <label for="bank_name">Ngân hàng đã chuyển khoản <span class="text-danger">*</span></label>
                                    <select name="bank_name" id="bank_name" class="form-control" required>
                                        <option value="">-- Chọn ngân hàng --</option>
                                        @foreach($bankInfo['banks'] as $bank)
                                            <option value="{{ $bank['name'] }}">{{ $bank['name'] }}</option>
                                        @endforeach
                                    </select>
                                                                 </div>

                                <div class="form-group">
                                    <label for="transfer_amount">Số tiền đã chuyển <span class="text-danger">*</span></label>
                                     <input type="number" name="transfer_amount" id="transfer_amount" 
                                           class="form-control" value="{{ (int) $payment->amount }}" required>
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

                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Xác nhận chuyển khoản
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Thông tin liên hệ -->
                    <div class="card mt-3 shadow-sm rounded-lg">
                        <div class="card-body">
                            <h6 class="text-dark mb-3">
                                <i class="fas fa-headset mr-2"></i>
                                Hỗ trợ
                            </h6>
                            <p class="mb-2"><i class="fas fa-phone mr-2"></i> Hotline: 1900-xxxx</p>
                            <p class="mb-2"><i class="fas fa-envelope mr-2"></i> Email: support@hotel.com</p>
                            <p class="mb-0"><i class="fas fa-clock mr-2"></i> Thời gian: 8:00 - 22:00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @section('styles')
    <style>
        .bank-item {
            transition: all 0.3s ease;
            border: 1px solid #e9ecef !important;
        }

        .bank-item:hover {
            border-color: #007bff !important;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.1);
        }

        .bank-name {
            color: #007bff;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .qr-code {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 10px;
            background: white;
        }

        .copy-btn {
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            transform: translateY(-1px);
        }

        .alert {
            border-radius: 8px;
        }

        .form-control {
            border-radius: 6px;
        }

        .btn {
            border-radius: 6px;
            font-weight: 500;
        }

        .card {
            border: none;
            border-radius: 12px;
        }

        .card-header {
            border-radius: 12px 12px 0 0 !important;
        }
        

    </style>
    @endsection

    @section('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
    <script>

        

        
        document.addEventListener('DOMContentLoaded', function() {
            // Khởi tạo clipboard
            new ClipboardJS('.copy-btn');

            // Thông báo khi copy thành công
            document.querySelectorAll('.copy-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const originalText = this.innerHTML;
                    this.innerHTML = '<i class="fas fa-check mr-1"></i> Đã copy';
                    this.classList.remove('btn-outline-primary');
                    this.classList.add('btn-success');
                    
                    setTimeout(() => {
                        this.innerHTML = originalText;
                        this.classList.remove('btn-success');
                        this.classList.add('btn-outline-primary');
                    }, 2000);
                });
            });

            // Validate form
            const form = document.querySelector('form');
            form.addEventListener('submit', function(e) {
                const bankName = document.getElementById('bank_name').value;
                const transferAmount = document.getElementById('transfer_amount').value;
                const transferDate = document.getElementById('transfer_date').value;

                if (!bankName || !transferAmount || !transferDate) {
                    e.preventDefault();
                    alert('Vui lòng điền đầy đủ thông tin bắt buộc!');
                    return;
                }

                if (parseFloat(transferAmount) !== {{ (int) $payment->amount }}) {
                    e.preventDefault();
                    alert('Số tiền chuyển khoản phải bằng số tiền cần thanh toán!');
                    return;
                }
            });
        });
    </script>
    @endsection
@endsection 