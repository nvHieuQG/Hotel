<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title', 'Deluxe - Free Bootstrap 4 Template')</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <link href="https://fonts.googleapis.com/css?family=Poppins:200,300,400,500,600,700" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('client/css/open-iconic-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/owl.theme.default.min.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/aos.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/ionicons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/bootstrap-datepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/jquery.timepicker.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/flaticon.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/icomoon.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('client/css/reviews.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .alert-container {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }
        .custom-alert {
            margin-bottom: 10px;
            padding: 15px;
            border-radius: 4px;
            opacity: 0.9;
            transition: opacity 0.3s;
        }
        .custom-alert:hover {
            opacity: 1;
        }
        .alert-success {
            background-color: #28a745;
            color: white;
        }
        .alert-danger {
            background-color: #dc3545;
            color: white;
        }
        .alert-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .alert-info {
            background-color: #17a2b8;
            color: white;
        }
        .close-alert {
            float: right;
            font-weight: bold;
            cursor: pointer;
            color: inherit;
            border: none;
            background: transparent;
        }
        
        /* Rating stars styles */
        .rating-input {
            display: inline-block;
            direction: rtl;
        }
        
        .rating-radio {
            display: none;
        }
        
        .rating-star {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }
        
        .rating-star:hover,
        .rating-star:hover ~ .rating-star,
        .rating-star.active,
        .rating-star.active ~ .rating-star {
            color: #ffc107;
        }
        
        .rating-input input[type="radio"]:checked ~ .rating-star {
            color: #ffc107;
        }
    </style>
</head>
<body>

    @include('client.layouts.blocks.header')

    <!-- Hiển thị thông báo -->
    @if(session('success') || session('error') || session('warning') || session('info'))
    <div class="alert-container">
        @if(session('success'))
        <div class="custom-alert alert-success">
            <button type="button" class="close-alert">&times;</button>
            {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="custom-alert alert-danger">
            <button type="button" class="close-alert">&times;</button>
            {{ session('error') }}
        </div>
        @endif

        @if(session('warning'))
        <div class="custom-alert alert-warning">
            <button type="button" class="close-alert">&times;</button>
            {{ session('warning') }}
        </div>
        @endif

        @if(session('info'))
        <div class="custom-alert alert-info">
            <button type="button" class="close-alert">&times;</button>
            {{ session('info') }}
        </div>
        @endif
    </div>
    @endif

    @yield('content')

    @include('client.layouts.blocks.footer')

    <script src="{{ asset('client/js/jquery.min.js') }}"></script>
    <script src="{{ asset('client/js/jquery-migrate-3.0.1.min.js') }}"></script>
    <script src="{{ asset('client/js/popper.min.js') }}"></script>
    <script src="{{ asset('client/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('client/js/jquery.easing.1.3.js') }}"></script>
    <script src="{{ asset('client/js/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('client/js/jquery.stellar.min.js') }}"></script>
    <script src="{{ asset('client/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('client/js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('client/js/aos.js') }}"></script>
    <script src="{{ asset('client/js/jquery.animateNumber.min.js') }}"></script>
    <script src="{{ asset('client/js/bootstrap-datepicker.js') }}"></script>
    <script src="{{ asset('client/js/jquery.timepicker.min.js') }}"></script>
    <script src="{{ asset('client/js/scrollax.min.js') }}"></script>
    <script src="{{ asset('client/js/google-map.js') }}"></script>
    <script src="{{ asset('client/js/main.js') }}"></script>
    
    <script>
        // Xử lý đóng thông báo
        document.addEventListener('DOMContentLoaded', function() {
            var closeButtons = document.querySelectorAll('.close-alert');
            closeButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    var alert = this.parentElement;
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                });
            });

            // Tự động ẩn thông báo sau 5 giây
            setTimeout(function() {
                var alerts = document.querySelectorAll('.custom-alert');
                alerts.forEach(function(alert) {
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.style.display = 'none';
                    }, 300);
                });
            }, 5000);
        });
    </script>
    
    @yield('scripts')
    
    <!-- Review Modal -->
    <div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reviewModalLabel">Đánh giá loại phòng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="reviewForm">
                    <div class="modal-body">
                        <input type="hidden" id="reviewId" name="review_id">
                        <input type="hidden" id="roomTypeId" name="room_type_id">
                        
                        <!-- Điểm đánh giá tổng thể -->
                        <div class="form-group">
                            <label for="rating">Điểm đánh giá tổng thể <span class="text-danger">*</span></label>
                            <div class="rating-input">
                                @for ($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="rating" value="{{ $i }}" id="star{{ $i }}" class="rating-radio" required>
                                    <label for="star{{ $i }}" class="rating-star">☆</label>
                                @endfor
                            </div>
                        </div>
                        
                        <!-- Đánh giá chi tiết -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cleanliness_rating">Vệ sinh</label>
                                    <select name="cleanliness_rating" id="cleanliness_rating" class="form-control">
                                        <option value="">Chọn điểm</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }} sao</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="comfort_rating">Tiện nghi</label>
                                    <select name="comfort_rating" id="comfort_rating" class="form-control">
                                        <option value="">Chọn điểm</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }} sao</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="location_rating">Vị trí</label>
                                    <select name="location_rating" id="location_rating" class="form-control">
                                        <option value="">Chọn điểm</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }} sao</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="facilities_rating">Cơ sở vật chất</label>
                                    <select name="facilities_rating" id="facilities_rating" class="form-control">
                                        <option value="">Chọn điểm</option>
                                        @for ($i = 1; $i <= 5; $i++)
                                            <option value="{{ $i }}">{{ $i }} sao</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="value_rating">Giá trị</label>
                            <select name="value_rating" id="value_rating" class="form-control">
                                <option value="">Chọn điểm</option>
                                @for ($i = 1; $i <= 5; $i++)
                                    <option value="{{ $i }}">{{ $i }} sao</option>
                                @endfor
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="comment">Bình luận</label>
                            <textarea name="comment" id="comment" class="form-control" rows="4" placeholder="Chia sẻ trải nghiệm của bạn..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="is_anonymous" name="is_anonymous" value="1">
                                <label class="custom-control-label" for="is_anonymous">Đánh giá ẩn danh</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Gửi đánh giá</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Review Modal JavaScript
        $(document).ready(function() {
            // Tạo đánh giá mới
            $('.create-review-btn').click(function() {
                var roomTypeId = $(this).data('room-type-id');
                $('#reviewModalLabel').text('Tạo đánh giá mới');
                $('#reviewId').val('');
                $('#roomTypeId').val(roomTypeId);
                $('#reviewForm')[0].reset();
                $('.rating-star').removeClass('active');
                $('#reviewModal').modal('show');
            });
            
            // Sửa đánh giá
            $('.edit-review-btn').click(function() {
                var reviewId = $(this).data('review-id');
                $('#reviewModalLabel').text('Chỉnh sửa đánh giá');
                $('#reviewId').val(reviewId);
                $('#roomTypeId').val('');
                
                // Load dữ liệu đánh giá từ endpoint JSON
                $.get('/user/reviews/' + reviewId + '/data', function(data) {
                    var review = data.review;
                    
                    $('input[name="rating"][value="' + review.rating + '"]').prop('checked', true);
                    $('.rating-star').removeClass('active');
                    $('input[name="rating"]:checked').next().addClass('active');
                    $('#cleanliness_rating').val(review.cleanliness_rating);
                    $('#comfort_rating').val(review.comfort_rating);
                    $('#location_rating').val(review.location_rating);
                    $('#facilities_rating').val(review.facilities_rating);
                    $('#value_rating').val(review.value_rating);
                    $('#comment').val(review.comment);
                    $('#is_anonymous').prop('checked', review.is_anonymous == 1);
                    $('#reviewModal').modal('show');
                });
            });
            
            // Xóa đánh giá
            $('.delete-review-btn').click(function() {
                var reviewId = $(this).data('review-id');
                if (confirm('Bạn có chắc chắn muốn xóa đánh giá này?')) {
                    $.ajax({
                        url: '/room-type-reviews/' + reviewId,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            alert(response.message);
                            location.reload();
                        },
                        error: function(xhr) {
                            alert('Có lỗi xảy ra: ' + xhr.responseJSON.error);
                        }
                    });
                }
            });
            
            // Submit form
            $('#reviewForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                var reviewId = $('#reviewId').val();
                var roomTypeId = $('#roomTypeId').val();
                var url = reviewId ? '/room-type-reviews/' + reviewId : '/room-type-reviews/' + roomTypeId;
                var method = reviewId ? 'PUT' : 'POST';
                
                $.ajax({
                    url: url,
                    type: method,
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        alert(response.message);
                        $('#reviewModal').modal('hide');
                        location.reload();
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            var errorMessage = '';
                            for (var field in errors) {
                                errorMessage += errors[field][0] + '\n';
                            }
                            alert('Lỗi validation:\n' + errorMessage);
                        } else {
                            alert('Có lỗi xảy ra: ' + xhr.responseJSON.error);
                        }
                    }
                });
            });
            
            // Rating stars
            $('.rating-star').click(function() {
                var radio = $(this).prev('input[type="radio"]');
                radio.prop('checked', true);
                $('.rating-star').removeClass('active');
                $(this).addClass('active');
            });
        });
    </script>
</body>
</html>
