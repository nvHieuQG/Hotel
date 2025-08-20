@extends('client.layouts.master')

@section('title', 'Yêu cầu xuất hóa đơn VAT - Tour Booking')

@section('content')
<div class="hero-wrap" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center" data-scrollax-parent="true">
            <div class="col-md-9 ftco-animate text-center" data-scrollax=" properties: { translateY: '70%' }">
                <p class="breadcrumbs" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">
                    <span class="mr-2"><a href="{{ route('index') }}">Trang chủ</a></span>
                    <span class="mr-2"><a href="{{ route('tour-booking.index') }}">Tour Booking</a></span>
                    <span>Xuất hóa đơn VAT</span>
                </p>
                <h1 class="mb-3 bread" data-scrollax="properties: { translateY: '30%', opacity: 1.6 }">Yêu cầu xuất hóa đơn VAT</h1>
            </div>
        </div>
    </div>
</div>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header" style="background-color: #C9A888; color: white;">
                        <h4 class="mb-0">Yêu cầu xuất hóa đơn VAT - Tour Booking #{{ $tourBooking->booking_id }}</h4>
                    </div>
                    <div class="card-body">
                        <!-- Thông tin tour booking -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <h6>Thông tin tour booking:</h6>
                                <p><strong>Tên tour:</strong> {{ $tourBooking->tour_name }}</p>
                                <p><strong>Số khách:</strong> {{ $tourBooking->total_guests }} người</p>
                                <p><strong>Số phòng:</strong> {{ $tourBooking->total_rooms }} phòng</p>
                                <p><strong>Check-in:</strong> {{ $tourBooking->check_in_date->format('d/m/Y') }}</p>
                                <p><strong>Check-out:</strong> {{ $tourBooking->check_out_date->format('d/m/Y') }}</p>
                                <p><strong>Tổng tiền:</strong> {{ number_format($tourBooking->total_price, 0, ',', '.') }} VNĐ</p>
                                @if($tourBooking->promotion_discount > 0)
                                    <p><strong>Giảm giá:</strong> -{{ number_format($tourBooking->promotion_discount, 0, ',', '.') }} VNĐ</p>
                                    <p><strong>Giá cuối:</strong> {{ number_format($tourBooking->final_price, 0, ',', '.') }} VNĐ</p>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h6>Trạng thái:</h6>
                                <span class="badge badge-{{ $tourBooking->status === 'confirmed' ? 'success' : ($tourBooking->status === 'pending' ? 'warning' : 'secondary') }}">
                                    {{ $tourBooking->status_text }}
                                </span>
                                
                                @if($tourBooking->vat_invoice_number)
                                    <div class="mt-3">
                                        <h6>Hóa đơn VAT:</h6>
                                        <p class="text-success">
                                            <i class="fas fa-check-circle"></i> Đã xuất: {{ $tourBooking->vat_invoice_number }}
                                        </p>
                                        <a href="{{ route('tour-booking.vat-invoice.download', $tourBooking->id) }}" 
                                           class="btn btn-success btn-sm">
                                            <i class="fas fa-download"></i> Tải xuống
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <hr>

                        @if(!$tourBooking->vat_invoice_number)
                            <!-- Form yêu cầu xuất hóa đơn VAT -->
                            <form action="{{ route('tour-booking.vat-invoice.request', $tourBooking->id) }}" method="POST">
                                @csrf
                                
                                <div class="form-group">
                                    <label for="company_name">Tên công ty <span class="text-danger">*</span></label>
                                    <input type="text" name="company_name" id="company_name" class="form-control" 
                                           value="{{ old('company_name', $tourBooking->company_name) }}" required>
                                    @error('company_name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="company_tax_code">Mã số thuế <span class="text-danger">*</span></label>
                                    <input type="text" name="company_tax_code" id="company_tax_code" class="form-control" 
                                           value="{{ old('company_tax_code', $tourBooking->company_tax_code) }}" required>
                                    @error('company_tax_code')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="company_address">Địa chỉ công ty <span class="text-danger">*</span></label>
                                    <textarea name="company_address" id="company_address" class="form-control" rows="2" required>{{ old('company_address', $tourBooking->company_address) }}</textarea>
                                    @error('company_address')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_email">Email công ty <span class="text-danger">*</span></label>
                                            <input type="email" name="company_email" id="company_email" class="form-control" 
                                                   value="{{ old('company_email', $tourBooking->company_email) }}" required>
                                            @error('company_email')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="company_phone">Điện thoại công ty <span class="text-danger">*</span></label>
                                            <input type="text" name="company_phone" id="company_phone" class="form-control" 
                                                   value="{{ old('company_phone', $tourBooking->company_phone) }}" required>
                                            @error('company_phone')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Lưu ý:</strong> Hóa đơn VAT sẽ được gửi qua email công ty sau khi xử lý.
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn" style="background-color: #C9A888; color: white; border-color: #C9A888;">
                                        <i class="fas fa-paper-plane"></i> Gửi yêu cầu xuất hóa đơn VAT
                                    </button>
                                    <a href="{{ route('tour-booking.show', $tourBooking->id) }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Quay lại
                                    </a>
                                </div>
                            </form>
                        @else
                            <div class="text-center">
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle fa-2x mb-3"></i>
                                    <h5>Hóa đơn VAT đã được xuất!</h5>
                                    <p>Mã hóa đơn: <strong>{{ $tourBooking->vat_invoice_number }}</strong></p>
                                    <p>Ngày xuất: {{ $tourBooking->vat_invoice_created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                
                                <a href="{{ route('tour-booking.show', $tourBooking->id) }}" class="btn btn-primary">
                                    <i class="fas fa-arrow-left"></i> Quay lại
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
