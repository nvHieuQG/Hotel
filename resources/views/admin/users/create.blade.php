@extends('admin.layouts.admin-master')
@section('title', 'Thêm người dùng')
@section('header', 'Thêm người dùng mới')
@section('content')
<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Tạo người dùng</h6>
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Quay lại
        </a>
    </div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" class="row g-3">
            @csrf
            <div class="col-md-6">
                <label class="form-label">Họ tên <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Tên đăng nhập</label>
                <input type="text" name="username" class="form-control" value="{{ old('username') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Điện thoại</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
            </div>

            <div class="col-md-6">
                <label class="form-label">Vai trò <span class="text-danger">*</span></label>
                <select name="role_id" class="form-select" required>
                    <option value="">-- Chọn vai trò --</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ (string)old('role_id') === (string)$role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6"></div>

            <div class="col-md-6">
                <label class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation" class="form-control" required>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 mt-3">
                <a href="{{ route('admin.users.index') }}" class="btn btn-light">Hủy</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> Lưu
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
