@extends('admin.layouts.admin-master')

@section('content')
<div class="container-fluid">
    <!-- Bộ lọc -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.rooms.index') }}">
                <div class="row g-3">
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label for="floor" class="form-label">Tầng</label>
                        <select name="floor" id="floor" class="form-select">
                            <option value="">Tất cả</option>
                            @foreach($floors as $floor)
                                <option value="{{ $floor }}" {{ ($filters['floor'] ?? '') == $floor ? 'selected' : '' }}>
                                    Tầng {{ $floor }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label for="room_type" class="form-label">Loại phòng</label>
                        <select name="room_type" id="room_type" class="form-select">
                            <option value="">Tất cả</option>
                            @foreach($roomTypes as $type)
                                <option value="{{ $type->id }}" {{ ($filters['room_type'] ?? '') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label for="search" class="form-label">Tìm kiếm</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Số phòng" value="{{ $filters['search'] ?? '' }}">
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label for="status" class="form-label">Trạng thái</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="available" {{ ($filters['status'] ?? '') == 'available' ? 'selected' : '' }}>Trống</option>
                            <option value="pending" {{ ($filters['status'] ?? '') == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="booked" {{ ($filters['status'] ?? '') == 'booked' ? 'selected' : '' }}>Đã đặt</option>
                            <option value="repair" {{ ($filters['status'] ?? '') == 'repair' ? 'selected' : '' }}>Bảo trì</option>
                        </select>
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label for="date" class="form-label">Ngày kiểm tra</label>
                        <input type="date" name="date" id="date" class="form-control" value="{{ $filters['date'] ?? '' }}">
                    </div>
                    <div class="col-12 col-sm-6 col-md-4 col-lg-2">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-filter me-1"></i> Lọc
                            </button>
                            <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Sơ đồ phòng (Room Map/Grid) -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <h5 class="mb-0">Sơ đồ phòng ({{ $totalRooms }} phòng)</h5>
                <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Thêm phòng
                </a>
            </div>
        </div>
        <div class="card-body">
            @php
                $statusClass = [
                    'available' => 'border-success',
                    'pending'   => 'border-warning',
                    'booked'    => 'border-secondary',
                    'repair'    => 'border-danger',
                ];
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

            @if($paginatedFloors->isEmpty())
                <div class="text-center p-5">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5>Không tìm thấy phòng nào</h5>
                    <p class="text-muted">Không có phòng nào phù hợp với bộ lọc hiện tại.</p>
                </div>
            @else
                @foreach($paginatedFloors as $floor => $floorRooms)
                    <div class="mb-4">
                        <h5 class="border-bottom pb-2 mb-3">Tầng {{ $floor }}</h5>
                        
                        <!-- Desktop Grid View -->
                        <div class="d-none d-lg-block">
                            <div class="row g-3">
                                @foreach($floorRooms as $room)
                                    @php 
                                        $displayStatus = !empty($filters['date']) 
                                            ? $room->getStatusForDate($filters['date']) 
                                            : $room->status_for_display; 
                                    @endphp
                                    <div class="col-md-3 col-lg-2">
                                        <div class="card h-100 {{ $statusClass[$displayStatus] ?? 'border-secondary' }} {{ $statusBgClass[$displayStatus] ?? 'bg-secondary bg-opacity-50' }}">
                                            <div class="card-body p-2 text-center">
                                                <h6 class="card-title mb-1">{{ $room->room_number }}</h6>
                                                <small class="text-muted d-block mb-2">{{ $room->roomType->name }}</small>
                                                <span class="badge bg-{{ 
                                                    $displayStatus == 'available' ? 'success' : 
                                                    ($displayStatus == 'pending' ? 'warning' : 
                                                    ($displayStatus == 'booked' ? 'danger' : 'secondary')) }}">
                                                    {{ $statusText[$displayStatus] ?? 'Không xác định' }}
                                                </span>
                                                <div class="mt-2">
                                                    <a href="{{ route('admin.rooms.show', $room->id) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-sm btn-outline-warning">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Mobile/Tablet List View -->
                        <div class="d-lg-none">
                            @foreach($floorRooms as $room)
                                @php 
                                    $displayStatus = !empty($filters['date']) 
                                        ? $room->getStatusForDate($filters['date']) 
                                        : $room->status_for_display; 
                                @endphp
                                <div class="card mb-2 {{ $statusClass[$displayStatus] ?? 'border-secondary' }}">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="card-title mb-1">{{ $room->room_number }}</h6>
                                                <small class="text-muted">{{ $room->roomType->name }}</small>
                                                <div class="mt-2">
                                                    <span class="badge bg-{{ 
                                                        $displayStatus == 'available' ? 'success' : 
                                                        ($displayStatus == 'pending' ? 'warning' : 
                                                        ($displayStatus == 'booked' ? 'danger' : 'secondary')) }}">
                                                        {{ $statusText[$displayStatus] ?? 'Không xác định' }}
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="btn-group btn-group-sm" role="group">
                                                <a href="{{ route('admin.rooms.show', $room->id) }}" class="btn btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-outline-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <!-- Phân trang -->
                @if($paginatedFloors->hasPages())
                    <div class="d-flex justify-content-center mt-4">
                        {{ $paginatedFloors->appends(request()->query())->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection