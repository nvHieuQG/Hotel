@extends('client.layouts.master')

@section('title', 'Xác minh Email')

@section('content')
<div class="hero-wrap" style="background-image: url('client/images/bg_1.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
            <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <p class="breadcrumbs mb-2"><span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span> <span>Xác minh Email</span></p>
                    <h1 class="mb-4 bread">Xác minh Email</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section contact-section bg-light">
    <div class="container">
        <div class="row block-9">
            <div class="col-md-6 mx-auto d-flex">
                <div class="bg-white p-5 contact-form w-100">
                    @if (session('status') == 'verification-link-sent')
                        <div class="alert alert-success">
                            Một liên kết xác minh mới đã được gửi đến địa chỉ email của bạn.
                        </div>
                    @endif
                    
                    <div class="text-center mb-4">
                        <h2>Xác minh địa chỉ email</h2>
                        <p>Cảm ơn bạn đã đăng ký! Trước khi bắt đầu, bạn cần xác minh địa chỉ email của mình bằng cách nhấp vào liên kết chúng tôi vừa gửi cho bạn qua email. Nếu bạn không nhận được email, chúng tôi sẽ sẵn lòng gửi lại.</p>
                    </div>
                    
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <div class="text-center">
                            <button type="submit" class="btn btn-primary py-3 px-5">Gửi lại email xác minh</button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-link">Đăng xuất</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection 