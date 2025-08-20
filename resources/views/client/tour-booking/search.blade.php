@extends('client.layouts.master')

@section('title', 'Đặt phòng Tour du lịch')

@section('content')
<section class="hero-wrap hero-wrap-2" style="background-image: url('{{ asset('client/images/bg_1.jpg') }}');" data-stellar-background-ratio="0.5">
    <div class="overlay"></div>
    <div class="container">
        <div class="row no-gutters slider-text js-fullheight align-items-center justify-content-center">
            <div class="col-md-9 ftco-animate pb-5 text-center">
                <h1 class="mb-3 bread">Đặt phòng Tour du lịch</h1>
                <p class="breadcrumbs"><span class="mr-2"><a href="{{ route('index') }}">Trang chủ <i class="ion-ios-arrow-forward"></i></a></span> <span>Đặt phòng Tour</span></p>
            </div>
        </div>
    </div>
</section>

<section class="ftco-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Thông tin Tour du lịch</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('tour-booking.search.post') }}" method="POST" id="tourSearchForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="tour_name">Tên Tour <span class="text-danger">*</span></label>
                                        <input type="text" name="tour_name" id="tour_name" class="form-control" 
                                               value="{{ request('tour_name', 'Tour du lịch - ' . date('d/m/Y')) }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="total_guests">Tổng số khách <span class="text-danger">*</span></label>
                                        <input type="number" name="total_guests" id="total_guests" class="form-control" 
                                               value="{{ request('total_guests', 10) }}" 
                                               min="1" max="360" step="1" required
                                               placeholder="Nhập số khách (1-360)">
                                        <small class="form-text text-muted">
                                            Số khách tối đa: 360 người (dựa trên sức chứa hiện tại của khách sạn)
                                        </small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="check_in_date">Ngày Check-in <span class="text-danger">*</span></label>
                                        <input type="date" name="check_in_date" id="check_in_date" class="form-control" 
                                               value="{{ request('check_in_date', date('Y-m-d')) }}" 
                                               min="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="check_out_date">Ngày Check-out <span class="text-danger">*</span></label>
                                        <input type="date" name="check_out_date" id="check_out_date" class="form-control" 
                                               value="{{ request('check_out_date', date('Y-m-d', strtotime('+1 day'))) }}" 
                                               min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg px-5" id="searchBtn">
                                    <i class="fa fa-search"></i> Tìm phòng phù hợp
                                </button>
                            </div>
                        </form>

                        @if($errors->any())
                            <div class="alert alert-danger mt-3">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Thông tin sức chứa khách sạn</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Loại phòng hiện có:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>Phòng Đơn Tiêu Chuẩn:</strong> 2 người/phòng (60 phòng)</li>
                                    <li><strong>Phòng Đôi Tiêu Chuẩn:</strong> 4 người/phòng (60 phòng)</li>
                                    <li><strong>Phòng Deluxe:</strong> 3 người/phòng (60 phòng)</li>
                                    <li><strong>Phòng Suite:</strong> 4 người/phòng (60 phòng)</li>
                                    <li><strong>Phòng Gia Đình:</strong> 6 người/phòng (60 phòng)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>Tổng sức chứa:</h6>
                                <div class="alert alert-info">
                                    <strong>360 người</strong> (tối đa)
                                </div>
                                <small class="text-muted">
                                    * Sức chứa có thể thay đổi tùy thuộc vào tình trạng đặt phòng
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-4">
                    <div class="card-header">
                        <h4>Hướng dẫn đặt phòng Tour</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 text-center">
                                <div class="step-item">
                                    <div class="step-number">1</div>
                                    <h5>Nhập thông tin</h5>
                                    <p>Điền thông tin tour và số khách</p>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="step-item">
                                    <div class="step-number">2</div>
                                    <h5>Chọn phòng</h5>
                                    <p>Chọn loại phòng và số lượng</p>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="step-item">
                                    <div class="step-number">3</div>
                                    <h5>Xác nhận</h5>
                                    <p>Kiểm tra và xác nhận thông tin</p>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <div class="step-item">
                                    <div class="step-number">4</div>
                                    <h5>Thanh toán</h5>
                                    <p>Hoàn tất thanh toán</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const totalGuestsInput = document.getElementById('total_guests');
    const searchBtn = document.getElementById('searchBtn');
    const maxCapacity = 360;

    // Validate khi người dùng nhập
    totalGuestsInput.addEventListener('input', function() {
        const value = parseInt(this.value);
        
        if (value > maxCapacity) {
            this.setCustomValidity(`Số khách không được vượt quá ${maxCapacity} người`);
            this.classList.add('is-invalid');
        } else if (value < 1) {
            this.setCustomValidity('Số khách phải ít nhất 1 người');
            this.classList.add('is-invalid');
        } else {
            this.setCustomValidity('');
            this.classList.remove('is-invalid');
        }
    });

    // Validate khi submit form
    document.getElementById('tourSearchForm').addEventListener('submit', function(e) {
        const value = parseInt(totalGuestsInput.value);
        
        if (value > maxCapacity) {
            e.preventDefault();
            alert(`Số khách (${value}) vượt quá sức chứa tối đa của khách sạn (${maxCapacity} người).`);
            totalGuestsInput.focus();
            return false;
        }
        
        if (value < 1) {
            e.preventDefault();
            alert('Số khách phải ít nhất 1 người.');
            totalGuestsInput.focus();
            return false;
        }
    });

    // Hiển thị thông tin khi hover
    totalGuestsInput.addEventListener('focus', function() {
        this.title = `Nhập số khách từ 1 đến ${maxCapacity} người`;
    });
});
</script>

<style>
.step-item {
    padding: 20px;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    margin-bottom: 20px;
}

.step-number {
    width: 40px;
    height: 40px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin: 0 auto 15px;
}

.step-item h5 {
    color: #333;
    margin-bottom: 10px;
}

.step-item p {
    color: #666;
    font-size: 14px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkInDate = document.getElementById('check_in_date');
    const checkOutDate = document.getElementById('check_out_date');

    // Tự động cập nhật ngày check-out khi thay đổi check-in
    checkInDate.addEventListener('change', function() {
        const checkIn = new Date(this.value);
        const checkOut = new Date(checkIn);
        checkOut.setDate(checkOut.getDate() + 1);
        
        checkOutDate.min = checkOut.toISOString().split('T')[0];
        if (checkOutDate.value <= this.value) {
            checkOutDate.value = checkOut.toISOString().split('T')[0];
        }
    });

    // Validate form
    document.getElementById('tourSearchForm').addEventListener('submit', function(e) {
        const checkIn = new Date(checkInDate.value);
        const checkOut = new Date(checkOutDate.value);
        
        if (checkOut <= checkIn) {
            e.preventDefault();
            alert('Ngày check-out phải sau ngày check-in!');
            return false;
        }
    });
});
</script>
@endsection 