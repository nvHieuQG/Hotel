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

            <!-- Form lọc -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <form method="GET" action="{{ route('admin.services.index') }}" class="d-flex gap-2">
                        <select name="category_id" class="form-select">
                            <option value="">Tất cả danh mục</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $categoryId == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <select name="room_type_id" class="form-select">
                            <option value="">Tất cả loại phòng</option>
                            @foreach($roomTypes as $roomType)
                                <option value="{{ $roomType->id }}" {{ $roomTypeId == $roomType->id ? 'selected' : '' }}>
                                    {{ $roomType->name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Lọc
                        </button>
                        @if($categoryId || $roomTypeId)
                            <a href="{{ route('admin.services.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Xóa lọc
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th style="width: 60px;">#</th>
                            <th>Tên dịch vụ</th>
                            <th>Danh mục</th>
                            <th>Giá</th>
                            <th>Mô tả</th>
                            <th>Loại phòng</th>
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
                                <td>
                                    @if($service->roomTypes->count() > 0)
                                        @foreach($service->roomTypes as $roomType)
                                            <span class="badge bg-info me-1">{{ $roomType->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">Chưa gán</span>
                                    @endif
                                </td>
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
                                <td colspan="7" class="text-center text-muted">Không có dịch vụ nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
