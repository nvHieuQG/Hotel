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
                                    <th style="min-width: 200px;">Thao tác</th>
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
                                            <div class="action-column">
                                                <!-- Dòng 1: Nút xem chi tiết -->
                                                <div class="action-row-item">
                                                 <a href="{{ route('admin.room-changes.show', $roomChange->id) }}" 
                                                    class="btn btn-info  btn-action" title="Xem chi tiết">
                                                     <i class="fa fa-eye"></i>
                                                 </a>
                                                </div>
                                                
                                                @if($roomChange->status === 'pending')
                                                    <!-- Dòng 2: Duyệt kèm ghi chú -->
                                                    <div class="action-row-item">
                                                        <form method="POST" action="{{ route('admin.room-changes.approve', $roomChange->id) }}">
                                                            @csrf
                                                            <div class="input-group input-group-sm">
                                                                <div class="input-group-append ">
                                                                    <button type="submit" class="btn btn-success  btn-action mr-2" title="Duyệt">
                                                                        <i class="fa fa-check"></i>
                                                                    </button>
                                                                </div>
                                                                <input type="text" name="admin_note" class="form-control input-note" placeholder="Ghi chú (tùy chọn)">
                                                                
                                                            </div>
                                                        </form>
                                                    </div>
                                                    
                                                    <!-- Dòng 3: Từ chối kèm lý do -->
                                                    <div class="action-row-item">
                                                        <form method="POST" action="{{ route('admin.room-changes.reject', $roomChange->id) }}">
                                                            @csrf
                                                            <div class="input-group input-group-sm">
                                                                <div class="input-group-append ">
                                                                    <button type="submit" class="btn btn-danger  btn-action mr-2" title="Từ chối">
                                                                        <i class="fa fa-times"></i>
                                                                    </button>
                                                                </div>
                                                                <input type="text" name="admin_note" class="form-control input-note" placeholder="Lý do từ chối" required>
                                                                
                                                            </div>
                                                        </form>
                                                    </div>
                                                @elseif($roomChange->status === 'approved')
                                                    <div class="action-row-item">
                                                     <button type="button" class="btn btn-primary  btn-action" 
                                                             onclick="completeRoomChange({{ $roomChange->id }})" title="Hoàn thành">
                                                         <i class="fa fa-check-circle"></i>
                                                     </button>
                                                    </div>
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
</div>

@push('styles')
<style>
  /* Thống nhất nút thao tác */
  .btn-action {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
  }

  .input-group .btn-action {
  margin-right: 10px; /* hoặc 0.5rem */
}

  .action-column {
     display: flex;
     flex-direction: column;
     gap: 6px;
     min-width: 300px;
   }
  .action-row-item {
    display: flex;
    align-items: center;
    gap: 6px;
  }
  .action-row-item form { margin: 0; }
   .input-note { width: 180px; }
  
  /* Đảm bảo table responsive không làm cột thao tác quá nhỏ */
  .table-responsive {
    overflow-x: auto;
  }
  
  .table th:last-child,
  .table td:last-child {
    min-width: 200px;
    white-space: nowrap;
  }
</style>
@endpush

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