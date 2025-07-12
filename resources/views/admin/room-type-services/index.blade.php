@extends('admin.layouts.admin-master')

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded">
        <div class="card-header d-flex justify-content-between align-items-center bg-success text-white">
            <h5 class="mb-0">Gán dịch vụ cho loại phòng</h5>
            <a href="{{ route('admin.services.index') }}" class="btn btn-light btn-sm">
                <i class="fas fa-plus"></i> Thêm dịch vụ
            </a>
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
                            <th>Tên loại phòng</th>
                            <th style="width: 150px;">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roomTypes as $roomType)
                            <tr>
                                <td class="text-center">{{ $roomType->id }}</td>
                                <td>{{ $roomType->name }}</td>
                                <td class="text-center">
                                    <a href="{{ route('admin.room-type-services.edit', $roomType->id) }}"
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-link"></i> Gán dịch vụ
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">Không có loại phòng nào.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
