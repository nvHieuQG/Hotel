@extends('client.layouts.master')

@section('title', 'Chi tiết đặt phòng')

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
                            <span>Chi tiết đặt phòng</span>
                        </p>
                        <h3 class="mb-4 bread">Chi tiết đặt phòng #{{ $booking->booking_id }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="ftco-section bg-light" style="font-family: 'Segoe UI', sans-serif;">
        <div class="container">
            @if (session('success'))
                <div class="alert alert-success d-flex align-items-center justify-content-center mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger d-flex align-items-center justify-content-center mb-4" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger d-flex align-items-center justify-content-center mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="row justify-content-center">
                <div class="col-md-10">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h4 class="mb-0">
                                <i class="fas fa-calendar-check mr-2"></i>
                                Chi tiết đặt phòng #{{ $booking->booking_id }}
                            </h4>
                        </div>
                        <div class="card-body p-4">
                            <!-- Ảnh phòng -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
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
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-info-circle mr-2"></i>Thông tin đặt phòng
                                    </h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Ngày đặt:</strong></td>
                                            <td>{{ $booking->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Check-in:</strong></td>
                                            <td>{{ $booking->check_in_date->format('d/m/Y') }} từ 14:00</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Check-out:</strong></td>
                                            <td>{{ $booking->check_out_date->format('d/m/Y') }} trước 12:00</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td>
                                                <span class="badge bg-{{ $booking->status == 'pending' ? 'warning' : ($booking->status == 'confirmed' ? 'success' : ($booking->status == 'cancelled' ? 'danger' : 'primary')) }}">
                                                    {{ $booking->status_text ?? $booking->status }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tổng tiền:</strong></td>
                                            <td class="text-success"><strong>{{ number_format($booking->total_booking_price, 0, ',', '.') }} VNĐ</strong></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-bed mr-2"></i>Thông tin phòng
                                    </h6>
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Loại phòng:</strong></td>
                                            <td>{{ $booking->room->roomType->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Sức chứa:</strong></td>
                                            <td>{{ $booking->room->roomType->capacity ?? 'N/A' }} người</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ghi chú:</strong></td>
                                            <td>
                                                @php
                                                    $customerNotes = $booking->notes()->where('type', 'customer')->get();
                                                @endphp
                                                @if($customerNotes->count() > 0)
                                                    @foreach($customerNotes as $note)
                                                        {{ $note->content }}
                                                    @endforeach
                                                @else
                                                    Không có
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Thông tin thanh toán -->
                            @if($paymentHistory->count() > 0)
                            <hr class="my-4">
                            <div class="row">
                                <div class="col-12">
                                    <h6 class="text-primary mb-3">
                                        <i class="fas fa-credit-card mr-2"></i>Lịch sử thanh toán
                                    </h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Thời gian</th>
                                                    <th>Phương thức</th>
                                                    <th>Giá gốc</th>
                                                    <th>Khuyến mại</th>
                                                    <th>Thanh toán</th>
                                                    <th>Trạng thái</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($paymentHistory as $payment)
                                                <tr>
                                                    <td>{{ $payment->created_at->format('d/m/Y H:i') }}</td>
                                                    <td>
                    	                                @if($payment->method === 'credit_card')
                                                            <i class="fas fa-credit-card text-primary"></i> Thẻ tín dụng
                                                        @elseif($payment->method === 'bank_transfer')
                                                            <i class="fas fa-university text-success"></i> Chuyển khoản
                                                        @else
                                                            {{ ucfirst($payment->method) }}
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($booking->total_booking_price) }} VNĐ</td>
                                                    <td class="text-success">
                                                        -{{ number_format((float)($payment->discount_amount ?? 0)) }} VNĐ
                                                        @if(($payment->discount_amount ?? 0) > 0 && $payment->promotion)
                                                            <div class="mt-1">
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-gift"></i>
                                                                    {{ $payment->promotion->title }}
                                                                    @if($payment->promotion->code)
                                                                        ({{ $payment->promotion->code }})
                                                                    @endif
                                                                </span>
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td class="text-primary"><strong>{{ number_format((float)$payment->amount) }} VNĐ</strong></td>
                                                    <td>
                                                        <span class="badge bg-{{ $payment->status_color }}">
                                                            {{ $payment->status_text }}
                                                        </span>
                                                    </td>
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Nút điều hướng -->
                    <div class="text-center mt-4">
                        @if($canPay)
                            <a href="{{ route('confirm-info-payment', $booking->id) }}" class="btn btn-success btn-lg me-3 mb-2">
                                <i class="fas fa-credit-card mr-2"></i>Thanh toán ngay
                            </a>
                        @elseif($booking->hasSuccessfulPayment())
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> Đặt phòng này đã được thanh toán thành công!
                            </div>
                        @endif
                        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary btn-lg mb-2">
                            <i class="fas fa-arrow-left mr-2"></i>Quay lại
                        </a>
                    </div>

                    <!-- Ghi chú đặt phòng -->
                    <div class="mt-4">
                        <x-booking-notes :booking="$booking" :showAddButton="true" :showSearch="true" />
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
        
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .badge {
            font-size: 0.875em;
            padding: 0.5em 0.75em;
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