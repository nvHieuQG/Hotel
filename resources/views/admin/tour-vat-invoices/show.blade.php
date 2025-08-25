@extends('admin.layouts.admin-master')

@section('title', 'Chi tiết VAT Invoice Tour Booking')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice"></i> Chi tiết VAT Invoice Tour Booking
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.tour-vat-invoices.index') }}" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Quay lại
                        </a>
                        @if($tourBooking->vat_invoice_number)
                            <a href="{{ route('admin.tour-vat-invoices.download', $tourBooking->id) }}" 
                               class="btn btn-success btn-sm">
                                <i class="fas fa-download"></i> Tải xuống
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-check"></i> Thành công!</h5>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                            <h5><i class="icon fas fa-ban"></i> Lỗi!</h5>
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Thông tin Tour Booking -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin Tour Booking</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>ID:</strong></td>
                                            <td>{{ $tourBooking->id }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Mã Booking:</strong></td>
                                            <td>
                                                <a href="{{ route('admin.tour-bookings.show', $tourBooking->id) }}" 
                                                   class="text-primary font-weight-bold">
                                                    {{ $tourBooking->booking_id }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tên Tour:</strong></td>
                                            <td>{{ $tourBooking->tour_name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Số khách:</strong></td>
                                            <td>{{ $tourBooking->total_guests }} người</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Số phòng:</strong></td>
                                            <td>{{ $tourBooking->total_rooms }} phòng</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Check-in:</strong></td>
                                            <td>{{ $tourBooking->check_in_date->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Check-out:</strong></td>
                                            <td>{{ $tourBooking->check_out_date->format('d/m/Y') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Số đêm:</strong></td>
                                            <td>{{ $tourBooking->total_nights }} đêm</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin Khách hàng</h5>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Họ tên:</strong></td>
                                            <td>{{ $tourBooking->user->name }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Email:</strong></td>
                                            <td>{{ $tourBooking->user->email }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Ngày đặt:</strong></td>
                                            <td>{{ $tourBooking->created_at->format('d/m/Y H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Trạng thái:</strong></td>
                                            <td>
                                                <span class="badge badge-{{ $tourBooking->status === 'confirmed' ? 'success' : ($tourBooking->status === 'pending' ? 'warning' : 'secondary') }}">
                                                    {{ $tourBooking->status_text }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin Công ty -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin Công ty</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Tên công ty:</strong></td>
                                                    <td>{{ $tourBooking->company_name }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Mã số thuế:</strong></td>
                                                    <td>{{ $tourBooking->company_tax_code }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Email:</strong></td>
                                                    <td>{{ $tourBooking->company_email }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Địa chỉ:</strong></td>
                                                    <td>{{ $tourBooking->company_address }}</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Điện thoại:</strong></td>
                                                    <td>{{ $tourBooking->company_phone }}</td>
                                                </tr>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thông tin Giá và VAT -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Thông tin Giá và VAT</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Tiền phòng:</strong></td>
                                                    <td>{{ number_format($paymentInfo['roomCost'], 0, ',', '.') }} VNĐ</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tiền dịch vụ:</strong></td>
                                                    <td>{{ number_format($paymentInfo['services'], 0, ',', '.') }} VNĐ</td>
                                                </tr>
                                                <tr>
                                                    <td><strong>Tổng cộng:</strong></td>
                                                    <td class="text-warning">{{ number_format($paymentInfo['roomCost'] + $paymentInfo['services'], 0, ',', '.') }} VNĐ</td>
                                                </tr>
                                                @if($paymentInfo['discount'] > 0)
                                                    <tr>
                                                        <td><strong>Giảm giá:</strong></td>
                                                        <td class="text-success">-{{ number_format($paymentInfo['discount'], 0, ',', '.') }} VNĐ</td>
                                                    </tr>
                                                    @if($tourBooking->promotion_code)
                                                        <tr>
                                                            <td><strong>Mã giảm giá:</strong></td>
                                                            <td class="text-muted small">{{ $tourBooking->promotion_code }}</td>
                                                        </tr>
                                                    @endif
                                                    <tr>
                                                        <td><strong>Giá cuối:</strong></td>
                                                        <td class="text-danger font-weight-bold">{{ number_format($paymentInfo['totalDue'], 0, ',', '.') }} VNĐ</td>
                                                    </tr>
                                                @else
                                                    <tr>
                                                        <td><strong>Giá cuối:</strong></td>
                                                        <td class="text-danger font-weight-bold">{{ number_format($paymentInfo['totalDue'], 0, ',', '.') }} VNĐ</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                        <div class="col-md-6">
                                            <table class="table table-borderless">
                                                <tr>
                                                    <td><strong>Trạng thái VAT:</strong></td>
                                                    <td>
                                                        @if($tourBooking->vat_invoice_status === 'sent')
                                                            <span class="badge badge-success">
                                                                <i class="fas fa-check"></i> Đã hoàn thành
                                                            </span>
                                                        @elseif($tourBooking->vat_invoice_status === 'generated')
                                                            <span class="badge badge-info">
                                                                <i class="fas fa-file-pdf"></i> Đã tạo file
                                                            </span>
                                                        @elseif($tourBooking->vat_invoice_number)
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-clock"></i> Đã xuất hóa đơn
                                                            </span>
                                                        @else
                                                            <span class="badge badge-warning">
                                                                <i class="fas fa-clock"></i> Chờ xử lý
                                                            </span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @if($tourBooking->vat_invoice_number)
                                                    <tr>
                                                        <td><strong>Mã hóa đơn:</strong></td>
                                                        <td>{{ $tourBooking->vat_invoice_number }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Ngày xuất:</strong></td>
                                                        <td>{{ $tourBooking->vat_invoice_created_at->format('d/m/Y H:i') }}</td>
                                                    </tr>
                                                    <tr>
                                                        <td><strong>Trạng thái:</strong></td>
                                                        <td>
                                                            @if($tourBooking->vat_invoice_status === 'generated')
                                                                <span class="badge badge-info">
                                                                    <i class="fas fa-file-pdf"></i> Đã tạo file
                                                                </span>
                                                            @elseif($tourBooking->vat_invoice_status === 'sent')
                                                                <span class="badge badge-success">
                                                                    <i class="fas fa-envelope"></i> Đã gửi email
                                                                </span>
                                                            @else
                                                                <span class="badge badge-warning">
                                                                    <i class="fas fa-clock"></i> {{ $tourBooking->vat_invoice_status ?? 'pending' }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @if($tourBooking->vat_invoice_file_path)
                                                        <tr>
                                                            <td><strong>File PDF:</strong></td>
                                                            <td>
                                                                <a href="{{ asset(str_starts_with($tourBooking->vat_invoice_file_path,'public/') ? Storage::url(str_replace('public/','',$tourBooking->vat_invoice_file_path)) : $tourBooking->vat_invoice_file_path) }}" 
                                                                   target="_blank" class="btn btn-info btn-sm">
                                                                    <i class="fas fa-eye"></i> Xem file
                                                                </a>
                                                                <a href="{{ route('admin.tour-vat-invoices.download', $tourBooking->id) }}" 
                                                                   class="btn btn-success btn-sm">
                                                                    <i class="fas fa-download"></i> Tải xuống
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                    @if($tourBooking->vat_invoice_generated_at)
                                                        <tr>
                                                            <td><strong>Ngày tạo file:</strong></td>
                                                            <td>{{ $tourBooking->vat_invoice_generated_at->format('d/m/Y H:i') }}</td>
                                                        </tr>
                                                    @endif
                                                    @if($tourBooking->vat_invoice_sent_at)
                                                        <tr>
                                                            <td><strong>Ngày gửi email:</strong></td>
                                                            <td>{{ $tourBooking->vat_invoice_sent_at->format('d/m/Y H:i') }}</td>
                                                        </tr>
                                                    @endif
                                                @endif
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chi tiết phòng -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Chi tiết phòng đã đặt</h5>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Loại phòng</th>
                                                    <th>Số lượng</th>
                                                    <th>Số khách/phòng</th>
                                                    <th>Giá/phòng</th>
                                                    <th>Tổng tiền</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tourBooking->tourBookingRooms as $room)
                                                    <tr>
                                                        <td>
                                                            <strong>{{ $room->roomType->name }}</strong>
                                                            <br><small class="text-muted">{{ $room->roomType->description }}</small>
                                                        </td>
                                                        <td>{{ $room->quantity }}</td>
                                                        <td>{{ $room->guests_per_room }}</td>
                                                        <td>{{ number_format($room->price_per_room, 0, ',', '.') }} VNĐ</td>
                                                        <td>{{ number_format($room->total_price, 0, ',', '.') }} VNĐ</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="table-info">
                                                <tr>
                                                    <td colspan="4" class="text-right"><strong>Tổng tiền phòng:</strong></td>
                                                    <td class="text-right font-weight-bold">{{ number_format($paymentInfo['roomCost'], 0, ',', '.') }} VNĐ</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Yêu cầu đặc biệt -->
                    @if($tourBooking->special_requests)
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Yêu cầu đặc biệt</h5>
                                    </div>
                                    <div class="card-body">
                                        <p class="mb-0">{{ $tourBooking->special_requests }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Hành động -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body text-center">
                                    @if(!$tourBooking->vat_invoice_number)
                                        <button type="button" class="btn btn-success btn-lg" 
                                                data-toggle="modal" 
                                                data-target="#generateVatModal">
                                            <i class="fas fa-file-invoice"></i> Tạo hóa đơn VAT
                                        </button>
                                        
                                        <button type="button" class="btn btn-danger btn-lg" 
                                                data-toggle="modal" 
                                                data-target="#rejectVatModal">
                                            <i class="fas fa-times"></i> Từ chối yêu cầu
                                        </button>
                                    @else
                                        <div class="alert alert-success">
                                            <i class="fas fa-check-circle fa-2x mb-3"></i>
                                            <h5>Hóa đơn VAT đã được tạo!</h5>
                                            <p>Mã hóa đơn: <strong>{{ $tourBooking->vat_invoice_number }}</strong></p>
                                            <p>Ngày xuất: {{ $tourBooking->vat_invoice_created_at->format('d/m/Y H:i') }}</p>
                                            @if($tourBooking->vat_invoice_file_path)
                                                <p>File PDF: <strong>Đã tạo thành công</strong></p>
                                            @endif
                                            @if($tourBooking->vat_invoice_status === 'sent')
                                                <p>Email: <strong>Đã gửi thành công</strong></p>
                                            @else
                                                <p>Email: <strong>Chưa gửi</strong></p>
                                                <a href="{{ route('admin.tour-vat-invoices.send', $tourBooking->id) }}" 
                                                   class="btn btn-primary btn-sm">
                                                    <i class="fas fa-envelope"></i> Gửi email hóa đơn
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate VAT Modal -->
@if(!$tourBooking->vat_invoice_number)
    <div class="modal fade" id="generateVatModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.tour-vat-invoices.generate', $tourBooking->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Tạo hóa đơn VAT</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="vat_invoice_number">Mã hóa đơn VAT <span class="text-danger">*</span></label>
                            <input type="text" name="vat_invoice_number" id="vat_invoice_number" 
                                   class="form-control" required 
                                   placeholder="Nhập mã hóa đơn VAT">
                        </div>
                        <div class="form-group">
                            <label for="notes">Ghi chú</label>
                            <textarea name="notes" id="notes" class="form-control" rows="3" 
                                      placeholder="Ghi chú (nếu có)"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-check"></i> Tạo hóa đơn
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject VAT Modal -->
    <div class="modal fade" id="rejectVatModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{ route('admin.tour-vat-invoices.reject', $tourBooking->id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Từ chối yêu cầu VAT</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="rejection_reason">Lý do từ chối <span class="text-danger">*</span></label>
                            <textarea name="rejection_reason" id="rejection_reason" class="form-control" 
                                      rows="4" required 
                                      placeholder="Nhập lý do từ chối yêu cầu VAT"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-times"></i> Từ chối
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
@endsection
