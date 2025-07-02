@if ($reviews->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Phòng</th>
                    <th>Điểm đánh giá</th>
                    <th>Bình luận</th>
                    <th>Trạng thái</th>
                    <th>Ngày đánh giá</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($reviews as $review)
                    <tr>
                        <td>
                            <div>
                                <strong>{{ $review->room->name }}</strong>
                                @if($review->booking)
                                    <br>
                                    <small class="text-muted">Mã: {{ $review->booking->booking_id }}</small>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="text-warning">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                @endfor
                                <span class="ml-2">{{ $review->rating }}/5</span>
                            </div>
                        </td>
                        <td>
                            @if($review->comment)
                                <div class="text-truncate" style="max-width: 200px;" title="{{ $review->comment }}">
                                    {{ $review->comment }}
                                </div>
                            @else
                                <span class="text-muted"><em>Không có bình luận</em></span>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }}">
                                {{ $review->status_text }}
                            </span>
                        </td>
                        <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-sm btn-outline-info btn-view-review" data-review-id="{{ $review->id }}" title="Xem chi tiết">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @if($review->status == 'pending')
                                    <a href="{{ route('reviews.edit', $review->id) }}" class="btn btn-sm btn-outline-warning" title="Chỉnh sửa">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('reviews.destroy', $review->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Phân trang -->
        @if($reviews->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
@else
    <div class="text-center py-4">
        <i class="fas fa-star-o fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Chưa có đánh giá nào</h5>
        <p class="text-muted">Bạn chưa có đánh giá nào cho các đặt phòng.</p>
        <a href="{{ route('reviews.index') }}" class="btn btn-primary">
            <i class="fas fa-star"></i> Viết đánh giá ngay
        </a>
    </div>
@endif 