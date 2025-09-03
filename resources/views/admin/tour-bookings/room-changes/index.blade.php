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
                        <a href="{{ route('staff.admin.tour-bookings.room-changes.create', $tourBookingId) }}" class="btn btn-success mr-2">
                            <i class="fas fa-plus"></i> Tạo yêu cầu đổi phòng
                        </a>
                        <a href="{{ route('admin.tour-bookings.show', $tourBookingId) }}" class="btn btn-info mr-2">
                            <i class="fas fa-info-circle"></i> Chi tiết tour
                        </a>
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
                        {{-- Hiển thị dạng card cho từng yêu cầu --}}
                        @foreach($changes as $change)
                            @include('admin.tour-bookings.room-changes.partials.room-change-card', ['change' => $change])
                        @endforeach
                        
                        {{-- Tùy chọn hiển thị dạng bảng (có thể toggle) --}}
                        <div class="mt-4">
                            <button class="btn btn-outline-secondary btn-sm" type="button" data-toggle="collapse" data-target="#tableView" aria-expanded="false">
                                <i class="fas fa-table"></i> Xem dạng bảng
                            </button>
                        </div>
                        
                        <div class="collapse mt-3" id="tableView">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped">
                                    <thead class="thead-dark">
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
                                                        <div class="d-flex flex-column">
                                                                                                                    <form class="approve-form mb-2" method="POST" action="{{ route('staff.admin.tour-room-changes.approve', $change->id) }}">
                                                            @csrf
                                                            <div class="input-group input-group-sm">
                                                                <input type="text" name="admin_note" class="form-control" placeholder="Ghi chú (tùy chọn)">
                                                                <div class="input-group-append">
                                                                    <button type="submit" class="btn btn-success" title="Duyệt">
                                                                        <i class="fa fa-check"></i> Duyệt
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                        <form class="reject-form" method="POST" action="{{ route('staff.admin.tour-room-changes.reject', $change->id) }}">
                                                            @csrf
                                                            <div class="input-group input-group-sm">
                                                                <input type="text" name="admin_note" class="form-control" placeholder="Ghi chú (tùy chọn)">
                                                                <div class="input-group-append">
                                                                    <button type="submit" class="btn btn-danger" title="Từ chối">
                                                                        <i class="fa fa-times"></i> Từ chối
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                        </div>
                                                    @elseif($change->status === 'approved')
                                                        <form class="d-inline complete-form" method="POST" action="{{ route('staff.admin.tour-room-changes.complete', $change->id) }}">
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

    // Xử lý tất cả form approve, reject, complete
    document.querySelectorAll('.approve-form, .reject-form, .complete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const url = this.action;
            const adminNoteInput = this.querySelector('input[name="admin_note"]');
            const adminNote = adminNoteInput ? adminNoteInput.value : '';
            
            let successMessage, errorMessage;
            if (this.classList.contains('approve-form')) {
                successMessage = 'Đã duyệt yêu cầu đổi phòng tour thành công.';
                errorMessage = 'Không thể duyệt yêu cầu đổi phòng tour.';
            } else if (this.classList.contains('reject-form')) {
                successMessage = 'Đã từ chối yêu cầu đổi phòng tour.';
                errorMessage = 'Không thể từ chối yêu cầu đổi phòng tour.';
            } else if (this.classList.contains('complete-form')) {
                successMessage = 'Đã hoàn tất đổi phòng tour.';
                errorMessage = 'Không thể hoàn tất yêu cầu đổi phòng tour.';
            }
            
            sendAjaxRequest(url, { admin_note: adminNote }, successMessage, errorMessage);
        });
    });
});
</script>
@endpush
