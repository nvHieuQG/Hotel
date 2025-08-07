@extends('admin.layouts.admin-master')

@section('title', 'Quản lý yêu cầu đổi phòng')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Quản lý yêu cầu đổi phòng</h3>
                </div>
                <div class="card-body">
                    <!-- Filter -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('admin.room-changes.index') }}" class="form-inline">
                                <div class="form-group mr-2">
                                    <label for="status" class="mr-2">Trạng thái:</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="">Tất cả</option>
                                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                                    </select>
                                </div>
                                <div class="form-group mr-2">
                                    <label for="date_from" class="mr-2">Từ ngày:</label>
                                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                                </div>
                                <div class="form-group mr-2">
                                    <label for="date_to" class="mr-2">Đến ngày:</label>
                                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                                </div>
                                <button type="submit" class="btn btn-primary mr-2">Lọc</button>
                                <a href="{{ route('admin.room-changes.index') }}" class="btn btn-secondary">Xóa bộ lọc</a>
                            </form>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="info-box bg-warning">
                                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Chờ duyệt</span>
                                    <span class="info-box-number">{{ $roomChanges->where('status', 'pending')->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fa fa-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Đã duyệt</span>
                                    <span class="info-box-number">{{ $roomChanges->where('status', 'approved')->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-danger">
                                <span class="info-box-icon"><i class="fa fa-times"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Từ chối</span>
                                    <span class="info-box-number">{{ $roomChanges->where('status', 'rejected')->count() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fa fa-check-circle"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Hoàn thành</span>
                                    <span class="info-box-number">{{ $roomChanges->where('status', 'completed')->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Booking</th>
                                    <th>Khách hàng</th>
                                    <th>Phòng cũ</th>
                                    <th>Phòng mới</th>
                                    <th>Lý do</th>
                                    <th>Chênh lệch giá</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày yêu cầu</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roomChanges as $roomChange)
                                    <tr>
                                        <td>{{ $roomChange->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.bookings.show', $roomChange->booking->id) }}" target="_blank">
                                                #{{ $roomChange->booking->booking_id }}
                                            </a>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.users.show', $roomChange->booking->user->id) }}" target="_blank">
                                                {{ $roomChange->booking->user->name }}
                                            </a>
                                        </td>
                                        <td>
                                            <strong>{{ $roomChange->oldRoom->room_number }}</strong><br>
                                            <small class="text-muted">{{ $roomChange->oldRoom->roomType->name }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $roomChange->newRoom->room_number }}</strong><br>
                                            <small class="text-muted">{{ $roomChange->newRoom->roomType->name }}</small>
                                        </td>
                                        <td>{{ Str::limit($roomChange->reason, 50) ?: 'Không có' }}</td>
                                        <td>
                                            @if($roomChange->price_difference > 0)
                                                <span class="text-danger">+{{ number_format($roomChange->price_difference, 0, ',', '.') }} VNĐ</span>
                                            @elseif($roomChange->price_difference < 0)
                                                <span class="text-success">{{ number_format($roomChange->price_difference, 0, ',', '.') }} VNĐ</span>
                                            @else
                                                <span class="text-muted">Không có chênh lệch</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $roomChange->getStatusColor() }} fw-bold text-dark">
                                                {{ $roomChange->getStatusText() }}
                                            </span>
                                        </td>
                                        <td>{{ $roomChange->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group">
                                                <a href="{{ route('admin.room-changes.show', $roomChange->id) }}" 
                                                   class="btn btn-sm btn-info" title="Xem chi tiết">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                
                                                @if($roomChange->status === 'pending')
                                                    <form method="POST" action="{{ route('admin.room-changes.approve', $roomChange->id) }}" style="display:inline-block;">
                                                        @csrf
                                                        <input type="text" name="admin_note" class="form-control form-control-sm d-inline-block" style="width:120px;" placeholder="Ghi chú (tùy chọn)">
                                                        <button type="submit" class="btn btn-sm btn-success" title="Duyệt">
                                                            <i class="fa fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.room-changes.reject', $roomChange->id) }}" style="display:inline-block;">
                                                        @csrf
                                                        <input type="text" name="admin_note" class="form-control form-control-sm d-inline-block" style="width:120px;" placeholder="Lý do từ chối" required>
                                                        <button type="submit" class="btn btn-sm btn-danger" title="Từ chối">
                                                            <i class="fa fa-times"></i>
                                                        </button>
                                                    </form>
                                                @elseif($roomChange->status === 'approved')
                                                    <button type="button" class="btn btn-sm btn-primary" 
                                                            onclick="completeRoomChange({{ $roomChange->id }})" title="Hoàn thành">
                                                        <i class="fa fa-check-circle"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center py-4">
                                            <i class="fa fa-info-circle fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">Không có yêu cầu đổi phòng nào.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<

@push('scripts')
<script>
function completeRoomChange(id) {
    if (confirm('Bạn có chắc chắn muốn hoàn thành đổi phòng này?')) {
        $.ajax({
            url: `/admin/room-changes/${id}/complete`,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Lỗi: ' + (response.message || 'Không thể hoàn thành đổi phòng'));
                }
            },
            error: function(xhr) {
                let message = 'Có lỗi xảy ra!';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                alert('Lỗi: ' + message);
            }
        });
    }
}
</script>
@endpush
@endsection 