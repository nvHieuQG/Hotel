@extends('admin.layouts.admin-master')

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <h5 class="mb-0">Dịch vụ</h5>
            <div>
                <a href="{{ route('admin.room-type-services.index') }}" class="btn btn-light btn-sm me-2">
                    <i class="fas fa-link"></i> Gán dịch vụ cho loại phòng
                </a>
                <a href="{{ route('admin.services.create') }}" class="btn btn-light btn-sm">
                    <i class="fas fa-plus"></i> Thêm dịch vụ
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 60px;">#</th>
                            <th>Tên dịch vụ</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Mô tả</th>
                            <th style="width: 160px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td class="text-center">{{ $service->id }}</td>
                                <td>{{ $service->name }}</td>
                                <td>{{ optional($service->category)->name }}</td>
                                <td>{{ number_format($service->price, 0, ',', '.') }} đ</td>
                                <td>{{ $service->description }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.services.edit', $service->id) }}" class="btn btn-warning btn-sm me-1">
                                        <i class="fas fa-edit"></i> Sửa
                                    </a>
                                    <form action="{{ route('admin.services.destroy', $service->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash-alt"></i> Xóa
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Không có dịch vụ nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
