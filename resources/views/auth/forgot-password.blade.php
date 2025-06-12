@extends('client.layouts.master')

@section('title', 'Quên mật khẩu')

@section('content')
<div class="hero-wrap" style="background-image: url('client/images/bg_1.jpg');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text d-flex align-itemd-end justify-content-center">
            <div class="col-md-9 ftco-animate text-center d-flex align-items-end justify-content-center">
                <div class="text">
                    <p class="breadcrumbs mb-2"><span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span> <span>Quên mật khẩu</span></p>
                    <h1 class="mb-4 bread">Quên mật khẩu</h1>
                </div>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section contact-section bg-light">
    <div class="container">
        <div class="row block-9">
            <div class="col-md-6 mx-auto d-flex">
                <form method="POST" action="{{ route('password.email') }}" class="bg-white p-5 contact-form w-100">
                    @csrf
                    
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary py-3 px-5">Gửi link đặt lại mật khẩu</button>
                    </div>

                    <div class="form-group text-center">
                        <p>Đã nhớ mật khẩu? <a href="{{ route('login') }}">Đăng nhập</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection 