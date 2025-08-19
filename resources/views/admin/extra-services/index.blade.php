@extends('admin.layouts.admin-master')
@section('title', 'Dịch vụ bổ sung')
@section('content')

<div class="container my-4">
    <div class="card shadow">
        <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
            <div><i class="bi bi-list-ul"></i> <b>Danh sách dịch vụ bổ sung</b></div>
            <a href="{{ route('admin.extra-services.create') }}" class="btn btn-success"><i class="bi bi-plus-circle"></i> Thêm dịch vụ</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-secondary">
                    <tr class="align-middle text-center">
                        <th><i class="bi bi-hash"></i></th>
                        <th><i class="bi bi-card-text"></i> Tên dịch vụ</th>
                        <th><i class="bi bi-info-circle"></i> Mô tả</th>
                        <th><i class="bi bi-people"></i> Áp dụng</th>
                        <th><i class="bi bi-person"></i> Giá NL</th>
                        <th><i class="bi bi-emoji-smile"></i> Giá TE</th>
                        <th><i class="bi bi-123"></i> Độ tuổi TE</th>
                        <th><i class="bi bi-calculator"></i> Kiểu tính phí</th>
                        <th><i class="bi bi-toggle-on"></i> Trạng thái</th>
                        <th><i class="bi bi-gear"></i> Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($extraServices as $service)
                        <tr>
                            <td>{{ $service->id }}</td>
                            <td>{{ $service->name }}</td>
                            <td>{{ $service->description }}</td>
                            <td>
                                @switch($service->applies_to)
                                    @case('both') Người lớn & Trẻ em @break
                                    @case('adult') Chỉ người lớn @break
                                    @case('child') Chỉ trẻ em @break
                                    @default -
                                @endswitch
                            </td>
                            <td>
                                {{ is_numeric($service->price_adult) ? number_format($service->price_adult, 0, ',', '.') . ' VNĐ' : '-' }}
                            </td>
                            <td>
                                {{ is_numeric($service->price_child) ? number_format($service->price_child, 0, ',', '.') . ' VNĐ' : '-' }}
                            </td>
                            <td>
                                @if(!is_null($service->child_age_min) && !is_null($service->child_age_max))
                                    {{ $service->child_age_min }} - {{ $service->child_age_max }} tuổi
                                @elseif(!is_null($service->child_age_min))
                                    &ge; {{ $service->child_age_min }} tuổi
                                @elseif(!is_null($service->child_age_max))
                                    &le; {{ $service->child_age_max }} tuổi
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @switch($service->charge_type)
                                    @case('per_person') Theo người @break
                                    @case('per_night') Theo đêm @break
                                    @case('per_service') Theo dịch vụ @break
                                    @case('per_hour') Theo giờ @break
                                    @case('per_use') Theo lượt sử dụng @break
                                    @default -
                                @endswitch
                            </td>
                            
                            <td>
                                <span class="badge {{ $service->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $service->is_active ? 'Kích hoạt' : 'Không kích hoạt' }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.extra-services.edit', $service->id) }}" class="btn btn-sm btn-warning">Sửa</a>
                                <form action="{{ route('admin.extra-services.destroy', $service->id) }}" method="POST" style="display:inline-block;" onsubmit="return confirm('Bạn có chắc muốn xóa?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Không có dịch vụ nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
