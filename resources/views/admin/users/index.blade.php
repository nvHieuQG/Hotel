@extends('admin.layouts.admin-master')
@section('title', 'Quản lý người dùng')
@section('header', 'Danh sách người dùng')
@section('content')

<div class="card shadow mb-4">
    <div class="card-header py-3">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
            <h6 class="m-0 font-weight-bold text-primary">Danh sách người dùng</h6>
            <div>
                <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Thêm người dùng mới
                </a>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Desktop Table View -->
        <div class="d-none d-lg-block">
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Họ tên</th>
                            <th>Email</th>
                            <th>Tên đăng nhập</th>
                            <th>Điện thoại</th>
                            <th>Vai trò</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>
                                <span class="badge bg-{{ 
                                    $user->role->name == 'Admin' ? 'danger' : 
                                    ($user->role->name == 'Staff' ? 'warning' : 'info') }}">
                                    {{ $user->role->name ?? 'Khách' }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Xác nhận xóa user này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
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
            @foreach($users as $user)
            <div class="card mb-3">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <h6 class="card-title mb-1">{{ $user->name }}</h6>
                            <small class="text-muted">{{ $user->email }}</small>
                        </div>
                        <span class="badge bg-{{ 
                            $user->role->name == 'Admin' ? 'danger' : 
                            ($user->role->name == 'Staff' ? 'warning' : 'info') }}">
                            {{ $user->role->name ?? 'Khách' }}
                        </span>
                    </div>
                    
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <small class="text-muted d-block">ID:</small>
                            <strong>{{ $user->id }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Tên đăng nhập:</small>
                            <strong>{{ $user->username }}</strong>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted d-block">Điện thoại:</small>
                        <strong>{{ $user->phone ?? 'Chưa cập nhật' }}</strong>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm flex-fill">
                            <i class="fas fa-eye me-1"></i> Xem
                        </a>
                        <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning btn-sm flex-fill">
                            <i class="fas fa-edit me-1"></i> Sửa
                        </a>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline flex-fill" onsubmit="return confirm('Xác nhận xóa user này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm w-100">
                                <i class="fas fa-trash me-1"></i> Xóa
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Phân trang -->
        @if($users->count() > 0)
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3">
                <div class="small text-muted mb-2 mb-md-0">
                    Hiển thị {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} 
                    trong tổng số {{ $users->total() }} người dùng
                </div>
                <div>
                    {{ $users->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

@endsection 