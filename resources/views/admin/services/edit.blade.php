@extends('admin.layouts.admin-master')

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0">Chỉnh sửa dịch vụ</h5>
        </div>
        <div class="card-body">
    <form action="{{ route('admin.services.update', $service->id) }}" method="POST">
        @csrf
        @method('PUT')
                <div class="row">
                    <div class="col-md-6">
        <div class="mb-3">
                            <label for="name" class="form-label">Tên dịch vụ <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $service->name) }}" required>
            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
                    </div>
                    <div class="col-md-6">
        <div class="mb-3">
                            <label for="service_category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                            <select name="service_category_id" id="service_category_id" class="form-select @error('service_category_id') is-invalid @enderror" required>
                <option value="">-- Chọn danh mục --</option>
                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('service_category_id', $service->service_category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                @endforeach
            </select>
            @error('service_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
        <div class="mb-3">
                            <label for="price" class="form-label">Giá <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" name="price" id="price" class="form-control @error('price') is-invalid @enderror" value="{{ old('price', $service->price) }}" min="0" required>
                                <span class="input-group-text">VNĐ</span>
                            </div>
            @error('price')
                                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>
                    </div>
                </div>

        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="3">{{ old('description', $service->description) }}</textarea>
            @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Gán cho loại phòng</label>
                    <div class="row">
                        @foreach($roomTypes as $roomType)
                            <div class="col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="room_type_ids[]" 
                                           value="{{ $roomType->id }}" id="room_type_{{ $roomType->id }}"
                                           {{ in_array($roomType->id, old('room_type_ids', $assignedRoomTypeIds)) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="room_type_{{ $roomType->id }}">
                                        {{ $roomType->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <small class="text-muted">Chọn các loại phòng sẽ được cung cấp dịch vụ này.</small>
                    @error('room_type_ids')
                        <div class="text-danger small">{{ $message }}</div>
            @enderror
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Cập nhật dịch vụ
                    </button>
                    <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
