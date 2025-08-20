@extends('admin.layouts.admin-master')

@section('title', 'Thêm Khuyến Mại Mới')

@section('header', 'Thêm khuyến mại mới')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.promotions.index') }}">Quản lý khuyến mại</a></li>
        <li class="breadcrumb-item active">Thêm mới</li>
    </ol>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Thông tin khuyến mại</h6>
                    <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-list"></i> Danh sách
                    </a>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.promotions.store') }}" enctype="multipart/form-data" id="promotion-form">
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="title" class="form-label">Tiêu đề <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                       id="title" name="title" value="{{ old('title') }}" required>
                                @error('title')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="code" class="form-label">Mã khuyến mại <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('code') is-invalid @enderror" 
                                       id="code" name="code" value="{{ old('code') }}" placeholder="VD: SUMMER2024" required>
                                @error('code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Mô tả <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                  id="description" name="description" rows="3" required>{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="discount_type" class="form-label">Loại giảm giá <span class="text-danger">*</span></label>
                                <select class="form-control @error('discount_type') is-invalid @enderror" 
                                        id="discount_type" name="discount_type" required>
                                    <option value="">Chọn loại giảm giá</option>
                                    <option value="percentage" {{ old('discount_type') == 'percentage' ? 'selected' : '' }}>Phần trăm (%)</option>
                                    <option value="fixed" {{ old('discount_type') == 'fixed' ? 'selected' : '' }}>Số tiền cố định (VNĐ)</option>
                                </select>
                                @error('discount_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="discount_value" class="form-label">Giá trị giảm <span class="text-danger">*</span></label>
                                <input type="number" class="form-control @error('discount_value') is-invalid @enderror" 
                                       id="discount_value" name="discount_value" value="{{ old('discount_value') }}" 
                                       step="0.01" min="0" required>
                                <small class="form-text text-muted" id="discount_help">
                                    Nhập giá trị từ 0-100 cho phần trăm, hoặc số tiền cho giảm cố định
                                </small>
                                @error('discount_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="minimum_amount" class="form-label">Đơn hàng tối thiểu (VNĐ)</label>
                                <input type="number" class="form-control @error('minimum_amount') is-invalid @enderror" 
                                       id="minimum_amount" name="minimum_amount" value="{{ old('minimum_amount') }}" 
                                       step="1000" min="0">
                                <small class="form-text text-muted">Để trống nếu không yêu cầu giá trị tối thiểu</small>
                                @error('minimum_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="usage_limit" class="form-label">Giới hạn số lần sử dụng</label>
                                <input type="number" class="form-control @error('usage_limit') is-invalid @enderror" 
                                       id="usage_limit" name="usage_limit" value="{{ old('usage_limit') }}" 
                                       min="1">
                                <small class="form-text text-muted">Để trống nếu không giới hạn</small>
                                @error('usage_limit')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="valid_from" class="form-label">Ngày bắt đầu</label>
                                <input type="date" class="form-control @error('valid_from') is-invalid @enderror" 
                                       id="valid_from" name="valid_from" value="{{ old('valid_from') }}">
                                <small class="form-text text-muted">Để trống nếu có hiệu lực ngay</small>
                                @error('valid_from')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="expired_at" class="form-label">Ngày hết hạn <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('expired_at') is-invalid @enderror" 
                                       id="expired_at" name="expired_at" value="{{ old('expired_at') }}" 
                                       required>
                                @error('expired_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="image" class="form-label">Hình ảnh</label>
                                <input type="file" class="form-control @error('image') is-invalid @enderror" 
                                       id="image" name="image" accept="image/*">
                                <small class="form-text text-muted">Tối đa 2MB, định dạng: JPG, PNG, GIF</small>
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="terms_conditions" class="form-label">Điều khoản và điều kiện</label>
                        <textarea class="form-control @error('terms_conditions') is-invalid @enderror" 
                                  id="terms_conditions" name="terms_conditions" rows="4">{{ old('terms_conditions') }}</textarea>
                        @error('terms_conditions')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Phạm vi áp dụng -->
                    <div class="mb-4">
                        <label class="form-label">
                            <i class="fas fa-bullseye text-primary"></i> 
                            Phạm vi áp dụng khuyến mại <span class="text-danger">*</span>
                        </label>
                        <small class="form-text text-muted d-block mb-3">Chọn một trong các tùy chọn dưới đây:</small>

                        @php
                            $currentScope = old('apply_scope', 'all');
                            if (!in_array($currentScope, ['all', 'room_types'])) {
                                $currentScope = 'all';
                            }
                        @endphp

                        <div class="card">
                            <div class="card-body">
                                <!-- Navigation Tabs -->
                                <ul class="nav nav-tabs" id="applyScopeTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $currentScope === 'all' ? 'active' : '' }}" 
                                                id="all-rooms-tab" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#all-rooms" 
                                                type="button" 
                                                role="tab"
                                                data-scope="all">
                                            <i class="fas fa-globe"></i> Tất cả phòng
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $currentScope === 'room_types' ? 'active' : '' }}" 
                                                id="room-types-tab" 
                                                data-bs-toggle="tab" 
                                                data-bs-target="#room-types" 
                                                type="button" 
                                                role="tab"
                                                data-scope="room_types">
                                            <i class="fas fa-layer-group"></i> Theo loại phòng
                                        </button>
                                    </li>
                                </ul>

                                <!-- Hidden input for form submission -->
                                <input type="hidden" name="apply_scope" id="apply_scope_input" value="{{ $currentScope }}">

                                <!-- Tab Content -->
                                <div class="tab-content mt-3" id="applyScopeTabContent">

                                    <!-- Tab: Tất cả phòng -->
                                    <div class="tab-pane fade {{ $currentScope === 'all' ? 'show active' : '' }}" id="all-rooms" role="tabpanel">
                                        <div class="alert alert-success">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-globe fa-2x text-success me-3"></i>
                                                <div>
                                                    <h6 class="mb-1"><strong>Áp dụng cho tất cả loại phòng</strong></h6>
                                                    <p class="mb-0">Khuyến mại này sẽ hiển thị trên tất cả các loại phòng trong khách sạn.</p>
                                                    <small class="text-muted">✓ Không cần chọn loại phòng cụ thể</small>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title text-primary">
                                                    <i class="fas fa-chart-bar"></i> Thống kê áp dụng
                                                </h6>
                                                @php
                                                    $totalRoomTypes = \App\Models\RoomType::count();
                                                    $totalRooms = \App\Models\Room::count();
                                                @endphp
                                                <div class="row text-center">
                                                    <div class="col-6">
                                                        <div class="border-end">
                                                            <h4 class="text-primary mb-0">{{ $totalRoomTypes }}</h4>
                                                            <small class="text-muted">Loại phòng</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <h4 class="text-success mb-0">{{ $totalRooms }}</h4>
                                                        <small class="text-muted">Tổng số phòng</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tab: Theo loại phòng -->
                                    <div class="tab-pane fade {{ $currentScope === 'room_types' ? 'show active' : '' }}" id="room-types" role="tabpanel">
                                        <div class="mb-3">
                                            <h6 class="mb-0">
                                                <i class="fas fa-layer-group text-primary"></i> 
                                                Chọn loại phòng áp dụng khuyến mại
                                            </h6>
                                            <small class="text-muted">Chọn một hoặc nhiều loại phòng để áp dụng khuyến mại</small>
                                        </div>
                                        
                                        <!-- Quick actions -->
                                        <div class="mb-3">
                                            <button type="button" class="btn btn-outline-primary btn-sm me-2" id="selectAllRoomTypes">
                                                <i class="fas fa-check-double"></i> Chọn tất cả
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="clearAllRoomTypes">
                                                <i class="fas fa-times"></i> Bỏ chọn tất cả
                                            </button>
                                        </div>
                                        
                                        <div class="row g-3">
                                            @foreach(\App\Models\RoomType::withCount('rooms')->get() as $roomType)
                                                <div class="col-sm-6 col-md-4">
                                                    <div class="form-check border rounded-3 p-3 text-center room-type-card h-100" 
                                                         data-room-type-id="{{ $roomType->id }}">
                                                        <input type="checkbox" 
                                                               class="form-check-input room-type-checkbox" 
                                                               name="room_type_ids[]" 
                                                               value="{{ $roomType->id }}"
                                                               id="room_type_{{ $roomType->id }}"
                                                               {{ in_array($roomType->id, old('room_type_ids', [])) ? 'checked' : '' }}>
                                                        <label for="room_type_{{ $roomType->id }}" class="form-check-label w-100 cursor-pointer">
                                                            <div class="mb-2">
                                                                <i class="fas fa-bed fa-lg text-success"></i>
                                                            </div>
                                                            <div class="fw-bold text-primary mb-1">{{ $roomType->name }}</div>
                                                            <div class="small text-muted mb-2">{{ Str::limit($roomType->description, 50) }}</div>
                                                            <div class="d-flex justify-content-between align-items-center">
                                                                <span class="badge bg-info">{{ $roomType->rooms_count }} phòng</span>
                                                                <span class="badge bg-warning">{{ number_format($roomType->price) }}đ/đêm</span>
                                                            </div>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        
                                        <!-- Summary -->
                                        <div class="mt-3">
                                            <div class="card bg-primary text-white">
                                                <div class="card-body">
                                                    <h6 class="card-title text-white">
                                                        <i class="fas fa-chart-line"></i> Thống kê lựa chọn
                                                    </h6>
                                                    <div class="row text-center">
                                                        <div class="col-6">
                                                            <div class="border-end border-light">
                                                                <h4 class="text-white mb-0" id="selectedCount">0</h4>
                                                                <small class="text-light">Loại phòng đã chọn</small>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <h4 class="text-white mb-0" id="totalRooms">0</h4>
                                                            <small class="text-light">Tổng số phòng áp dụng</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Checkboxes -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" 
                                       {{ old('is_active', '1') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    <i class="fas fa-power-off"></i> Kích hoạt ngay
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_featured" name="is_featured" value="1" 
                                       {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">
                                    <i class="fas fa-star"></i> Đặt nổi bật
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="can_combine" name="can_combine" value="1" 
                                       {{ old('can_combine') ? 'checked' : '' }}>
                                <label class="form-check-label" for="can_combine">
                                    <i class="fas fa-layer-group"></i> Có thể dùng gộp
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Submit buttons -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Tạo khuyến mại
                        </button>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Hướng dẫn</h6>
            </div>
            <div class="card-body">
                <h6><i class="fas fa-info-circle text-info"></i> Thông tin cần biết:</h6>
                <ul class="list-unstyled">
                    <li><strong>Mã khuyến mại:</strong> Chỉ chứa chữ cái, số, dấu gạch ngang và gạch dưới</li>
                    <li><strong>Giảm phần trăm:</strong> Giá trị từ 0-100</li>
                    <li><strong>Giảm cố định:</strong> Số tiền tính bằng VNĐ</li>
                    <li><strong>Đơn tối thiểu:</strong> Giá trị đơn hàng tối thiểu để áp dụng</li>
                </ul>

                <h6 class="mt-4"><i class="fas fa-lightbulb text-warning"></i> Gợi ý:</h6>
                <ul class="list-unstyled">
                    <li>• Tạo mã ngắn gọn, dễ nhớ</li>
                    <li>• Đặt hạn sử dụng hợp lý</li>
                    <li>• Thêm hình ảnh để thu hút</li>
                    <li>• Viết điều khoản rõ ràng</li>
                </ul>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header">
                <h6 class="mb-0">Xem trước</h6>
            </div>
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-primary text-white d-inline-flex align-items-center justify-content-center" 
                         id="preview" style="width: 80px; height: 80px; border-radius: 50%;">
                        <i class="fas fa-percentage fa-2x"></i>
                    </div>
                </div>
                <h6 class="text-center" id="preview-title">Tiêu đề khuyến mại</h6>
                <p class="text-center text-muted small" id="preview-description">Mô tả khuyến mại sẽ hiển thị ở đây</p>
                <div class="text-center">
                    <span class="badge bg-success text-white" id="preview-discount">0%</span>
                </div>
                <div class="mt-2 text-center">
                    <small class="text-muted">Mã: <strong id="preview-code">CODE</strong></small>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<style>
.room-type-card {
    transition: all 0.3s ease;
    border: 2px solid #e9ecef;
    cursor: pointer;
    position: relative;
    overflow: hidden;
}

.room-type-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.room-type-card .form-check-input:checked + .form-check-label {
    color: #007bff;
}

.room-type-card .form-check-input:checked ~ .room-type-card {
    border-color: #007bff;
    background-color: #f8f9ff;
}

.room-type-card .form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

.room-type-card .form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.cursor-pointer {
    cursor: pointer;
}

.badge {
    font-size: 0.75rem;
    padding: 0.375rem 0.75rem;
    border-radius: 6px;
}

.badge.bg-info {
    background-color: #17a2b8 !important;
}

.badge.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.badge.bg-success {
    background-color: #28a745 !important;
}

.badge.bg-secondary {
    background-color: #6c757d !important;
}

/* Quick action buttons */
.btn-outline-primary:hover {
    background-color: #007bff;
    border-color: #007bff;
    color: white;
}

.btn-outline-secondary:hover {
    background-color: #6c757d;
    border-color: #6c757d;
    color: white;
}

/* Tab improvements */
.nav-tabs .nav-link {
    border: none;
    border-bottom: 3px solid transparent;
    color: #6c757d;
    font-weight: 500;
    padding: 0.75rem 1rem;
    transition: all 0.3s ease;
}

.nav-tabs .nav-link:hover {
    border-color: transparent;
    color: #007bff;
    background-color: #f8f9fa;
}

.nav-tabs .nav-link.active {
    color: #007bff;
    border-bottom-color: #007bff;
    background-color: transparent;
}

/* Card improvements */
.card {
    border: none;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.12);
}

.card-header {
    border-bottom: none;
    padding: 1rem 1.25rem;
    background-color: #f8f9fa;
}

/* Form improvements */
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.form-check-input:checked {
    background-color: #007bff;
    border-color: #007bff;
}

.form-check-input:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
</style>

<script>
$(document).ready(function() {
    // Function to update apply_scope based on current selections
    function updateApplyScope() {
        const checkedRoomTypes = $('.room-type-checkbox:checked').length;
        
        if (checkedRoomTypes > 0) {
            $('#apply_scope_input').val('room_types');
        } else {
            $('#apply_scope_input').val('all');
        }
    }

    // Tab switching logic
    $('#applyScopeTabs button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        const scope = $(e.target).data('scope');
        
        if (scope === 'all') {
            // Clear all checkboxes when switching to "all rooms"
            $('.room-type-checkbox').prop('checked', false);
            updateApplyScope();
        }
    });

    // Individual checkbox change events
    function setupCheckboxEvents() {
        $('.room-type-checkbox').on('change', function() {
            updateApplyScope();
            updateRoomTypeSummary();
        });
    }

    // Quick actions for room type selection
    function setupQuickActions() {
        $('#selectAllRoomTypes').on('click', function() {
            $('.room-type-checkbox').prop('checked', true);
            updateApplyScope();
            updateRoomTypeSummary();
        });
        
        $('#clearAllRoomTypes').on('click', function() {
            $('.room-type-checkbox').prop('checked', false);
            updateApplyScope();
            updateRoomTypeSummary();
        });
    }

    // Update room type selection summary
    function updateRoomTypeSummary() {
        const checkedCount = $('.room-type-checkbox:checked').length;
        const totalCount = $('.room-type-checkbox').length;
        
        let totalRooms = 0;
        $('.room-type-checkbox:checked').each(function() {
            const roomTypeId = $(this).val();
            const roomTypeCard = $(`.room-type-card[data-room-type-id="${roomTypeId}"]`);
            const roomCountText = roomTypeCard.find('.badge.bg-info').text();
            const roomCount = parseInt(roomCountText.match(/\d+/)[0]);
            totalRooms += roomCount;
        });
        
        $('#selectedCount').text(checkedCount);
        $('#totalRooms').text(totalRooms);
        
        // Update button states
        if (checkedCount === 0) {
            $('#selectAllRoomTypes').removeClass('btn-outline-primary').addClass('btn-outline-secondary');
        } else if (checkedCount === totalCount) {
            $('#selectAllRoomTypes').removeClass('btn-outline-secondary').addClass('btn-outline-primary');
        } else {
            $('#selectAllRoomTypes').removeClass('btn-outline-secondary').addClass('btn-outline-primary');
        }
    }

    // Form validation
    function setupFormValidation() {
        $('#promotion-form').on('submit', function(e) {
            const scope = $('#apply_scope_input').val();
            
            if (scope === 'room_types') {
                const checkedRoomTypes = $('.room-type-checkbox:checked').length;
                if (checkedRoomTypes === 0) {
                    e.preventDefault();
                    alert('Vui lòng chọn ít nhất một loại phòng hoặc chuyển sang tab "Tất cả phòng"');
                    return false;
                }
            }
            
            return true;
        });
    }

    // Auto-generate code from title
    $('#title').on('input', function() {
        const title = $(this).val();
        const code = title.toUpperCase()
                          .replace(/[^A-Z0-9\s]/g, '')
                          .replace(/\s+/g, '')
                          .substring(0, 12);
        if (code && !$('#code').val()) {
            $('#code').val(code);
        }
        updatePreview();
    });

    // Update discount help text based on type
    $('#discount_type').change(function() {
        const type = $(this).val();
        const $discountValue = $('#discount_value');
        const $helpText = $('#discount_help');
        
        if (type === 'percentage') {
            $discountValue.attr({
                'max': 80,
                'step': 0.01
            });
            $helpText.text('Nhập giá trị từ 0-80 cho phần trăm (tối đa 80%)');
        } else if (type === 'fixed') {
            $discountValue.attr({
                'max': null,
                'step': 1000
            });
            $helpText.text('Nhập số tiền giảm (VNĐ)');
        }
        updatePreview();
    });

    // Validate discount value
    $('#discount_value').on('input', function() {
        const type = $('#discount_type').val();
        const value = parseFloat($(this).val());
        
        if (type === 'percentage' && value > 80) {
            $(this).val(80);
            alert('Giảm giá theo phần trăm không được vượt quá 80%');
        }
    });

    // Preview functionality
    function updatePreview() {
        const title = $('#title').val() || 'Tiêu đề khuyến mại';
        const description = $('#description').val() || 'Mô tả khuyến mại sẽ hiển thị ở đây';
        const code = $('#code').val() || 'CODE';
        const discountType = $('#discount_type').val();
        const discountValue = $('#discount_value').val() || '0';
        
        let discountText = '0%';
        if (discountType === 'percentage') {
            discountText = discountValue + '%';
        } else if (discountType === 'fixed') {
            discountText = new Intl.NumberFormat('vi-VN').format(discountValue) + 'đ';
        }
        
        $('#preview-title').text(title);
        $('#preview-description').text(description.substring(0, 60) + (description.length > 60 ? '...' : ''));
        $('#preview-code').text(code);
        $('#preview-discount').text(discountText);
    }

    // Image preview
    $('#image').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview').html(`<img src="${e.target.result}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover;">`);
            };
            reader.readAsDataURL(file);
        }
    });

    // Bind preview updates to form changes
    $('#title, #description, #code, #discount_value').on('input', updatePreview);
    $('#discount_type').on('change', updatePreview);

    // Initialize on page load
    function initializeForm() {
        setupCheckboxEvents();
        setupQuickActions(); // Initialize quick actions
        setupFormValidation();
        
        // Set initial apply_scope based on current selections
        updateApplyScope();
        updateRoomTypeSummary(); // Initialize summary on load
        
        // Initial preview update
        updatePreview();
    }

    // Initialize
    initializeForm();
});
</script>
@endpush
@endsection 