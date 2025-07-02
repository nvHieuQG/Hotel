@if ($bookings->count() > 0)
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="thead-light">
                <tr>
                    <th>Mã đặt phòng</th>
                    <th>Phòng</th>
                    <th>Ngày check-in</th>
                    <th>Ngày check-out</th>
                    <th>Giá</th>
                    <th>Trạng thái</th>
                    <th>Đánh giá</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bookings as $booking)
                    <tr>
                        <td>{{ $booking->booking_id }}</td>
                        <td>{{ $booking->room->name }}</td>
                        <td>{{ $booking->check_in_date->format('d/m/Y') }}</td>
                        <td>{{ $booking->check_out_date->format('d/m/Y') }}</td>
                        <td>{{ number_format($booking->price) }}đ</td>
                        <td>
                            <span class="badge badge-{{ $booking->status == 'pending' ? 'warning' : ($booking->status == 'confirmed' ? 'success' : ($booking->status == 'cancelled' ? 'danger' : 'primary')) }}">
                                {{ $booking->status_text }}
                            </span>
                        </td>
                        <td>
                            @if ($booking->hasReview())
                                @php $review = $booking->review @endphp
                                <div class="d-flex align-items-center">
                                    <div class="text-warning mr-2">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star{{ $i <= $review->rating ? '' : '-o' }}"></i>
                                        @endfor
                                    </div>
                                    <span class="badge badge-{{ $review->status == 'approved' ? 'success' : ($review->status == 'rejected' ? 'danger' : 'warning') }}">
                                        {{ $review->status_text }}
                                    </span>
                                </div>
                            @else
                                @if ($booking->status == 'completed')
                                    <a href="{{ route('reviews.create', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-star"></i> Đánh giá
                                    </a>
                                @else
                                    <span class="text-muted">Chưa đánh giá</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info btn-view-booking" data-booking-id="{{ $booking->id }}" title="Xem chi tiết">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        
        <!-- Phân trang -->
        @if($bookings->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $bookings->links() }}
            </div>
        @endif
    </div>
@else
    <div class="text-center py-4">
        <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
        <h5 class="text-muted">Chưa có đặt phòng nào</h5>
        <p class="text-muted">Bạn chưa có lịch sử đặt phòng nào.</p>
        <a href="{{ route('rooms') }}" class="btn btn-primary">
            <i class="fas fa-search"></i> Tìm phòng ngay
        </a>
    </div>
@endif 