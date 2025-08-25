<!DOCTYPE html>
<html lang="en">
<head>
    <title>@yield('title', 'MARRON')</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
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
        .chat-widget {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    font-family: 'Roboto', sans-serif;
}

.chat-button {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #D2691E 0%, #B8860B 100%);
    border: none;
    color: white;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(210, 105, 30, 0.3);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
}

.chat-button:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(210, 105, 30, 0.4);
}

.chat-button .notification-badge {
    position: absolute;
    top: -5px;
    right: -5px;
    background: #F44336;
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    display: none;
}

.chat-box {
    position: fixed;
    bottom: 90px;
    right: 20px;
    width: 350px;
    height: 500px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    display: none;
    flex-direction: column;
    overflow: hidden;
    border: 1px solid #E0E0E0;
    animation: slideIn 0.3s ease;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.chat-header {
    background: linear-gradient(135deg, #D2691E 0%, #B8860B 100%);
    color: white;
    padding: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 16px 16px 0 0;
}

.chat-header-left {
    display: flex;
    align-items: center;
    gap: 12px;
}

.chat-logo {
    width: 32px;
    height: 32px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 14px;
}

.chat-header-info h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
}

.chat-status {
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 6px;
    opacity: 0.9;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #4CAF50;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.close-chat {
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: background 0.2s;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.close-chat:hover {
    background: rgba(255, 255, 255, 0.2);
}

.chat-messages {
    flex: 1;
    padding: 20px;
    overflow-y: auto;
    background: #F8F9FA;
    scroll-behavior: smooth;
}

.chat-messages::-webkit-scrollbar {
    width: 6px;
}

.chat-messages::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.chat-messages::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.message {
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
    animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.message.sent {
    align-items: flex-end;
}

.message.received {
    align-items: flex-start;
}

.message-bubble {
    max-width: 85%;
    padding: 12px 16px;
    border-radius: 20px;
    font-size: 14px;
    line-height: 1.4;
    word-wrap: break-word;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.message.sent .message-bubble {
    background: linear-gradient(135deg, #D2691E 0%, #B8860B 100%);
    color: white;
    border-bottom-right-radius: 6px;
}

.message.received .message-bubble {
    background: white;
    color: #333;
    border: 1px solid #E0E0E0;
    border-bottom-left-radius: 6px;
}

.message-time {
    font-size: 11px;
    color: #9E9E9E;
    margin-top: 6px;
    text-align: center;
}

.welcome-message {
    text-align: center;
    padding: 20px;
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

.welcome-message .welcome-icon {
    font-size: 48px;
    color: #D2691E;
    margin-bottom: 12px;
    opacity: 0.7;
}

.chat-input-container {
    padding: 20px;
    border-top: 1px solid #E0E0E0;
    background: white;
    border-radius: 0 0 16px 16px;
}

/* File preview styles */
.file-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #e3f2fd;
    border: 1px solid #bbdefb;
    border-radius: 8px;
    margin: 8px 20px;
    max-width: calc(100% - 40px);
}

.file-preview-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1976d2;
}

.file-preview-name {
    flex: 1;
    font-size: 12px;
    color: #1976d2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-preview-remove {
    width: 20px;
    height: 20px;
    border: none;
    background: #ffebee;
    color: #d32f2f;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.2s;
}

.file-preview-remove:hover {
    background: #ffcdd2;
    transform: scale(1.1);
}

/* Image display in chat */
.message-bubble img {
    max-width: 200px;
    max-height: 150px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
}

.message-bubble img:hover {
    transform: scale(1.05);
}

/* Modal popup cho ảnh */
.image-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}

.image-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 90%;
    max-height: 90%;
    text-align: center;
}

.image-modal img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.image-modal-close {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 10000;
}

.image-modal-close:hover {
    color: #ccc;
}

.chat-input-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #FDF5E6;
    border-radius: 18px;
    padding: 4px 10px;
    border: 1px solid #DEB887;
    transition: border-color 0.2s;
}

.chat-input-wrapper:focus-within {
    border-color: #D2691E;
    box-shadow: 0 0 0 3px rgba(210, 105, 30, 0.1);
}

.chat-input {
    flex: 1;
    border: none;
    background: transparent;
    outline: none;
    font-size: 14px;
    resize: none;
    max-height: 60px;
    min-height: 16px;
    padding: 4px 0;
    line-height: 1.4;
    transition: all 0.3s ease;
}

.chat-input::placeholder {
    color: #9E9E9E;
}

.chat-attachments {
    display: flex;
    gap: 8px;
}

.attachment-btn {
    width: 26px;
    height: 26px;
    border: none;
    border-radius: 50%;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
    color: #8B4513;
    font-size: 13px;
}

.attachment-btn:hover {
    background: rgba(0, 0, 0, 0.1);
    color: #D2691E;
}

.send-btn {
    width: 30px;
    height: 30px;
    border: none;
    border-radius: 50%;
    background: #D2691E;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
    box-shadow: 0 2px 8px rgba(210, 105, 30, 0.3);
}

.send-btn:hover {
    background: #B8860B;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(210, 105, 30, 0.4);
}

.send-btn:disabled {
    background: #9E9E9E;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

/* Admin-style notification for chat errors */
.chat-error {
    background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
    color: #c53030;
    padding: 16px 20px;
    border-radius: 12px;
    font-size: 13px;
    margin: 12px 0;
    border: 1px solid #feb2b2;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 12px rgba(197, 48, 48, 0.1);
    position: relative;
    overflow: hidden;
}

/* Drag & Drop visual feedback */
.chat-input.dragover {
    border: 2px dashed #007bff !important;
    background-color: #f8f9fa !important;
    border-radius: 8px;
}

.chat-error::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #e53e3e 0%, #c53030 100%);
}

.chat-error i {
    font-size: 18px;
    color: #e53e3e;
    flex-shrink: 0;
}

.chat-error span {
    flex: 1;
    line-height: 1.4;
    font-weight: 500;
}

/* Success notification style */
.chat-success {
    background: linear-gradient(135deg, #f0fff4 0%, #c6f6d5 100%);
    color: #22543d;
    padding: 16px 20px;
    border-radius: 12px;
    font-size: 13px;
    margin: 12px 0;
    border: 1px solid #9ae6b4;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 12px rgba(34, 84, 61, 0.1);
    position: relative;
    overflow: hidden;
}

.chat-success::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #38a169 0%, #2f855a 100%);
}

.chat-success i {
    font-size: 18px;
    color: #38a169;
    flex-shrink: 0;
}

.chat-success span {
    flex: 1;
    line-height: 1.4;
    font-weight: 500;
}

/* Info notification style */
.chat-info {
    background: linear-gradient(135deg, #ebf8ff 0%, #bee3f8 100%);
    color: #2a4365;
    padding: 16px 20px;
    border-radius: 12px;
    font-size: 13px;
    margin: 12px 0;
    border: 1px solid #90cdf4;
    display: flex;
    align-items: center;
    gap: 12px;
    animation: slideIn 0.3s ease;
    box-shadow: 0 4px 12px rgba(42, 67, 101, 0.1);
    position: relative;
    overflow: hidden;
}

.chat-info::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: linear-gradient(135deg, #3182ce 0%, #2c5282 100%);
}

.chat-info i {
    font-size: 18px;
    color: #3182ce;
    flex-shrink: 0;
}

.chat-info span {
    flex: 1;
    line-height: 1.4;
    font-weight: 500;
}

/* Responsive */
@media (max-width: 480px) {
    .chat-box {
        width: calc(100vw - 40px);
        right: 20px;
        left: 20px;
        height: 60vh;
    }

    .chat-button {
        width: 56px;
        height: 56px;
        font-size: 22px;
    }
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

    <!-- Toast Container -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <!-- Toast notifications will be inserted here -->
    </div>

    <script src="{{ asset('client/js/jquery.min.js') }}"></script>
    <script src="{{ asset('client/js/jquery-migrate-3.0.1.min.js') }}"></script>
    <script src="{{ asset('client/js/popper.min.js') }}"></script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
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
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="reviewModalLabel">
                        <i class="fas fa-star mr-2"></i>Đánh giá loại phòng
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="reviewForm" class="review-form-modern">
                    <div class="modal-body">
                        <input type="hidden" id="reviewId" name="review_id">
                        <input type="hidden" id="roomTypeId" name="room_type_id">

                        <!-- Rating tổng thể -->
                        <div class="form-group mb-4">
                            <label class="form-label font-weight-bold text-dark mb-3">
                                <i class="fas fa-star text-warning mr-2"></i>Đánh giá tổng thể
                                <span class="text-danger">*</span>
                            </label>
                            <div class="rating-container">
                                <div class="rating-stars" data-rating="0">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <input type="radio" name="rating" value="{{ $i }}" id="modal_star{{ $i }}" class="rating-input" required>
                                        <label for="modal_star{{ $i }}" class="rating-star" data-value="{{ $i }}">
                                            <i class="fas fa-star"></i>
                                        </label>
                                    @endfor
                                </div>
                                <div class="rating-text mt-2">
                                    <span class="text-muted">Chọn số sao để đánh giá</span>
                                </div>
                            </div>
                        </div>

                        <!-- Đánh giá chi tiết -->
                        <div class="detailed-ratings mb-4">
                            <h6 class="font-weight-bold text-dark mb-3">
                                <i class="fas fa-chart-bar text-primary mr-2"></i>Đánh giá chi tiết
                                <small class="text-muted font-weight-normal">(không bắt buộc)</small>
                            </h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="rating-item mb-3">
                                        <label class="rating-label">
                                            <i class="fas fa-broom text-success mr-2"></i>Vệ sinh
                                        </label>
                                        <div class="rating-stars-sm" data-rating="0">
                                            @for ($i = 5; $i >= 1; $i--)
                                                <input type="radio" name="cleanliness_rating" value="{{ $i }}" id="modal_cleanliness_star{{ $i }}" class="rating-input-sm">
                                                <label for="modal_cleanliness_star{{ $i }}" class="rating-star-sm" data-value="{{ $i }}">
                                                    <i class="fas fa-star"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="rating-item mb-3">
                                        <label class="rating-label">
                                            <i class="fas fa-couch text-info mr-2"></i>Thoải mái
                                        </label>
                                        <div class="rating-stars-sm" data-rating="0">
                                            @for ($i = 5; $i >= 1; $i--)
                                                <input type="radio" name="comfort_rating" value="{{ $i }}" id="modal_comfort_star{{ $i }}" class="rating-input-sm">
                                                <label for="modal_comfort_star{{ $i }}" class="rating-star-sm" data-value="{{ $i }}">
                                                    <i class="fas fa-star"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="rating-item mb-3">
                                        <label class="rating-label">
                                            <i class="fas fa-map-marker-alt text-warning mr-2"></i>Vị trí
                                        </label>
                                        <div class="rating-stars-sm" data-rating="0">
                                            @for ($i = 5; $i >= 1; $i--)
                                                <input type="radio" name="location_rating" value="{{ $i }}" id="modal_location_star{{ $i }}" class="rating-input-sm">
                                                <label for="modal_location_star{{ $i }}" class="rating-star-sm" data-value="{{ $i }}">
                                                    <i class="fas fa-star"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="rating-item mb-3">
                                        <label class="rating-label">
                                            <i class="fas fa-wifi text-primary mr-2"></i>Tiện nghi
                                        </label>
                                        <div class="rating-stars-sm" data-rating="0">
                                            @for ($i = 5; $i >= 1; $i--)
                                                <input type="radio" name="facilities_rating" value="{{ $i }}" id="modal_facilities_star{{ $i }}" class="rating-input-sm">
                                                <label for="modal_facilities_star{{ $i }}" class="rating-star-sm" data-value="{{ $i }}">
                                                    <i class="fas fa-star"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="rating-item mb-3">
                                        <label class="rating-label">
                                            <i class="fas fa-dollar-sign text-success mr-2"></i>Giá trị
                                        </label>
                                        <div class="rating-stars-sm" data-rating="0">
                                            @for ($i = 5; $i >= 1; $i--)
                                                <input type="radio" name="value_rating" value="{{ $i }}" id="modal_value_star{{ $i }}" class="rating-input-sm">
                                                <label for="modal_value_star{{ $i }}" class="rating-star-sm" data-value="{{ $i }}">
                                                    <i class="fas fa-star"></i>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bình luận -->
                        <div class="form-group mb-4">
                            <label for="comment" class="form-label font-weight-bold text-dark mb-2">
                                <i class="fas fa-comment text-primary mr-2"></i>Bình luận của bạn
                                <span class="text-danger">*</span>
                            </label>
                            <textarea name="comment" id="comment" class="form-control border-0 bg-light" rows="4"
                                      placeholder="Chia sẻ trải nghiệm của bạn về phòng này... (tối thiểu 10 ký tự)" required></textarea>
                            <div class="form-text">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Bình luận sẽ giúp khách hàng khác hiểu rõ hơn về chất lượng phòng
                                </small>
                            </div>
                        </div>

                        <!-- Tùy chọn ẩn danh -->
                        <div class="form-group mb-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_anonymous" name="is_anonymous" value="1">
                                <label class="custom-control-label" for="is_anonymous">
                                    <i class="fas fa-user-secret text-muted mr-2"></i>
                                    Đánh giá ẩn danh
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Khi bật, tên của bạn sẽ không hiển thị trong đánh giá
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-paper-plane mr-2"></i>
                            <span class="btn-text">Gửi đánh giá</span>
                            <span class="btn-loading d-none">
                                <i class="fas fa-spinner fa-spin mr-2"></i>Đang gửi...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Review Modal JavaScript
        $(document).ready(function() {
            // Rating stars cho modal
            $('#reviewModal').on('shown.bs.modal', function() {
                // Rating stars chính
                $('#reviewModal .rating-stars').each(function() {
                    const container = $(this);
                    const stars = container.find('.rating-star');
                    const text = container.siblings('.rating-text');
                    const ratingTexts = [
                        'Chọn số sao để đánh giá',
                        'Rất không hài lòng',
                        'Không hài lòng',
                        'Bình thường',
                        'Hài lòng',
                        'Rất hài lòng'
                    ];

                    stars.off('click').on('click', function() {
                        const value = $(this).data('value');
                        container.attr('data-rating', value);
                        text.text(ratingTexts[value]);

                        // Reset tất cả stars
                        stars.find('i').removeClass('text-warning').addClass('text-muted');

                        // Highlight stars đã chọn
                        stars.each(function() {
                            const starValue = $(this).data('value');
                            if (starValue <= value) {
                                $(this).find('i').removeClass('text-muted').addClass('text-warning');
                            }
                        });
                    });

                    // Hover effect
                    stars.off('mouseenter mouseleave').on('mouseenter', function() {
                        const value = $(this).data('value');
                        stars.find('i').removeClass('text-warning').addClass('text-muted');
                        stars.each(function() {
                            const starValue = $(this).data('value');
                            if (starValue <= value) {
                                $(this).find('i').removeClass('text-muted').addClass('text-warning');
                            }
                        });
                    }).on('mouseleave', function() {
                        const currentRating = container.attr('data-rating') || 0;
                        stars.find('i').removeClass('text-warning').addClass('text-muted');
                        stars.each(function() {
                            const starValue = $(this).data('value');
                            if (starValue <= currentRating) {
                                $(this).find('i').removeClass('text-muted').addClass('text-warning');
                            }
                        });
                    });
                });

                // Rating stars nhỏ
                $('#reviewModal .rating-stars-sm').each(function() {
                    const container = $(this);
                    const stars = container.find('.rating-star-sm');

                    stars.off('click').on('click', function() {
                        const value = $(this).data('value');
                        container.attr('data-rating', value);

                        // Reset tất cả stars
                        stars.find('i').removeClass('text-warning').addClass('text-muted');

                        // Highlight stars đã chọn
                        stars.each(function() {
                            const starValue = $(this).data('value');
                            if (starValue <= value) {
                                $(this).find('i').removeClass('text-muted').addClass('text-warning');
                            }
                        });
                    });

                    // Hover effect
                    stars.off('mouseenter mouseleave').on('mouseenter', function() {
                        const value = $(this).data('value');
                        stars.find('i').removeClass('text-warning').addClass('text-muted');
                        stars.each(function() {
                            const starValue = $(this).data('value');
                            if (starValue <= value) {
                                $(this).find('i').removeClass('text-muted').addClass('text-warning');
                            }
                        });
                    }).on('mouseleave', function() {
                        const currentRating = container.attr('data-rating') || 0;
                        stars.find('i').removeClass('text-warning').addClass('text-muted');
                        stars.each(function() {
                            const starValue = $(this).data('value');
                            if (starValue <= currentRating) {
                                $(this).find('i').removeClass('text-muted').addClass('text-warning');
                            }
                        });
                    });
                });
            });

            // Tạo đánh giá mới
            $('.create-review-btn').click(function() {
                var roomTypeId = $(this).data('room-type-id');
                $('#reviewModalLabel').html('<i class="fas fa-star mr-2"></i>Tạo đánh giá mới');
                $('#reviewId').val('');
                $('#roomTypeId').val(roomTypeId);
                $('#reviewForm')[0].reset();
                $('#reviewModal .rating-stars').attr('data-rating', '0');
                $('#reviewModal .rating-stars-sm').attr('data-rating', '0');
                $('#reviewModal .rating-star i, #reviewModal .rating-star-sm i').removeClass('text-warning').addClass('text-muted');
                $('#reviewModal .rating-text').text('Chọn số sao để đánh giá');
                $('#reviewModal').modal('show');
            });

            // Sửa đánh giá
            $('.edit-review-btn').click(function() {
                var reviewId = $(this).data('review-id');
                $('#reviewModalLabel').html('<i class="fas fa-edit mr-2"></i>Chỉnh sửa đánh giá');
                $('#reviewId').val(reviewId);
                $('#roomTypeId').val('');

                // Load dữ liệu đánh giá từ endpoint JSON
                $.get('/user/reviews/' + reviewId + '/data', function(data) {
                    var review = data.review;

                    // Set rating tổng thể
                    $('#reviewModal .rating-stars').attr('data-rating', review.rating);
                    $('#reviewModal .rating-star i').removeClass('text-warning').addClass('text-muted');
                    $('#reviewModal .rating-star').each(function() {
                        const starValue = $(this).data('value');
                        if (starValue <= review.rating) {
                            $(this).find('i').removeClass('text-muted').addClass('text-warning');
                        }
                    });

                    // Set rating chi tiết
                    if (review.cleanliness_rating) {
                        $('#reviewModal .rating-stars-sm').eq(0).attr('data-rating', review.cleanliness_rating);
                        $('#reviewModal .rating-star-sm').eq(0).find('i').removeClass('text-warning').addClass('text-muted');
                        $('#reviewModal .rating-star-sm').each(function() {
                            const starValue = $(this).data('value');
                            if (starValue <= review.cleanliness_rating) {
                                $(this).find('i').removeClass('text-muted').addClass('text-warning');
                            }
                        });
                    }

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
                            showToast(response.message, 'success');
                            location.reload();
                        },
                        error: function(xhr) {
                            showToast('Có lỗi xảy ra: ' + xhr.responseJSON.error, 'danger');
                        }
                    });
                }
            });

            // Submit form
            $('#reviewForm').submit(function(e) {
                e.preventDefault();

                const form = $(this);
                const submitBtn = form.find('button[type="submit"]');
                const btnText = submitBtn.find('.btn-text');
                const btnLoading = submitBtn.find('.btn-loading');

                // Kiểm tra rating tổng thể
                const overallRating = form.find('.rating-stars').attr('data-rating');
                if (!overallRating || overallRating == '0') {
                    showToast('Vui lòng chọn đánh giá tổng thể!', 'warning');
                    return;
                }

                // Kiểm tra comment
                const comment = form.find('#comment').val().trim();
                if (comment.length < 10) {
                    showToast('Bình luận phải có ít nhất 10 ký tự!', 'warning');
                    return;
                }

                // Disable button và hiển thị loading
                submitBtn.prop('disabled', true);
                btnText.addClass('d-none');
                btnLoading.removeClass('d-none');

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
                        showToast(response.message, 'success');
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
                            showToast('Lỗi validation:\n' + errorMessage, 'danger');
                        } else {
                            showToast('Có lỗi xảy ra: ' + xhr.responseJSON.error, 'danger');
                        }
                    },
                    complete: function() {
                        // Enable button và khôi phục text
                        submitBtn.prop('disabled', false);
                        btnText.removeClass('d-none');
                        btnLoading.addClass('d-none');
                    }
                });
            });

            // Character counter cho comment
            $('#reviewModal #comment').on('input', function() {
                const length = $(this).val().length;
                const minLength = 10;
                const maxLength = 1000;

                if (length < minLength) {
                    $(this).addClass('is-invalid').removeClass('is-valid');
                } else if (length > maxLength) {
                    $(this).addClass('is-invalid').removeClass('is-valid');
                } else {
                    $(this).removeClass('is-invalid').addClass('is-valid');
                }
            });
        });

        // Toast notification function
        function showToast(message, type = 'info') {
            const toast = $(`
                <div class="toast-notification toast-notification-${type}" role="alert">
                    <div class="toast-header">
                        <i class="fas fa-${type === 'success' ? 'check-circle text-success' : type === 'warning' ? 'exclamation-triangle text-warning' : type === 'danger' ? 'times-circle text-danger' : 'info-circle text-info'} mr-2"></i>
                        <strong class="mr-auto">Thông báo</strong>
                        <button type="button" class="ml-2 mb-1 close" data-dismiss="toast">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `);

            // Thêm toast vào container
            if ($('#toast-container').length === 0) {
                $('body').append('<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
            }

            $('#toast-container').append(toast);
            toast.toast({ delay: 5000 }).toast('show');

            // Tự động xóa sau khi ẩn
            toast.on('hidden.bs.toast', function() {
                $(this).remove();
            });
        }
    </script>

@auth
<!-- Modern Chat Widget -->
<div class="chat-widget">
    <button id="openChatModal" class="chat-button">
        <i class="fas fa-comments"></i>
        <span class="notification-badge">2</span>
    </button>

    <div id="chatBox" class="chat-box">
        <div class="chat-header">
            <div class="chat-header-left">
                <div class="chat-logo">M</div>
                <div class="chat-header-info">
                    <h3>Hỗ trợ khách hàng</h3>
                    <div class="chat-status">
                        <div class="status-indicator"></div>
                        <span>Online</span>
                    </div>
                </div>
            </div>
            <button id="closeChatBox" class="close-chat">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <div id="chatMessages" class="chat-messages">
            @php
                // Lấy conversation của user hiện tại thông qua SupportService
                $currentUserId = Auth::id();
                $supportService = app(\App\Services\SupportService::class);
                $latestMessage = $supportService->getUserConversation($currentUserId);

                if ($latestMessage) {
                    $conversationId = $latestMessage->conversation_id;
                    // Lấy tất cả tin nhắn trong conversation này
                    $messages = $supportService->getUserConversationMessages($currentUserId);

                    // Debug info
                    // echo "<!-- Debug: User ID: $currentUserId, Conversation ID: $conversationId, Messages count: " . $messages->count() . " -->";
                } else {
                    $conversationId = null;
                    $messages = collect();
                    // echo "<!-- Debug: User ID: $currentUserId, No existing conversation -->";
                }
            @endphp
            @if($messages && $messages->count() > 0)
                @foreach($messages as $msg)
                    <div class="message {{ $msg->sender_type == 'user' ? 'sent' : 'received' }}" data-message-id="{{ $msg->id }}">
                        @if(!empty(trim($msg->message)))
                            <div class="message-bubble">{{ $msg->message }}</div>
                        @endif
                        @if(!empty($msg->attachment_path) && \Illuminate\Support\Str::startsWith((string)$msg->attachment_type, 'image'))
                            <div class="message-bubble" onclick="openImageModal('{{ asset('storage/'.$msg->attachment_path) }}')" style="cursor: pointer;">
                                <img src="{{ asset('storage/'.$msg->attachment_path) }}" alt="attachment" style="max-width:200px; max-height:150px; border-radius:8px;" />
                            </div>
                        @elseif(!empty($msg->attachment_path))
                            <div class="message-bubble">
                                <a href="{{ asset('storage/'.$msg->attachment_path) }}" target="_blank" rel="noopener">{{ $msg->attachment_name ?? 'Tệp đính kèm' }}</a>
                            </div>
                        @endif
                        <div class="message-time">{{ $msg->created_at->format('H:i') }}</div>
                    </div>
                @endforeach
            @else
                <div class="welcome-message">
                    <div class="welcome-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <p>Xin chào! Chúng tôi có thể giúp gì cho bạn?</p>
                    <p style="font-size: 12px; opacity: 0.7;">Hãy gửi tin nhắn để bắt đầu cuộc trò chuyện</p>
                </div>
            @endif
        </div>

        <div class="chat-input-container">
            <form id="chatForm" enctype="multipart/form-data">
                @csrf
                <div class="chat-input-wrapper">
                    <textarea id="chatInput" name="message" class="chat-input" placeholder="Nhập tin nhắn..."></textarea>
                    <div class="chat-attachments">
                        <label for="attachmentInput" class="attachment-btn" title="Đính kèm file" style="cursor: pointer;">
                            <i class="fas fa-paperclip"></i>
                        </label>
                        <input type="file" id="attachmentInput" name="attachment" accept="image/*,application/pdf,application/zip,text/plain" style="display: none;" />
                    </div>
                    <button type="submit" id="sendChatBtn" class="send-btn" title="Gửi tin nhắn">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>

                <!-- File preview area -->
                <div id="filePreview" class="file-preview" style="display: none;">
                    <div class="file-preview-icon">
                        <i class="fas fa-file"></i>
                    </div>
                    <div class="file-preview-name" id="fileName"></div>
                    <button type="button" class="file-preview-remove" id="removeFile" title="Xóa tệp">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

     <input type="hidden" id="conversationIdInput" value="{{ $conversationId ?? '' }}">
</div>

<!-- Modal popup cho ảnh -->
<div id="imageModal" class="image-modal">
    <span class="image-modal-close">&times;</span>
    <div class="image-modal-content">
        <img id="modalImage" src="" alt="Ảnh lớn" />
    </div>
</div>
<script>
  const openBtn = document.getElementById('openChatModal');
  const chatBox = document.getElementById('chatBox');
  const closeBtn = document.getElementById('closeChatBox');
  const chatInput = document.getElementById('chatInput');
  const chatForm = document.getElementById('chatForm');
  const chatMessages = document.querySelector('#chatMessages');
  const conversationIdInput = document.getElementById('conversationIdInput');

  // File upload elements
  const attachmentInput = document.getElementById('attachmentInput');
  const filePreview = document.getElementById('filePreview');
  const fileName = document.getElementById('fileName');
  const removeFile = document.getElementById('removeFile');

  // Modal elements
  const imageModal = document.getElementById('imageModal');
  const modalImage = document.getElementById('modalImage');
  const modalClose = document.querySelector('.image-modal-close');

  // Debug info
  console.log('Chat initialized for user:', {{ Auth::id() }});
  console.log('Conversation ID:', conversationIdInput.value);

  // Biến để lưu trạng thái realtime
  let isRealtimeEnabled = false;
  let lastMessageId = 0;
  let isSending = false;

  // Khởi tạo lastMessageId từ tin nhắn cuối cùng
  const lastMessage = chatMessages.querySelector('.message[data-message-id]:last-child');
  if (lastMessage) {
    lastMessageId = parseInt(lastMessage.getAttribute('data-message-id'));
    console.log('Last message ID:', lastMessageId);
  }

  openBtn.onclick = function() {
    chatBox.style.display = 'flex';
    setTimeout(() => { chatInput.focus(); }, 200);

    // Bắt đầu realtime nếu đã có conversation
    const conversationId = conversationIdInput.value;
    if(conversationId) {
      console.log('Starting realtime for conversation:', conversationId);
      startRealtimeChat();
      showChatInfo('Đã kết nối với cuộc trò chuyện!');
    } else {
      showChatInfo('Chào mừng! Hãy gửi tin nhắn để bắt đầu.');
    }
  };

  closeBtn.onclick = function() {
    chatBox.style.display = 'none';
    stopRealtimeChat();
  };

  // Hàm gửi tin nhắn
  function sendMessage(message, attachment = null) {
    if(isSending) return;

    isSending = true;
    const sendBtn = document.getElementById('sendChatBtn');
    sendBtn.disabled = true;
    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    const conversationId = conversationIdInput.value;
    let url = '';

    // Tạo FormData nếu có file, JSON nếu không có file
    let requestData;
    let headers;

    if (attachment) {
      // Gửi file với FormData
      requestData = new FormData();
      requestData.append('message', message);
      requestData.append('attachment', attachment);
      requestData.append('_token', '{{ csrf_token() }}');
      headers = {
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      };
    } else {
      // Gửi text với JSON
      requestData = JSON.stringify({
        message: message,
        _token: '{{ csrf_token() }}'
      });
      headers = {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}',
        'Accept': 'application/json'
      };
    }

    if(conversationId) {
      url = '/support/conversation/' + conversationId + '/message';
      console.log('Sending message to existing conversation:', conversationId);
    } else {
      url = '/support/message';
      console.log('Creating new conversation for user:', {{ Auth::id() }});
    }

    fetch(url, {
      method: 'POST',
      headers: headers,
      body: requestData
    })
    .then(res => {
      if (!res.ok) {
        throw new Error(`HTTP error! status: ${res.status}`);
      }
      return res.json();
    })
    .then(data => {
      console.log('Send message response:', data);
      if(data.success) {
        // Cập nhật conversation ID nếu là tin nhắn đầu tiên
        if(!conversationId && data.conversation_id) {
          conversationIdInput.value = data.conversation_id;
          console.log('New conversation created:', data.conversation_id);
          showChatSuccess('Cuộc trò chuyện đã được tạo!');
        }

                // Thêm tin nhắn vào UI
        addMessageToUI(message, 'user', data.message_id, data.attachment);
        chatInput.value = '';
        chatInput.style.height = 'auto';

        // Xóa file preview nếu có
        if (attachment) {
          clearFilePreview();
        }

        // Cập nhật conversation ID nếu là tin nhắn đầu tiên
        if(!conversationId && data.conversation_id) {
          conversationIdInput.value = data.conversation_id;
          console.log('New conversation created:', data.conversation_id);
          showChatSuccess('Cuộc trò chuyện đã được tạo!');
        }

        // Bắt đầu realtime sau khi gửi tin nhắn đầu tiên
        if(!isRealtimeEnabled) {
          startRealtimeChat();
          showChatInfo('Đã bật chế độ realtime!');
        }
      } else {
        showChatError(data.message || 'Có lỗi khi gửi tin nhắn!');
      }
    })
    .catch((error) => {
      console.error('Chat error:', error);
      showChatError('Kết nối mạng có vấn đề. Vui lòng thử lại sau!');
    })
    .finally(() => {
      isSending = false;
      sendBtn.disabled = false;
      sendBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
    });
  }

  // Hàm hiển thị lỗi chat
  function showChatError(message) {
    const errorDiv = document.createElement('div');
    errorDiv.className = 'chat-error';
    errorDiv.innerHTML = `
      <i class="fas fa-exclamation-triangle"></i>
      <span>${message}</span>
    `;

    chatMessages.insertBefore(errorDiv, chatMessages.firstChild);

    setTimeout(() => {
      if (errorDiv.parentNode) {
        errorDiv.remove();
      }
    }, 5000);
  }

  // Hàm hiển thị thông báo thành công
  function showChatSuccess(message) {
    const successDiv = document.createElement('div');
    successDiv.className = 'chat-success';
    successDiv.innerHTML = `
      <i class="fas fa-check-circle"></i>
      <span>${message}</span>
    `;

    chatMessages.insertBefore(successDiv, chatMessages.firstChild);

    setTimeout(() => {
      if (successDiv.parentNode) {
        successDiv.remove();
      }
    }, 3000);
  }

  // Hàm hiển thị thông báo thông tin
  function showChatInfo(message) {
    const infoDiv = document.createElement('div');
    infoDiv.className = 'chat-info';
    infoDiv.innerHTML = `
      <i class="fas fa-info-circle"></i>
      <span>${message}</span>
    `;

    chatMessages.insertBefore(infoDiv, chatMessages.firstChild);

    setTimeout(() => {
      if (infoDiv.parentNode) {
        infoDiv.remove();
      }
    }, 4000);
  }

  // Auto-resize textarea
  chatInput.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 100) + 'px';
  });

  // Hàm thêm tin nhắn vào UI
  function addMessageToUI(message, senderType, messageId = null, attachment = null) {
    // Cho phép gửi chỉ attachment (không cần text)
    if((!message || message.trim() === '') && !attachment) return;

    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${senderType === 'user' ? 'sent' : 'received'}`;

    if(messageId) {
      messageDiv.setAttribute('data-message-id', messageId);
    }

    // Thêm tin nhắn text nếu có
    if (message && message.trim()) {
      const messageBubble = document.createElement('div');
      messageBubble.className = 'message-bubble';
      messageBubble.textContent = message.trim();
      messageDiv.appendChild(messageBubble);
    }

    // Thêm file đính kèm nếu có
    if (attachment) {
      if (attachment.type && attachment.type.startsWith('image/')) {
        // Hiển thị ảnh
        const imageBubble = document.createElement('div');
        imageBubble.className = 'message-bubble';
        imageBubble.style.cursor = 'pointer';
        imageBubble.onclick = () => openImageModal(attachment.url);

        const img = document.createElement('img');
        img.src = attachment.url;
        img.alt = 'attachment';
        img.style.maxWidth = '200px';
        img.style.maxHeight = '150px';
        img.style.borderRadius = '8px';

        imageBubble.appendChild(img);
        messageDiv.appendChild(imageBubble);
      } else {
        // Hiển thị file khác
        const fileBubble = document.createElement('div');
        fileBubble.className = 'message-bubble';

        const fileLink = document.createElement('a');
        fileLink.href = attachment.url;
        fileLink.target = '_blank';
        fileLink.rel = 'noopener';
        fileLink.textContent = attachment.name || 'Tệp đính kèm';

        fileBubble.appendChild(fileLink);
        messageDiv.appendChild(fileBubble);
      }
    }

    const messageTime = document.createElement('div');
    messageTime.className = 'message-time';
    messageTime.textContent = new Date().toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});

    messageDiv.appendChild(messageTime);
    chatMessages.appendChild(messageDiv);

    // Scroll xuống tin nhắn mới nhất
    chatMessages.scrollTop = chatMessages.scrollHeight;
  }

  // Hàm bắt đầu realtime chat
  function startRealtimeChat() {
    if(isRealtimeEnabled) return;

    isRealtimeEnabled = true;
    console.log('Starting realtime chat...');

    // Cập nhật lastMessageId nếu chưa có
    if(lastMessageId === 0) {
      const lastMessage = chatMessages.querySelector('.message[data-message-id]:last-child');
      if(lastMessage) {
        lastMessageId = parseInt(lastMessage.getAttribute('data-message-id'));
        console.log('Updated last message ID:', lastMessageId);
      }
    }

    // Bắt đầu polling
    checkNewMessages();
  }

  // Hàm dừng realtime chat
  function stopRealtimeChat() {
    if(window.realtimeInterval) {
      clearInterval(window.realtimeInterval);
      window.realtimeInterval = null;
    }
    isRealtimeEnabled = false;
    console.log('Stopped realtime chat');
  }

  // Hàm kiểm tra tin nhắn mới
  function checkNewMessages() {
    if(!isRealtimeEnabled) return;

    const conversationId = conversationIdInput.value;
    if(!conversationId) return;

    console.log('Checking new messages for conversation:', conversationId, 'last ID:', lastMessageId);

    fetch(`/support/conversation/${conversationId}/messages?last_id=${lastMessageId}`, {
      headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
    .then(res => res.json())
    .then(data => {
      if(data.success && data.messages && data.messages.length > 0) {
        console.log('Received new messages:', data.messages.length);

        data.messages.forEach(msg => {
          // Kiểm tra xem tin nhắn đã tồn tại chưa để tránh duplicate
          const existingMessage = chatMessages.querySelector(`[data-message-id="${msg.id}"]`);
          if(!existingMessage && msg.id > lastMessageId) {
            // Tạo attachment object nếu có
            let attachment = null;
            if (msg.attachment) {
              attachment = {
                url: msg.attachment.url,
                name: msg.attachment.name,
                type: msg.attachment.type
              };
            }

            addMessageToUI(msg.message, msg.sender_type, msg.id, attachment);
            lastMessageId = Math.max(lastMessageId, msg.id);

            // Thông báo khi nhận tin nhắn từ admin
            if(msg.sender_type === 'admin') {
              showChatSuccess('Có tin nhắn mới từ hỗ trợ viên!');
            }
          }
        });
      }
    })
    .catch(err => {
      console.error('Error checking new messages:', err);
    })
    .finally(() => {
      // Tiếp tục polling nếu realtime vẫn được bật
      if(isRealtimeEnabled) {
        setTimeout(checkNewMessages, 3000);
      }
    });
  }

    // Form submit handler
  chatForm.onsubmit = function(e) {
    e.preventDefault();
    const msg = chatInput.value.trim();
    const attachment = attachmentInput.files[0];

    if ((!msg || msg.trim() === '') && !attachment) {
      showChatError('Vui lòng nhập tin nhắn hoặc chọn tệp đính kèm');
      return;
    }

    if (isSending) return;

    // Gửi tin nhắn với attachment (có thể chỉ có attachment)
    sendMessage(msg || '', attachment);
  };

  // Enter key handler
  chatInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && !e.shiftKey) {
      e.preventDefault();
      const msg = this.value.trim();
      const attachment = attachmentInput.files[0];
      if ((!msg || msg.trim() === '') && !attachment) return;
      if (isSending) return;
      sendMessage(msg || '', attachment);
    }
  });

  // Paste image handler - cho phép copy & paste ảnh trực tiếp
  chatInput.addEventListener('paste', function(e) {
    const items = (e.clipboardData || e.originalEvent.clipboardData).items;

    for (let item of items) {
      if (item.type.indexOf('image') !== -1) {
        e.preventDefault();

        const file = item.getAsFile();
        if (file) {
          // Tạo FileList object để gán vào input
          const dataTransfer = new DataTransfer();
          dataTransfer.items.add(file);
          attachmentInput.files = dataTransfer.files;

          // Hiển thị preview
          showFilePreview(file);

          // Bỏ required cho message input
          chatInput.removeAttribute('required');

          showChatSuccess('Đã paste ảnh! Bạn có thể gửi ngay hoặc thêm tin nhắn.');
        }
        break;
      }
    }
  });

    // File input event listeners
  attachmentInput.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
      showFilePreview(file);
      // Bỏ required cho message input nếu có file
      chatInput.removeAttribute('required');
    }
  });

  // Remove file button
  removeFile.addEventListener('click', function() {
    clearFilePreview();
    // Khôi phục required cho message input
    chatInput.setAttribute('required', 'required');
  });

  // Drag and drop ảnh trực tiếp vào input text
  chatInput.addEventListener('dragover', function(e) {
    e.preventDefault();
    this.style.borderColor = '#007bff';
    this.style.backgroundColor = '#f8f9fa';
  });

  chatInput.addEventListener('dragleave', function(e) {
    e.preventDefault();
    this.style.borderColor = '';
    this.style.backgroundColor = '';
  });

  chatInput.addEventListener('drop', function(e) {
    e.preventDefault();
    this.style.borderColor = '';
    this.style.backgroundColor = '';

    const files = e.dataTransfer.files;
    if (files.length > 0) {
      const file = files[0];
      if (file.type.startsWith('image/')) {
        // Tạo FileList object để gán vào input
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        attachmentInput.files = dataTransfer.files;

        // Hiển thị preview
        showFilePreview(file);

        // Bỏ required cho message input
        chatInput.removeAttribute('required');

        showChatSuccess('Đã kéo thả ảnh! Bạn có thể gửi ngay hoặc thêm tin nhắn.');
      }
    }
  });

  // Modal event listeners
  modalClose.addEventListener('click', function() {
    imageModal.style.display = 'none';
  });

  // Đóng modal khi click bên ngoài
  imageModal.addEventListener('click', function(e) {
    if (e.target === imageModal) {
      imageModal.style.display = 'none';
    }
  });

  // Đóng modal bằng ESC key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && imageModal.style.display === 'block') {
      imageModal.style.display = 'none';
    }
  });

  // Dừng realtime khi tab không active
  document.addEventListener('visibilitychange', function() {
    if (document.hidden) {
      stopRealtimeChat();
    } else {
      const conversationId = conversationIdInput.value;
      if(conversationId && chatBox.style.display === 'flex') {
        startRealtimeChat();
      }
    }
  });

  // Hàm hiển thị file preview
  function showFilePreview(file) {
    fileName.textContent = file.name;

    // Thay đổi icon tùy theo loại file
    const iconElement = filePreview.querySelector('.file-preview-icon i');
    if (file.type.startsWith('image/')) {
      iconElement.className = 'fas fa-image';
    } else if (file.type === 'application/pdf') {
      iconElement.className = 'fas fa-file-pdf';
    } else if (file.type === 'application/zip') {
      iconElement.className = 'fas fa-file-archive';
    } else if (file.type === 'text/plain') {
      iconElement.className = 'fas fa-file-alt';
    } else {
      iconElement.className = 'fas fa-file';
    }

    filePreview.style.display = 'flex';
  }

  // Hàm xóa file preview
  function clearFilePreview() {
    attachmentInput.value = '';
    filePreview.style.display = 'none';
    fileName.textContent = '';
  }

  // Hàm mở modal ảnh
  function openImageModal(imageSrc) {
    modalImage.src = imageSrc;
    imageModal.style.display = 'block';
  }
</script>
@endauth

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xử lý form Check Availability (tìm kiếm phòng)
    const availabilityForm = document.getElementById('availabilityForm');
    const checkInDate = document.getElementById('check_in_date');
    const checkOutDate = document.getElementById('check_out_date');
    const guestsSelect = document.getElementById('guests');

    // Validation cho ngày check-in và check-out
    function validateDates() {
        const checkIn = new Date(checkInDate.value);
        const checkOut = new Date(checkOutDate.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);

        if (checkIn < today) {
            alert('Ngày check-in không thể là ngày trong quá khứ!');
            checkInDate.focus();
            return false;
        }

        if (checkOut <= checkIn) {
            alert('Ngày check-out phải sau ngày check-in!');
            checkOutDate.focus();
            return false;
        }

        return true;
    }

    // Validation cho form tìm kiếm
    availabilityForm.addEventListener('submit', function(e) {
        if (!checkInDate.value) {
            alert('Vui lòng chọn ngày check-in!');
            checkInDate.focus();
            e.preventDefault();
            return;
        }

        if (!checkOutDate.value) {
            alert('Vui lòng chọn ngày check-out!');
            checkOutDate.focus();
            e.preventDefault();
            return;
        }

        if (!validateDates()) {
            e.preventDefault();
            return;
        }

        // Form hợp lệ, cho phép submit để tìm kiếm phòng
        console.log('Tìm kiếm phòng với thông tin:', {
            check_in_date: checkInDate.value,
            check_out_date: checkOutDate.value,
            guests: guestsSelect.value
        });
    });

    // Tự động cập nhật ngày check-out khi thay đổi ngày check-in
    checkInDate.addEventListener('change', function() {
        if (this.value) {
            const checkIn = new Date(this.value);
            const nextDay = new Date(checkIn);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutDate.min = nextDay.toISOString().split('T')[0];

            // Nếu ngày check-out hiện tại nhỏ hơn ngày check-in + 1, cập nhật
            if (checkOutDate.value && new Date(checkOutDate.value) <= checkIn) {
                checkOutDate.value = nextDay.toISOString().split('T')[0];
            }
        }
    });

    // Hiển thị thông báo khi thay đổi số khách
    guestsSelect.addEventListener('change', function() {
        if (this.value) {
            console.log('Số khách đã chọn:', this.value);
        }
    });
});
</script>

    <style>
    /* Review form styles for modal */
    .review-form-modern {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .rating-container {
        text-align: center;
    }

    .rating-stars {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: 8px;
    }

    .rating-input, .rating-input-sm {
        display: none;
    }

    .rating-star {
        font-size: 2.5rem;
        color: #e0e0e0;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 4px;
        border-radius: 50%;
    }

    .rating-star:hover,
    .rating-star:hover ~ .rating-star,
    .rating-input:checked ~ .rating-star {
        color: #ffc107;
        transform: scale(1.1);
    }

    .rating-star i {
        transition: all 0.2s ease;
    }

    .rating-text {
        font-size: 0.9rem;
        min-height: 20px;
    }

    /* Rating stars nhỏ cho đánh giá chi tiết */
    .rating-stars-sm {
        display: inline-flex;
        flex-direction: row-reverse;
        gap: 4px;
    }

    .rating-star-sm {
        font-size: 1.2rem;
        color: #e0e0e0;
        cursor: pointer;
        transition: all 0.2s ease;
        padding: 2px;
    }

    .rating-star-sm:hover,
    .rating-star-sm:hover ~ .rating-star-sm,
    .rating-input-sm:checked ~ .rating-star-sm {
        color: #ffc107;
        transform: scale(1.1);
    }

    .rating-item {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
    }

    .rating-item:hover {
        background: #ffffff;
        border-color: #007bff;
        box-shadow: 0 2px 8px rgba(0,123,255,0.1);
    }

    .rating-label {
        display: block;
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
        font-size: 0.9rem;
    }

    .form-control {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.2s ease;
        font-size: 0.95rem;
    }

    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
        background-color: #ffffff;
    }

    .custom-switch .custom-control-label::before {
        border-radius: 20px;
        height: 24px;
        width: 44px;
    }

    .custom-switch .custom-control-label::after {
        border-radius: 50%;
        height: 18px;
        width: 18px;
        top: 3px;
        left: 3px;
    }

    .custom-switch .custom-control-input:checked ~ .custom-control-label::after {
        transform: translateX(20px);
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
        box-shadow: 0 4px 12px rgba(0,123,255,0.3);
    }

    .btn-loading {
        display: none;
    }

    .btn-loading.d-none {
        display: none !important;
    }

    /* Toast styles */
    .toast-notification {
        min-width: 300px;
        margin-bottom: 10px;
    }

    .toast-notification-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
    }

    .toast-notification-warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
    }

    .toast-notification-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
    }

    .toast-notification-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .rating-star {
            font-size: 2rem;
        }

        .rating-star-sm {
            font-size: 1rem;
        }

        .rating-item {
            padding: 12px;
        }

        .btn-lg {
            padding: 10px 20px;
            font-size: 1rem;
        }
    }
    </style>
    @yield('styles')
</body>
</html>
