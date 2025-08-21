@extends('admin.layouts.admin-master')

@section('title', 'Quản lý VAT Invoice Tour Booking')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice"></i> Quản lý VAT Invoice Tour Booking
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.tour-vat-invoices.statistics') }}" class="btn btn-info btn-sm">
                            <i class="fas fa-chart-bar"></i> Thống kê
                        </a>
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

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Mã Booking</th>
                                    <th>Tên Tour</th>
                                    <th>Khách hàng</th>
                                    <th>Công ty</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày yêu cầu</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vatRequests as $request)
                                    <tr>
                                        <td>{{ $request->id }}</td>
                                        <td>
                                            <a href="{{ route('admin.tour-bookings.show', $request->id) }}" 
                                               class="text-primary font-weight-bold">
                                                {{ $request->booking_id }}
                                            </a>
                                        </td>
                                        <td>{{ $request->tour_name }}</td>
                                        <td>
                                            <strong>{{ $request->user->name }}</strong><br>
                                            <small class="text-muted">{{ $request->user->email }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $request->company_name }}</strong><br>
                                            <small class="text-muted">MST: {{ $request->company_tax_code }}</small>
                                        </td>
                                        <td>
                                            @if($request->vat_invoice_number)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Đã xuất
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-clock"></i> Chờ xử lý
                                                </span>
                                            @endif
                                        </td>
                                        <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.tour-vat-invoices.show', $request->id) }}" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Xem
                                            </a>
                                            
                                            @if(!$request->vat_invoice_number)
                                                <button type="button" class="btn btn-success btn-sm" 
                                                        data-toggle="modal" 
                                                        data-target="#generateVatModal{{ $request->id }}">
                                                    <i class="fas fa-file-invoice"></i> Tạo VAT
                                                </button>
                                                
                                                <button type="button" class="btn btn-danger btn-sm" 
                                                        data-toggle="modal" 
                                                        data-target="#rejectVatModal{{ $request->id }}">
                                                    <i class="fas fa-times"></i> Từ chối
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Không có yêu cầu VAT invoice nào</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($vatRequests->hasPages())
                        <div class="d-flex justify-content-center">
                            {{ $vatRequests->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Generate VAT Modal -->
@foreach($vatRequests as $request)
    @if(!$request->vat_invoice_number)
        <div class="modal fade" id="generateVatModal{{ $request->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.tour-vat-invoices.generate', $request->id) }}" method="POST">
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
        <div class="modal fade" id="rejectVatModal{{ $request->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('admin.tour-vat-invoices.reject', $request->id) }}" method="POST">
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
@endforeach
@endsection
