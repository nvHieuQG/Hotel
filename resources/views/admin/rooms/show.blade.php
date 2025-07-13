@extends('admin.layouts.admin-master')

@section('title', 'Chi tiết phòng')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Quản lý phòng</a></li>
            <li class="breadcrumb-item active">Chi tiết phòng</li>
        </ol>
        <div class="col-lg-12">
            <h3 class="fw-bold text-secondary">Chi tiết phòng: {{ $room->name }}</h3>
        </div>
    </div>

    <div class="row">
        {{-- Thông tin phòng --}}
        <div class="col-md-8">
            <div class="card shadow-sm rounded">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-white">Thông tin phòng</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2"><strong>Tên phòng:</strong> {{ $room->name }}</div>
                    <div class="mb-2"><strong>Giá:</strong> {{ number_format($room->price, 0, ',', '.') }} VND / đêm</div>
                    <div class="mb-2"><strong >Loại phòng:</strong> {{ $room->roomType->name }}</div>
                    <div class="mb-2"><strong>Sức chứa:</strong> {{ $room->capacity }} người</div>
                    <div class="mb-2"><strong>Mô tả:</strong> {!! nl2br(e($room->description)) !!}</div>
                    @php
                        $statusText = [
                            'available' => 'Trống',
                            'pending'   => 'Chờ xác nhận',
                            'booked'    => 'Đã đặt',
                            'repair'    => 'Bảo trì',
                        ];
                        $statusBgClass = [
                            'available' => 'bg-success bg-opacity-50',
                            'pending'   => 'bg-warning bg-opacity-50',
                            'booked'    => 'bg-danger bg-opacity-50',
                            'repair'    => 'bg-secondary bg-opacity-50',
                        ];
                    @endphp
                    <div class="mb-2">
                        <strong>Trạng thái:</strong>
                        <span class="badge {{ $statusBgClass[$room->status_for_display] ?? '' }}">
                            {{ $statusText[$room->status_for_display] ?? 'Không rõ' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Dịch vụ đi kèm --}}
            <div class="card mt-4 shadow-sm rounded">
                <div class="card-header bg-light">
                    <h5 class="mb-0 text-white">Dịch vụ đi kèm</h5>
                </div>
                <div class="card-body">
                    @if($room->services && $room->services->count())
                        <ul class="list-group list-group-flush">
                            @foreach($room->services as $service)
                                <li class="list-group-item">{{ $service->name }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted">Không có dịch vụ đi kèm.</p>
                    @endif
                </div>
            </div>

            {{-- Nút điều hướng --}}
            <div class="mt-4">
                <a href="{{ route('admin.rooms.index') }}" class="btn btn-outline-secondary me-2">
                    <i class="fa fa-arrow-left"></i> Quay lại
                </a>
                <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-outline-primary">
                    <i class="fa fa-edit"></i> Chỉnh sửa
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
