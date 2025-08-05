@extends('client.layouts.master')

@section('title', 'Thanh toán thất bại')

@section('content')
<div class="hero-wrap" style="background-image: url('/client/images/bg_1.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
            <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <h1 class="mb-4 bread">Thanh toán thất bại</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-body text-center p-5">
                        <div class="mb-4">
                            <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                        </div>
                        
                        <h3 class="text-danger mb-4">Thanh toán thất bại</h3>
                        
                        <p class="text-muted mb-4">{{ session('error', 'Có lỗi xảy ra trong quá trình thanh toán.') }}</p>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle mr-2"></i>Nguyên nhân có thể:</h6>
                            <ul class="text-left mb-0">
                                <li>Thông tin thẻ không chính xác</li>
                                <li>Thẻ bị khóa hoặc hết hạn</li>
                                <li>Số tiền vượt quá hạn mức</li>
                                <li>Lỗi kết nối mạng</li>
                                <li>Lỗi hệ thống thanh toán tạm thời</li>
                            </ul>
                        </div>

                        <div class="mt-4">
                            <a href="{{ route('booking') }}" class="btn btn-primary mr-3">
                                <i class="fas fa-redo mr-2"></i>Thử lại
                            </a>
                            <a href="{{ route('index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-home mr-2"></i>Về trang chủ
                            </a>
                        </div>


                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 