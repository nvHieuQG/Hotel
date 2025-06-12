@extends('admin.layouts.admin-master')

@section('header', 'Thêm phòng mới')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Quản lý phòng</a></li>
        <li class="breadcrumb-item active">Thêm phòng mới</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-plus me-1"></i>
            Thêm phòng mới
        </div>
        <div class="card-body">
            <form action="{{route('admin.rooms.store')}}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="room_number" class="form-label">Số phòng</label>
                    <input type="text" name="room_number" id="room_number" class="form-control" value="{{ old('room_number') }}" required>
                    @error('room_number')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="room_type_id" class="form-label">Loại phòng</label>
                    <select name="room_type_id" id="room_type_id" class="form-select" required>
                        <option value="">-- Chọn loại phòng --</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}" {{ old('room_type_id') == $type->id ? 'selected' : '' }}>
                                {{ $type->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('room_type_id')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="price" class="form-label">Giá (VND)</label>
                    <input type="number" name="price" id="price" class="form-control" value="{{ old('price') }}" required>
                    @error('price')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="capacity" class="form-label">Sức chứa</label>
                    <input type="number" name="capacity" id="capacity" class="form-control" value="{{ old('capacity') }}" required>
                    @error('capacity')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="available" {{ old('status') == 'available' ? 'selected' : '' }}>Còn trống</option>
                        <option value="booked" {{ old('status') == 'booked' ? 'selected' : '' }}>Đã đặt</option>
                        <option value="repair" {{ old('status') == 'repair' ? 'selected' : '' }}>Bảo trì</option>
                    </select>
                    @error('status')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mt-4">
                    <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary">Thêm phòng</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
