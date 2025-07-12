@extends('admin.layouts.admin-master')

@section('content')
<div class="container mt-4">
    <div class="card shadow rounded">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">Gán dịch vụ cho loại phòng: <strong>{{ $roomType->name }}</strong></h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.room-type-services.update', $roomType->id) }}" method="POST">
                @csrf
                @method('PUT')

                @foreach($categories as $category)
                    <div class="mb-4">
                        <h6 class="text-primary mb-2">{{ $category->name }}</h6>
                        <div class="row">
                            @forelse($services->where('service_category_id', $category->id) as $service)
                                <div class="col-md-4">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" name="service_ids[]" value="{{ $service->id }}"
                                               id="service_{{ $service->id }}" {{ in_array($service->id, $selectedServices) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="service_{{ $service->id }}">
                                            {{ $service->name }}
                                        </label>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-muted ps-3">Không có dịch vụ nào trong danh mục này.</div>
                            @endforelse
                        </div>
                    </div>
                @endforeach

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Lưu
                    </button>
                    <a href="{{ route('admin.room-type-services.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
