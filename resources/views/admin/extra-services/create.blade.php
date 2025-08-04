@extends('admin.layouts.admin-master')
@section('title', 'Thêm dịch vụ bổ sung')
@section('content')
<div class="container mt-4">
    <h2>Thêm dịch vụ bổ sung</h2>
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <form action="{{ route('admin.extra-services.store') }}" method="POST">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description">{{ old('description') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="applies_to" class="form-label">Áp dụng cho <span class="text-danger">*</span></label>
            <select class="form-select @error('applies_to') is-invalid @enderror" id="applies_to" name="applies_to" required>
                <option value="both" {{ old('applies_to') == 'both' ? 'selected' : '' }}>Người lớn & Trẻ em</option>
                <option value="adult" {{ old('applies_to') == 'adult' ? 'selected' : '' }}>Chỉ người lớn</option>
                <option value="child" {{ old('applies_to') == 'child' ? 'selected' : '' }}>Chỉ trẻ em</option>
            </select>
            @error('applies_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3" id="price_adult_field">
            <label for="price_adult" class="form-label">Giá người lớn</label>
            <input type="number" step="0.01" min="0" class="form-control @error('price_adult') is-invalid @enderror" id="price_adult" name="price_adult" value="{{ old('price_adult') }}">
            @error('price_adult')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3" id="price_child_field">
            <label for="price_child" class="form-label">Giá trẻ em</label>
            <input type="number" step="0.01" min="0" class="form-control @error('price_child') is-invalid @enderror" id="price_child" name="price_child" value="{{ old('price_child') }}">
            @error('price_child')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="charge_type" class="form-label">Kiểu tính phí <span class="text-danger">*</span></label>
            <select class="form-select @error('charge_type') is-invalid @enderror" id="charge_type" name="charge_type" required>
                <option value="per_person" {{ old('charge_type') == 'per_person' ? 'selected' : '' }}>Theo người</option>
                <option value="per_night" {{ old('charge_type') == 'per_night' ? 'selected' : '' }}>Theo đêm</option>
                <option value="per_service" {{ old('charge_type') == 'per_service' ? 'selected' : '' }}>Theo dịch vụ</option>
                <option value="per_hour" {{ old('charge_type') == 'per_hour' ? 'selected' : '' }}>Theo giờ</option>
                <option value="per_use" {{ old('charge_type') == 'per_use' ? 'selected' : '' }}>Theo lượt sử dụng</option>
            </select>
            @error('charge_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="child_age_min" class="form-label">Độ tuổi trẻ em (từ)</label>
            <input type="number" min="0" class="form-control @error('child_age_min') is-invalid @enderror" id="child_age_min" name="child_age_min" value="{{ old('child_age_min') }}">
            @error('child_age_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label for="child_age_max" class="form-label">Độ tuổi trẻ em (đến)</label>
            <input type="number" min="0" class="form-control @error('child_age_max') is-invalid @enderror" id="child_age_max" name="child_age_max" value="{{ old('child_age_max') }}">
            @error('child_age_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Trạng thái <span class="text-danger">*</span></label>
            <select class="form-select @error('is_active') is-invalid @enderror" name="is_active" required>
                <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Kích hoạt</option>
                <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Ẩn</option>
            </select>
            @error('is_active')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <button type="submit" class="btn btn-primary">Lưu</button>
        <a href="{{ route('admin.extra-services.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

{{-- Script để xử lý hiện/ẩn trường giá theo loại người áp dụng --}}
<script>
    function togglePriceFields() {
        var applies = document.getElementById('applies_to').value;
        document.getElementById('price_adult_field').style.display = (applies === 'adult' || applies === 'both') ? 'block' : 'none';
        document.getElementById('price_child_field').style.display = (applies === 'child' || applies === 'both') ? 'block' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        togglePriceFields();
        document.getElementById('applies_to').addEventListener('change', togglePriceFields);
    });
</script>
@endsection
