@extends('client.layouts.master')

@section('title', 'Thanh toán thành công')

@section('content')
    <div class="hero-wrap"
        style="background-image: url('{{ asset('client/images/bg_1.jpg') }}'); font-family: 'Segoe UI', sans-serif;">
        <div class="overlay"></div>
        <div class="container">
            <div class="row no-gutters slider-text d-flex align-items-end justify-content-center">
                <div class="col-md-9 text-center d-flex align-items-end justify-content-center">
                    <div class="text">
                        <p class="breadcrumbs mb-2">
                            <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                            <span class="mr-2"><a href="{{ route('booking.detail', $booking->id) }}">Đặt phòng</a></span>
                            <span>Thanh toán thành công</span>
                        </p>
                        <h3 class="mb-4 bread">Thanh toán thành công!</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="ftco-section bg-light" style="font-family: 'Segoe UI', sans-serif;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card border-success shadow-sm">
                        <div class="card-header bg-success text-white text-center">
                            <h4 class="mb-0">
                                <i class="fas fa-check-circle"></i> Thanh toán thành công!
                            </h4>
                        </div>
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                                <h5 class="text-success mt-3">Cảm ơn bạn đã thanh toán!</h5>
                                <p class="text-muted">Đặt phòng của bạn đã được xác nhận và thanh toán thành công.</p>
                                
                                <!-- Thông báo thanh toán đúng thời gian -->
                                <div class="alert alert-success mt-3" style="max-width: 500px; margin: 0 auto;">
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-clock text-success mr-2"></i>
                                        <div>
                                            <strong>Thanh toán đúng thời gian!</strong>
                                            <div class="text-success small">
                                                Bạn đã hoàn tất thanh toán trong thời gian quy định (30 phút)
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                

                            </div>
                            
                            <!-- Ảnh phòng -->
                            <div class="row justify-content-center mb-4">
                                <div class="col-md-8">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary mb-3">
                                                <i class="fas fa-image mr-2"></i>Ảnh phòng
                                            </h6>
                                            <div class="room-image-container">
                                                @if($booking->room->primaryImage)
                                                    <img src="{{ asset('storage/' . $booking->room->primaryImage->image_url) }}" 
                                                         alt="Ảnh phòng {{ $booking->room->name }}" 
                                                         class="img-fluid rounded shadow-sm" 
                                                         style="max-height: 300px; width: 100%; object-fit: cover;">
                                                @elseif($booking->room->firstImage)
                                                    <img src="{{ asset('storage/' . $booking->room->firstImage->image_url) }}" 
                                                         alt="Ảnh phòng {{ $booking->room->name }}" 
                                                         class="img-fluid rounded shadow-sm" 
                                                         style="max-height: 300px; width: 100%; object-fit: cover;">
                                                @else
                                                    <div class="bg-light rounded d-flex justify-content-center align-items-center" 
                                                         style="height: 300px;">
                                                        <div class="text-center text-muted">
                                                            <i class="fas fa-image fa-3x mb-3"></i>
                                                            <p>Chưa có ảnh phòng</p>
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Thông tin giao dịch -->
                            <div class="row justify-content-center mb-4">
                                <div class="col-md-8">
                                    <div class="card border-0 bg-light">
                                        <div class="card-body">
                                            <h6 class="card-title text-primary mb-3">
                                                <i class="fas fa-receipt mr-2"></i>Thông tin giao dịch
                                            </h6>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <table class="table table-borderless">
                                                        <tr>
                                                            <td><strong>Mã đặt phòng:</strong></td>
                                                            <td>{{ $booking->booking_id }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Phòng:</strong></td>
                                                            <td>{{ $booking->room->name ?? ($booking->roomType->name ?? 'Không xác định') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Số tiền:</strong></td>
                                                            <td class="text-success"><strong>{{ $latestPayment->formatted_amount ?? number_format($latestPayment->amount) }} VNĐ</strong></td>
                                                        </tr>
                                                    </table>
                                                </div>
                                                <div class="col-md-6">
                                                    <table class="table table-borderless">
                                                        <tr>
                                                            <td><strong>Phương thức:</strong></td>
                                                            <td>
                                                                @if($latestPayment->payment_method === 'credit_card')
                                                                    <i class="fas fa-credit-card text-primary"></i> Thẻ tín dụng/ghi nợ
                                                                    @if(isset($latestPayment->gateway_response['card_info']['last4']))
                                                                        <br><small class="text-muted">Thẻ {{ $latestPayment->gateway_response['card_info']['brand'] }} ****{{ $latestPayment->gateway_response['card_info']['last4'] }}</small>
                                                                    @endif
                                                                @elseif($latestPayment->payment_method === 'bank_transfer')
                                                                    <i class="fas fa-university text-primary"></i> Chuyển khoản ngân hàng
                                                                @else
                                                                    {{ ucfirst($latestPayment->payment_method ?? 'Không xác định') }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td><strong>Thời gian:</strong></td>
                                                            <td>
                                                                @if($latestPayment->paid_at)
                                                                    {{ $latestPayment->paid_at->format('d/m/Y H:i:s') }}
                                                                @else
                                                                    {{ $latestPayment->created_at->format('d/m/Y H:i:s') }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        @if($latestPayment->transaction_id)
                                                        <tr>
                                                            <td><strong>Mã giao dịch:</strong></td>
                                                            <td><code>{{ $latestPayment->transaction_id }}</code></td>
                                                        </tr>
                                                        @endif
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Thông tin check-in -->
                            <div class="alert alert-info">
                                <h6 class="mb-3">
                                    <i class="fas fa-info-circle mr-2"></i>Thông tin quan trọng
                                </h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul class="mb-0">
                                            <li><strong>Check-in:</strong> {{ $booking->check_in_date ? \Carbon\Carbon::parse($booking->check_in_date)->format('d/m/Y') : 'Chưa xác định' }} từ 14:00</li>
                                            <li><strong>Check-out:</strong> {{ $booking->check_out_date ? \Carbon\Carbon::parse($booking->check_out_date)->format('d/m/Y') : 'Chưa xác định' }} trước 12:00</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="mb-0">
                                            <li>Mang theo giấy tờ tùy thân khi check-in</li>
                                            <li>Nếu có thay đổi, vui lòng liên hệ khách sạn trước 24h</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Nút điều hướng -->
                            <div class="text-center mt-4">
                                <a href="{{ route('booking.detail', $booking->id) }}" class="btn btn-primary btn-lg me-3 mb-2">
                                    <i class="fas fa-eye mr-2"></i>Xem chi tiết đặt phòng
                                </a>
                                <a href="{{ route('user.bookings') }}" class="btn btn-outline-primary btn-lg me-3 mb-2">
                                    <i class="fas fa-list mr-2"></i>Danh sách đặt phòng
                                </a>
                                <a href="{{ route('index') }}" class="btn btn-outline-secondary btn-lg mb-2">
                                    <i class="fas fa-home mr-2"></i>Về trang chủ
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @section('styles')
    <style>
        .card {
            border-radius: 15px;
            overflow: hidden;
        }
        
        .card-header {
            border-bottom: none;
            padding: 1.5rem;
        }
        
        .btn-lg {
            padding: 12px 24px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .btn-lg:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
        }
        
        .table td {
            padding: 0.5rem 0;
            border: none;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            border: none;
            border-radius: 10px;
        }
        
        code {
            background-color: #f8f9fa;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .btn-lg {
                padding: 10px 20px;
                font-size: 1rem;
                margin-bottom: 10px;
            }
            
            .card-body {
                padding: 2rem;
            }
        }
    </style>
    @endsection
@endsection 