@extends('admin.layouts.admin-master')

@section('title', 'Chi tiết yêu cầu đổi phòng')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Chi tiết yêu cầu đổi phòng #{{ $roomChange->id }}</h3>
                    <a href="{{ route('admin.room-changes.index') }}" class="btn btn-secondary">
                        <i class="fa fa-arrow-left"></i> Quay lại
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Thông tin cơ bản -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thông tin cơ bản</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>ID yêu cầu:</strong></td>
                                            <td>#{{ $roomChange->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Booking:</strong></td>
                                            <td>
                                                <a href="{{ route('admin.bookings.show', $roomChange->booking->id) }}" target="_blank">
                                                    #{{ $roomChange->booking->booking_id }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Khách hàng:</strong></td>
                                            <td>
                                                <a href="{{ route('admin.users.show', $roomChange->booking->user->id) }}" target="_blank">
                                                    {{ $roomChange->booking->user->name }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày yêu cầu:</strong></td>
                                            <td>{{ $roomChange->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $roomChange->getStatusColor() }}">
                                                    {{ $roomChange->getStatusText() }}
                                                </span>
                                            </td>
                                        </tr>
                                        @if($roomChange->approved_at)
                                        <tr>
                                            <td><strong>Ngày duyệt:</strong></td>
                                            <td>{{ $roomChange->approved_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        @endif
                                        @if($roomChange->completed_at)
                                        <tr>
                                            <td><strong>Ngày hoàn thành:</strong></td>
                                            <td>{{ $roomChange->completed_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Thông tin phòng -->
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thông tin phòng</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Phòng cũ:</h6>
                                            <p><strong>{{ $roomChange->oldRoom->room_number }}</strong></p>
                                            <p><small class="text-muted">{{ $roomChange->oldRoom->roomType->name }}</small></p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Phòng mới:</h6>
                                            <p><strong>{{ $roomChange->newRoom->room_number }}</strong></p>
                                            <p><small class="text-muted">{{ $roomChange->newRoom->roomType->name }}</small></p>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Chênh lệch giá:</h6>
                                            @if($roomChange->price_difference > 0)
                                                <span class="text-danger h5">+{{ number_format($roomChange->price_difference, 0, ',', '.') }} VNĐ</span>
                                                <small class="text-muted d-block">Phòng mới đắt hơn</small>
                                            @elseif($roomChange->price_difference < 0)
                                                <span class="text-success h5">{{ number_format($roomChange->price_difference, 0, ',', '.') }} VNĐ</span>
                                                <small class="text-muted d-block">Phòng mới rẻ hơn</small>
                                            @else
                                                <span class="text-muted h5">Không có chênh lệch</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lý do và ghi chú -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Lý do và ghi chú</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Lý do đổi phòng:</h6>
                                            <p>{{ $roomChange->reason ?: 'Không có' }}</p>
                                        </div>
                                        <div class="col-md-6">
                                            <h6>Ghi chú của khách hàng:</h6>
                                            <p>{{ $roomChange->customer_note ?: 'Không có' }}</p>
                                        </div>
                                    </div>
                                    @if($roomChange->admin_note)
                                    <hr>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h6>Ghi chú của admin:</h6>
                                            <p class="text-info">{{ $roomChange->admin_note }}</p>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin người xử lý -->
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thông tin người xử lý</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6>Người yêu cầu:</h6>
                                            <p>{{ $roomChange->requestedBy->name }}</p>
                                            <small class="text-muted">{{ $roomChange->requestedBy->email }}</small>
                                        </div>
                                        @if($roomChange->approvedBy)
                                        <div class="col-md-6">
                                            <h6>Người duyệt:</h6>
                                            <p>{{ $roomChange->approvedBy->name }}</p>
                                            <small class="text-muted">{{ $roomChange->approvedBy->email }}</small>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thao tác -->
                    @if($roomChange->status === 'pending')
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thao tác</h5>
                                </div>
                                <div class="card-body">
                                    <button type="button" class="btn btn-success mr-2" onclick="approveRoomChange()">
                                        <i class="fa fa-check"></i> Duyệt yêu cầu
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="rejectRoomChange()">
                                        <i class="fa fa-times"></i> Từ chối yêu cầu
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @elseif($roomChange->status === 'approved')
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Thao tác</h5>
                                </div>
                                <div class="card-body">
                                    <button type="button" class="btn btn-primary" onclick="completeRoomChange()">
                                        <i class="fa fa-check-circle"></i> Hoàn thành đổi phòng
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Approve -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Duyệt yêu cầu đổi phòng</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.room-changes.approve', $roomChange->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="admin_note">Ghi chú (tùy chọn):</label>
                        <textarea name="admin_note" id="admin_note" class="form-control" rows="3" 
                                  placeholder="Ghi chú cho khách hàng..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Duyệt</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Từ chối yêu cầu đổi phòng</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.room-changes.reject', $roomChange->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label for="admin_note_reject">Lý do từ chối: <span class="text-danger">*</span></label>
                        <textarea name="admin_note" id="admin_note_reject" class="form-control" rows="3" 
                                  placeholder="Lý do từ chối yêu cầu..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function approveRoomChange() {
    $('#approveModal').modal('show');
}

function rejectRoomChange() {
    $('#rejectModal').modal('show');
}

function completeRoomChange() {
    if (confirm('Bạn có chắc chắn muốn hoàn thành đổi phòng này?')) {
        window.location.href = '{{ route("admin.room-changes.complete", $roomChange->id) }}';
    }
}
</script>
@endpush
@endsection 