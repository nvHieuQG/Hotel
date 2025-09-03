{{-- Component hiển thị thông tin yêu cầu đổi phòng dạng card --}}
<div class="card mb-3 border-left-{{ $change->status === 'pending' ? 'warning' : ($change->status === 'approved' ? 'success' : ($change->status === 'rejected' ? 'danger' : 'info')) }}">
    <div class="card-header bg-{{ $change->status === 'pending' ? 'warning' : ($change->status === 'approved' ? 'success' : ($change->status === 'rejected' ? 'danger' : 'info')) }} text-white">
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="fas fa-exchange-alt"></i>
                Yêu cầu đổi phòng #{{ $change->id }}
            </h6>
            <span class="badge badge-light">
                @switch($change->status)
                    @case('pending')
                        <i class="fas fa-clock"></i> Chờ duyệt
                        @break
                    @case('approved')
                        <i class="fas fa-check"></i> Đã duyệt
                        @break
                    @case('rejected')
                        <i class="fas fa-times"></i> Từ chối
                        @break
                    @case('completed')
                        <i class="fas fa-check-circle"></i> Hoàn tất
                        @break
                @endswitch
            </span>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6><i class="fas fa-bed"></i> Thông tin phòng:</h6>
                <div class="d-flex align-items-center mb-2">
                    <span class="badge badge-info mr-2">{{ $change->fromRoom->room_number ?? 'N/A' }}</span>
                    <i class="fas fa-arrow-right mx-2"></i>
                    @php $toText = $change->toRoom->room_number ?? null; @endphp
                    @if(!$toText && $change->suggested_to_room_id)
                        @php $sug = \App\Models\Room::find($change->suggested_to_room_id); @endphp
                        <span class="badge badge-warning">Đề xuất: {{ $sug?->room_number ?? 'N/A' }}</span>
                    @else
                        <span class="badge badge-success">{{ $toText ?? 'N/A' }}</span>
                    @endif
                </div>
                <p class="mb-1"><strong>Loại phòng:</strong> {{ $change->fromRoom->roomType->name ?? 'N/A' }}</p>
                <p class="mb-0"><strong>Lý do:</strong> {{ $change->reason ?? 'Không có' }}</p>
            </div>
            <div class="col-md-6">
                <h6><i class="fas fa-money-bill-wave"></i> Thông tin tài chính:</h6>
                @if($change->price_difference != 0)
                    <div class="alert alert-{{ $change->price_difference > 0 ? 'warning' : 'success' }} py-2 mb-2">
                        <strong>
                            @if($change->price_difference > 0)
                                <i class="fas fa-arrow-up"></i> Cần thanh toán thêm: +{{ number_format($change->price_difference) }} VNĐ
                            @else
                                <i class="fas fa-arrow-down"></i> Được hoàn tiền: {{ number_format($change->price_difference) }} VNĐ
                            @endif
                        </strong>
                    </div>
                @else
                    <div class="alert alert-info py-2 mb-2">
                        <strong><i class="fas fa-equals"></i> Không có chênh lệch giá</strong>
                    </div>
                @endif
                <p class="mb-0"><strong>Ghi chú khách:</strong> {{ $change->customer_note ?? 'Không có' }}</p>
            </div>
        </div>
        
        @if($change->status === 'pending')
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <form class="approve-form" method="POST" action="{{ route('staff.admin.tour-room-changes.approve', $change->id) }}">
                        @csrf
                        <div class="form-group">
                            <label class="text-success"><i class="fas fa-check"></i> Duyệt yêu cầu:</label>
                            <div class="input-group">
                                <input type="text" name="admin_note" class="form-control" placeholder="Ghi chú duyệt (tùy chọn)">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-check"></i> Duyệt
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-6">
                    <form class="reject-form" method="POST" action="{{ route('staff.admin.tour-room-changes.reject', $change->id) }}">
                        @csrf
                        <div class="form-group">
                            <label class="text-danger"><i class="fas fa-times"></i> Từ chối yêu cầu:</label>
                            <div class="input-group">
                                <input type="text" name="admin_note" class="form-control" placeholder="Lý do từ chối (tùy chọn)">
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-danger">
                                        <i class="fas fa-times"></i> Từ chối
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @elseif($change->status === 'approved')
            <hr>
            <div class="text-center">
                <form class="d-inline complete-form" method="POST" action="{{ route('staff.admin.tour-room-changes.complete', $change->id) }}">
                    @csrf
                    <button type="submit" class="btn btn-info btn-lg">
                        <i class="fas fa-check-circle"></i> Hoàn tất đổi phòng
                    </button>
                </form>
            </div>
        @endif
        
        <div class="mt-3">
            <small class="text-muted">
                <i class="fas fa-clock"></i> Tạo lúc: {{ $change->created_at->format('d/m/Y H:i:s') }}
                @if($change->approved_at)
                    | <i class="fas fa-user-check"></i> Duyệt bởi: {{ $change->approvedBy->name ?? 'N/A' }} lúc {{ $change->approved_at->format('d/m/Y H:i:s') }}
                @endif
            </small>
        </div>
    </div>
</div>
