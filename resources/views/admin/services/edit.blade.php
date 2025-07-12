@extends('admin.layouts.admin-master')

@section('content')
<div class="container mt-4">
    <h2>Chỉnh sửa dịch vụ</h2>
    <form action="{{ route('admin.services.update', $service->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Tên dịch vụ</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $service->name) }}" required>
            @error('name')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="service_category_id" class="form-label">Danh mục</label>
            <select name="service_category_id" id="service_category_id" class="form-control" required>
                <option value="">-- Chọn danh mục --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ old('service_category_id', $service->service_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('service_category_id')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Giá</label>
            <input type="number" name="price" id="price" class="form-control" value="{{ old('price', $service->price) }}" min="0" required>
            @error('price')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <div class="mb-3">
            <label for="description" class="form-label">Mô tả</label>
            <textarea name="description" id="description" class="form-control">{{ old('description', $service->description) }}</textarea>
            @error('description')
                <div class="text-danger">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Cập nhật</button>
        <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection
