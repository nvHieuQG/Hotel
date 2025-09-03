@extends('admin.layouts.admin-master')

@section('title', 'Yêu cầu đổi phòng Tour')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Yêu cầu đổi phòng Tour #{{ $tourBooking->booking_id ?? $tourBookingId }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.tour-bookings.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($changes->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Từ phòng</th>
                                        <th>Đến phòng</th>
                                        <th>Chênh lệch giá</th>
                                        <th>Lý do</th>
                                        <th>Ghi chú khách</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày tạo</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($changes as $change)
                                        <tr>
                                            <td>{{ $change->id }}</td>
                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $change->fromRoom->room_number ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge badge-success">
                                                    {{ $change->toRoom->room_number ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($change->price_difference > 0)
                                                    <span class="text-success">+{{ number_format($change->price_difference) }} VNĐ</span>
                                                @elseif($change->price_difference < 0)
                                                    <span class="text-danger">{{ number_format($change->price_difference) }} VNĐ</span>
                                                @else
                                                    <span class="text-muted">0 VNĐ</span>
                                                @endif
                                            </td>
                                            <td>{{ $change->reason ?? 'Không có' }}</td>
                                            <td>{{ $change->customer_note ?? 'Không có' }}</td>
                                            <td>
                                                @switch($change->status)
                                                    @case('pending')
                                                        <span class="badge badge-warning">Chờ duyệt</span>
                                                        @break
                                                    @case('approved')
                                                        <span class="badge badge-success">Đã duyệt</span>
                                                        @break
                                                    @case('rejected')
                                                        <span class="badge badge-danger">Từ chối</span>
                                                        @break
                                                    @case('completed')
                                                        <span class="badge badge-info">Hoàn tất</span>
                                                        @break
                                                @endswitch
                                            </td>
                                            <td>{{ $change->created_at->format('d/m/Y H:i') }}</td>
                                            <td>
                                                @if($change->status === 'pending')
                                                    <div class="btn-group" role="group">
                                                        <form class="d-inline approve-form" method="POST" action="{{ route('admin.tour-room-changes.approve', $change->id) }}">
                                                            @csrf
                                                            <div class="input-group input-group-sm">
                                                                <div class="input-group-append">
                                                                    <button type="submit" class="btn btn-success btn-action mr-1" title="Duyệt">
                                                                        <i class="fa fa-check"></i>
                                                                    </button>
                                                                </div>
                                                                <input type="text" name="admin_note" class="form-control input-note" placeholder="Ghi chú (tùy chọn)">
                                                            </div>
                                                        </form>
                                                        <form class="d-inline reject-form" method="POST" action="{{ route('admin.tour-room-changes.reject', $change->id) }}">
                                                            @csrf
                                                            <div class="input-group input-group-sm">
                                                                <div class="input-group-append">
                                                                    <button type="submit" class="btn btn-danger btn-action ml-1" title="Từ chối">
                                                                        <i class="fa fa-times"></i>
                                                                    </button>
                                                                </div>
                                                                <input type="text" name="admin_note" class="form-control input-note" placeholder="Ghi chú (tùy chọn)">
                                                            </div>
                                                        </form>
                                                    </div>
                                                @elseif($change->status === 'approved')
                                                    <form class="d-inline complete-form" method="POST" action="{{ route('admin.tour-room-changes.complete', $change->id) }}">
                                                        @csrf
                                                        <button type="submit" class="btn btn-info btn-sm" title="Hoàn tất">
                                                            <i class="fa fa-check-circle"></i> Hoàn tất
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Chưa có yêu cầu đổi phòng nào cho tour này.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    function sendAjaxRequest(url, data, successMessage, errorMessage) {
        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errorData => {
                    throw new Error(errorData.message || errorMessage);
                });
            }
            return response.json();
        })
        .then(data => {
            toastr.success(data.message || successMessage);
            setTimeout(() => location.reload(), 600);
        })
        .catch(error => {
            toastr.error(error.message || errorMessage);
        });
    }

    document.querySelectorAll('.approve-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const url = this.action;
            const adminNoteInput = this.querySelector('input[name="admin_note"]');
            const adminNote = adminNoteInput ? adminNoteInput.value : '';
            sendAjaxRequest(url, { admin_note: adminNote }, 'Đã duyệt yêu cầu đổi phòng tour thành công.', 'Không thể duyệt yêu cầu đổi phòng tour.');
        });
    });

    document.querySelectorAll('.reject-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const url = this.action;
            const adminNoteInput = this.querySelector('input[name="admin_note"]');
            const adminNote = adminNoteInput ? adminNoteInput.value : '';
            sendAjaxRequest(url, { admin_note: adminNote }, 'Đã từ chối yêu cầu đổi phòng tour.', 'Không thể từ chối yêu cầu đổi phòng tour.');
        });
    });

    document.querySelectorAll('.complete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const url = this.action;
            sendAjaxRequest(url, {}, 'Đã hoàn tất đổi phòng tour.', 'Không thể hoàn tất yêu cầu đổi phòng tour.');
        });
    });
});
</script>
@endpush
