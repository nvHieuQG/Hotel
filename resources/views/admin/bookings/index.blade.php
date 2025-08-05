@extends('admin.layouts.admin-master')

@section('header', 'Quản lý đặt phòng')

@section('content')
<div class="container-fluid px-4">
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Quản lý đặt phòng</li>
    </ol>
    
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                <div>
                    <i class="fas fa-table me-1"></i>
                    Danh sách đặt phòng
                </div>
                <div>
                    <a href="{{ route('admin.bookings.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus me-1"></i> Tạo đặt phòng mới
                    </a>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="mb-3">
                <form action="{{ route('admin.bookings.index') }}" method="GET" class="row g-3">
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="status" class="form-label">Lọc theo trạng thái:</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Chờ xác nhận</option>
                            <option value="confirmed" {{ $status == 'confirmed' ? 'selected' : '' }}>Đã xác nhận</option>
                            <option value="checked_in" {{ $status == 'checked_in' ? 'selected' : '' }}>Đã nhận phòng</option>
                            <option value="checked_out" {{ $status == 'checked_out' ? 'selected' : '' }}>Đã trả phòng</option>
                            <option value="completed" {{ $status == 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="cancelled" {{ $status == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                            <option value="no_show" {{ $status == 'no_show' ? 'selected' : '' }}>Không đến</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="payment_status" class="form-label">Lọc theo thanh toán:</label>
                        <select name="payment_status" id="payment_status" class="form-select">
                            <option value="">Tất cả</option>
                            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                            <option value="processing" {{ request('payment_status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                            <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Chờ thanh toán</option>
                            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Thanh toán thất bại</option>
                            <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label">&nbsp;</label>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-fill">
                                <i class="fas fa-filter me-1"></i> Lọc
                            </button>
                            <a href="{{ route('admin.bookings.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Desktop Table View -->
            <div class="d-none d-lg-block">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>STT</th>
                                <th>Mã đặt phòng</th>
                                <th>Khách hàng</th>
                                <th>Phòng</th>
                                <th>Ngày check-in</th>
                                <th>Ngày check-out</th>
                                <th>Giá</th>
                                <th>Trạng thái</th>
                                <th>Thanh toán</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bookings as $booking)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $booking->booking_id }}</td>
                                <td>{{ $booking->user->name }}</td>
                                <td>{{ $booking->room->name }}</td>
                                <td>{{ $booking->check_in_date }}</td>    
                                <td>{{ $booking->check_out_date }}</td>
                                <td>{{ number_format($booking->price) }} VND</td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $booking->status == 'pending' ? 'warning' : 
                                        ($booking->status == 'confirmed' ? 'primary' : 
                                        ($booking->status == 'checked_in' ? 'info' :
                                        ($booking->status == 'checked_out' ? 'success' :
                                        ($booking->status == 'completed' ? 'success' :
                                        ($booking->status == 'cancelled' ? 'danger' : 'secondary'))))) }}">
                                        {{ $booking->status_text }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ 
                                        $booking->payment_status == 'paid' ? 'success' : 
                                        ($booking->payment_status == 'processing' ? 'info' :
                                        ($booking->payment_status == 'pending' ? 'warning' :
                                        ($booking->payment_status == 'failed' ? 'danger' : 'secondary'))) }}">
                                        {{ $booking->payment_status_text }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.bookings.generate-pdf', $booking->id) }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile/Tablet Card View -->
            <div class="d-lg-none">
                @foreach($bookings as $booking)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <h6 class="card-title mb-1">{{ $booking->booking_id }}</h6>
                                <small class="text-muted">{{ $booking->user->name }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-{{ 
                                    $booking->status == 'pending' ? 'warning' : 
                                    ($booking->status == 'confirmed' ? 'primary' : 
                                    ($booking->status == 'checked_in' ? 'info' :
                                    ($booking->status == 'checked_out' ? 'success' :
                                    ($booking->status == 'completed' ? 'success' :
                                    ($booking->status == 'cancelled' ? 'danger' : 'secondary'))))) }}">
                                    {{ $booking->status_text }}
                                </span>
                            </div>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Phòng:</small>
                                <strong>{{ $booking->room->name }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Giá:</small>
                                <strong>{{ number_format($booking->price) }} VND</strong>
                            </div>
                        </div>
                        
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <small class="text-muted d-block">Check-in:</small>
                                <strong>{{ $booking->check_in_date }}</strong>
                            </div>
                            <div class="col-6">
                                <small class="text-muted d-block">Check-out:</small>
                                <strong>{{ $booking->check_out_date }}</strong>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-{{ 
                                $booking->payment_status == 'paid' ? 'success' : 
                                ($booking->payment_status == 'processing' ? 'info' :
                                ($booking->payment_status == 'pending' ? 'warning' :
                                ($booking->payment_status == 'failed' ? 'danger' : 'secondary'))) }}">
                                {{ $booking->payment_status_text }}
                            </span>
                            
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ route('admin.bookings.show', $booking->id) }}" class="btn btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.bookings.generate-pdf', $booking->id) }}" class="btn btn-success">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Phân trang -->
            @if($bookings->count() > 0)
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                    <div class="small text-muted mb-2 mb-md-0">
                        Hiển thị {{ $bookings->firstItem() ?? 0 }} - {{ $bookings->lastItem() ?? 0 }} 
                        trong tổng số {{ $bookings->total() }} đặt phòng
                    </div>
                    <div>
                        {{ $bookings->appends(request()->query())->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        
    });
</script>
@endsection 