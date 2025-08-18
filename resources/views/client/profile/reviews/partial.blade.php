@if ($reviews->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Phòng</th>
                    <th>Điểm đánh giá</th>
                    <th>Nội dung đánh giá</th>
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
                                <strong>{{ $review->roomType->name }}</strong>
                                @if($review->roomType)
                                    <br>
                                    <small class="text-muted">Loại phòng: {{ $review->roomType->name }}</small>
                                    @if($review->booking)
                                        <br>
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-view-booking" data-booking-id="{{ $review->booking->id }}" title="Xem chi tiết đặt phòng">
                                            <i class="fas fa-calendar-check"></i> Xem đặt phòng
                                        </button>
                                    @endif
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
                                <span class="text-muted"><em>Không có nội dung đánh giá</em></span>
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
                                <button type="button" class="btn btn-sm btn-outline-info btn-view-review" data-review-id="{{ $review->id }}" title="Xem chi tiết">Xem</button>
                                @if($review->status == 'pending')
                                    <button class="btn btn-sm btn-outline-warning edit-review-btn" data-review-id="{{ $review->id }}" title="Chỉnh sửa">Sửa</button>
                                    <button class="btn btn-sm btn-outline-danger delete-review-btn" data-review-id="{{ $review->id }}" title="Xóa">Xóa</button>
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
    </div>
@endif 