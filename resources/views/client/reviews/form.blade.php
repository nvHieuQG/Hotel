<form id="reviewFormPopup" method="POST" action="{{ route('room-type-reviews.store-ajax') }}">
    @csrf
    @if(isset($roomType) && $roomType)
        <input type="hidden" name="room_type_id" value="{{ $roomType->id }}">
        <div class="alert alert-info mb-3">
            <strong>Đánh giá cho loại phòng:</strong> {{ $roomType->name }}
            @if(isset($booking) && $booking)
                <br><strong>Mã booking:</strong> {{ $booking->booking_id }}
            @endif
        </div>
    @else
        <div class="alert alert-danger mb-3">
            <strong>Lỗi:</strong> Không tìm thấy thông tin loại phòng.
        </div>
    @endif
    @if(isset($booking) && $booking)
        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
    @endif
    <div class="form-group">
        <label for="rating">Đánh giá tổng thể (sao): <span class="text-danger">*</span></label>
        <select name="rating" id="rating" class="form-control" required>
            <option value="">Chọn số sao</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}">{{ $i }} sao</option>
            @endfor
        </select>
    </div>
    <div class="form-group">
        <label for="comment">Nội dung đánh giá: <span class="text-danger">*</span></label>
        <textarea name="comment" id="comment" class="form-control" rows="3" required></textarea>
    </div>
    <div class="form-group">
        <label for="cleanliness_rating">Sạch sẽ: <span class="text-danger">*</span></label>
        <select name="cleanliness_rating" id="cleanliness_rating" class="form-control" required>
            <option value="">Chọn số sao</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}">{{ $i }} sao</option>
            @endfor
        </select>
    </div>
    <div class="form-group">
        <label for="comfort_rating">Thoải mái: <span class="text-danger">*</span></label>
        <select name="comfort_rating" id="comfort_rating" class="form-control" required>
            <option value="">Chọn số sao</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}">{{ $i }} sao</option>
            @endfor
        </select>
    </div>
    <div class="form-group">
        <label for="location_rating">Vị trí: <span class="text-danger">*</span></label>
        <select name="location_rating" id="location_rating" class="form-control" required>
            <option value="">Chọn số sao</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}">{{ $i }} sao</option>
            @endfor
        </select>
    </div>
    <div class="form-group">
        <label for="facilities_rating">Tiện nghi: <span class="text-danger">*</span></label>
        <select name="facilities_rating" id="facilities_rating" class="form-control" required>
            <option value="">Chọn số sao</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}">{{ $i }} sao</option>
            @endfor
        </select>
    </div>
    <div class="form-group">
        <label for="value_rating">Giá trị: <span class="text-danger">*</span></label>
        <select name="value_rating" id="value_rating" class="form-control" required>
            <option value="">Chọn số sao</option>
            @for ($i = 5; $i >= 1; $i--)
                <option value="{{ $i }}">{{ $i }} sao</option>
            @endfor
        </select>
    </div>
    <div class="form-group">
        <label><input type="checkbox" name="is_anonymous" value="1"> Ẩn danh</label>
    </div>
    <button type="submit" class="btn btn-primary" {{ !isset($roomType) || !$roomType ? 'disabled' : '' }}>Gửi đánh giá</button>
</form>
<script>
$(function() {
    $('#reviewFormPopup').off('submit').on('submit', function(e) {
        e.preventDefault();
        
        // Kiểm tra xem có room_type_id không
        var roomTypeId = $('input[name="room_type_id"]').val();
        if (!roomTypeId) {
            showToast('Không tìm thấy thông tin loại phòng. Vui lòng thử lại.', 'danger');
            return;
        }
        
        var form = $(this);
        var btn = form.find('button[type=submit]');
        btn.prop('disabled', true).text('Đang gửi...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            success: function(res) {
                btn.prop('disabled', false).text('Gửi đánh giá');
                $('#reviewFormModal').modal('hide');
                if (typeof loadReviewsData === 'function') loadReviewsData();
                if (typeof loadBookingsData === 'function') loadBookingsData();
                showToast(res.message || 'Đánh giá đã được gửi!', 'success');
            },
            error: function(xhr) {
                btn.prop('disabled', false).text('Gửi đánh giá');
                var msg = 'Có lỗi xảy ra khi gửi đánh giá.';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                showToast(msg, 'danger');
            }
        });
    });
});
</script> 