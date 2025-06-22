@extends('admin.layouts.admin-master')

@section('header', 'Cập nhật phòng')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="{{ route('admin.rooms.index') }}">Quản lý phòng</a></li>
        <li class="breadcrumb-item active">Cập nhật phòng</li>
    </ol>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-edit me-1"></i>
            Cập nhật phòng
        </div>
        <div class="card-body">
            <form action="{{ route('admin.rooms.update', $room->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT') <!-- Sử dụng phương thức PUT cho cập nhật -->

                <div class="mb-3">
                    <label for="room_number" class="form-label">Số phòng</label>
                    <input type="text" name="room_number" id="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}" required>
                    @error('room_number')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="room_type_id" class="form-label">Loại phòng</label>
                    <select name="room_type_id" id="room_type_id" class="form-select" required>
                        <option value="">-- Chọn loại phòng --</option>
                        @foreach($roomTypes as $type)
                            <option value="{{ $type->id }}" {{ old('room_type_id', $room->room_type_id) == $type->id ? 'selected' : '' }}>
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
                    <input type="number" name="price" id="price" class="form-control" value="{{ old('price', $room->price) }}" required>
                    @error('price')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>

               

                <div class="mb-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select name="status" id="status" class="form-select" required>
                        <option value="available" {{ old('status', $room->status) == 'available' ? 'selected' : '' }}>Còn trống</option>
                        <option value="booked" {{ old('status', $room->status) == 'booked' ? 'selected' : '' }}>Đã đặt</option>
                        <option value="repair" {{ old('status', $room->status) == 'repair' ? 'selected' : '' }}>Bảo trì</option>
                    </select>
                    @error('status')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="images" class="form-label">Thêm hình ảnh mới</label>
                    <input type="file" name="images[]" id="images" class="form-control" multiple accept="image/*">
                    <div class="form-text">Có thể chọn nhiều ảnh. Ảnh đầu tiên sẽ là ảnh chính.</div>
                    @error('images.*')
                        <div class="text-danger small">{{ $message }}</div>
                    @enderror
                </div>
                
                @if($room->images && $room->images->count() > 0)
                <div class="mb-3">
                    <label class="form-label">Hình ảnh hiện tại</label>
                    <div class="row">
                        @foreach($room->images as $image)
                        <div class="col-md-3 mb-2">
                            <div class="card">
                                <img src="{{ $image->full_image_url }}" class="card-img-top" alt="Room Image" style="height: 150px; object-fit: cover;">
                                <div class="card-body p-2">
                                    
                                    <div class="d-flex justify-content-between">
                                        @if($image->is_primary)
                                            <span class="badge bg-success">Ảnh chính</span>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-primary set-primary" 
                                                    data-room-id="{{ $room->id }}" data-image-id="{{ $image->id }}">
                                                Đặt làm ảnh chính
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger delete-image" 
                                                data-room-id="{{ $room->id }}" data-image-id="{{ $image->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <div class="mt-4">
                    <a href="{{ route('admin.rooms.index') }}" class="btn btn-secondary">Quay lại</a>
                    <button type="submit" class="btn btn-primary">Cập nhật phòng</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')
<script>
$(document).ready(function() {
    console.log('Script loaded');
    
    // Xóa ảnh
    $('.delete-image').click(function() {
        console.log('Delete image clicked');
        if (confirm('Bạn có chắc chắn muốn xóa ảnh này?')) {
            const roomId = $(this).data('room-id');
            const imageId = $(this).data('image-id');
            
            console.log('Room ID:', roomId, 'Image ID:', imageId);
            
            $.ajax({
                url: `/admin/rooms/${roomId}/images/${imageId}`,
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    console.log('Success:', response);
                    if (response.success) {
                        location.reload();
                    } else {
                        alert(response.message || 'Có lỗi xảy ra khi xóa ảnh');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', xhr.responseText);
                    alert('Có lỗi xảy ra khi xóa ảnh. Vui lòng thử lại.');
                }
            });
        }
    });
    
    // Đặt ảnh chính
    $('.set-primary').click(function() {
        console.log('Set primary clicked');
        const roomId = $(this).data('room-id');
        const imageId = $(this).data('image-id');
        
        console.log('Room ID:', roomId, 'Image ID:', imageId);
        
        $.ajax({
            url: `/admin/rooms/${roomId}/images/${imageId}/primary`,
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                console.log('Success:', response);
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message || 'Có lỗi xảy ra khi đặt ảnh chính');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error:', xhr.responseText);
                alert('Có lỗi xảy ra khi đặt ảnh chính. Vui lòng thử lại.');
            }
        });
    });
});
</script>
@endsection