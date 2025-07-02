@if($reviews->count() > 0)
    <div class="reviews-list">
        @foreach($reviews as $review)
            <div class="review-item border-bottom pb-3 mb-3">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center">
                        <div class="stars mr-2">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= $review->rating)
                                    <span class="icon-star text-warning"></span>
                                @else
                                    <span class="icon-star-o text-muted"></span>
                                @endif
                            @endfor
                        </div>
                        <span class="text-muted">{{ $review->rating }}/5</span>
                    </div>
                    <small class="text-muted">{{ $review->created_at->format('d/m/Y H:i') }}</small>
                </div>
                
                <div class="review-content">
                    @if($review->is_anonymous)
                        <p class="mb-1"><strong>Khách hàng ẩn danh</strong></p>
                    @else
                        <p class="mb-1"><strong>{{ $review->user->name }}</strong></p>
                    @endif
                    
                    @if($review->comment)
                        <p class="mb-0">{{ $review->comment }}</p>
                    @else
                        <p class="mb-0 text-muted"><em>Không có bình luận</em></p>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
    
    <!-- Phân trang -->
    @if($reviews->hasPages())
        <div class="d-flex justify-content-center">
            {{ $reviews->links() }}
        </div>
    @endif
@else
    <div class="text-center py-4">
        <i class="icon-star-o text-muted" style="font-size: 3rem;"></i>
        <p class="text-muted mt-2">Chưa có đánh giá nào cho phòng này</p>
    </div>
@endif 