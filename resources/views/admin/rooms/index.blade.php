@extends('admin.layouts.admin-master')

@section('header', 'Quản lý phòng')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Quản lý phòng</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-bed me-1"></i>
                    Danh sách phòng
                </div>
                <div>
                    <a href="{{ route('admin.rooms.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus"></i> Thêm phòng mới
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <form action="{{ route('admin.rooms.index') }}" method="GET" class="row g-3">
                    <div class="col-auto">
                        <label for="status" class="col-form-label">Lọc theo trạng thái:</label>
                    </div>
                    <div class="col-auto">
                        <select name="status" id="status" class="form-select" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Còn trống</option>
                            <option value="booked" {{ request('status') == 'booked' ? 'selected' : '' }}>Đã đặt hoặc đang xử lý</option>
                            <option value="repair" {{ request('status') == 'repair' ? 'selected' : '' }}>Bảo trì</option>
                        </select>
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>STT</th>
                            <th>Ảnh</th>
                            <th>Số phòng</th>
                            <th>Loại phòng</th>
                            <th>Giá</th>
                            <th>Sức chứa</th>
                            <th>Trạng thái</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rooms as $room)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if($room->primaryImage)
                                    <img src="{{ $room->primaryImage->full_image_url }}" 
                                         alt="Room Image" 
                                         style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                @elseif($room->firstImage)
                                    <img src="{{ $room->firstImage->full_image_url }}" 
                                         alt="Room Image" 
                                         style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">
                                @else
                                    <div style="width: 60px; height: 40px; background-color: #f8f9fa; border-radius: 4px; display: flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $room->room_number }}</td>
                            <td>{{ $room->roomType->name ?? '' }}</td>
                            <td>{{ number_format($room->price) }} VND</td>
                            <td>{{ $room->capacity }}</td>
                            <td>
                                @php
                                    $hasActiveBooking = $room->bookings()->whereIn('status', ['pending', 'confirmed'])->count() > 0;
                                @endphp
                                <span class="badge bg-{{ 
                                    $hasActiveBooking ? 'warning' : 
                                    ($room->status == 'available' ? 'success' : 
                                    ($room->status == 'booked' ? 'primary' : 'warning')) 
                                }}">
                                    {{ 
                                        $hasActiveBooking ? 'Đang xử lý đặt phòng' :
                                        ($room->status == 'available' ? 'Còn trống' : 
                                        ($room->status == 'booked' ? 'Đã đặt' : 'Bảo trì')) 
                                    }}
                                </span>
                                
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.rooms.show', $room->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.rooms.edit', $room->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.rooms.destroy', $room->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa phòng này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $rooms->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Các mã JavaScript tùy chỉnh ở đây
    });
</script>
@endsection
