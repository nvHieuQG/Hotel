<div class="promotion-form mb-4">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-gradient-primary text-white">
            <i class="fas fa-gift mr-2"></i> 
            <strong>Mã khuyến mại</strong>
        </div>
        <div class="card-body">
            @if($appliedPromotion)
                <div class="applied-promotion mb-3 p-3 bg-success text-white rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ $appliedPromotion['title'] }}</strong>
                            <div class="small">{{ $appliedPromotion['code'] }}</div>
                            <div class="small">Giảm: {{ number_format($appliedPromotion['discount_amount']) }}đ</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-light" onclick="removePromotion()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            @else
                <form id="promotionForm" class="mb-3">
                    <div class="input-group">
                        <input type="text" 
                               class="form-control" 
                               id="promotionCode" 
                               placeholder="Nhập mã khuyến mại..."
                               maxlength="50">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Áp dụng
                        </button>
                    </div>
                </form>
                
                <div id="promotionAlert" class="alert d-none"></div>
                
                <div class="available-promotions">
                    <h6 class="text-muted mb-2">Khuyến mại có thể áp dụng:</h6>
                    <div id="availablePromotionsList">
                        <div class="text-center text-muted py-2">
                            <i class="fas fa-spinner fa-spin"></i> Đang tải...
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadAvailablePromotions();
    
    // Form submit
    document.getElementById('promotionForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        applyPromotion();
    });
});

function loadAvailablePromotions() {
    const bookingId = '{{ $booking->id ?? "" }}';
    if (!bookingId) return;
    
    fetch(`/bookings/${bookingId}/promotions/available`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAvailablePromotions(data.promotions);
            }
        })
        .catch(error => {
            console.error('Error loading promotions:', error);
        });
}

function displayAvailablePromotions(promotions) {
    const container = document.getElementById('availablePromotionsList');
    
    if (promotions.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-2">Không có khuyến mại nào khả dụng</div>';
        return;
    }
    
    let html = '';
    promotions.forEach(promo => {
        html += `
            <div class="promotion-item mb-2 p-2 border rounded bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="flex-grow-1">
                        <div class="fw-bold text-primary">{{ $promo['title'] }}</div>
                        <div class="small text-muted">{{ $promo['description'] }}</div>
                        <div class="small">
                            <span class="badge bg-success">{{ $promo['discount_text'] }}</span>
                            <span class="text-muted">Tối thiểu: {{ number_format($promo['minimum_amount']) }}đ</span>
                        </div>
                    </div>
                    <div class="text-end ms-2">
                        <div class="fw-bold text-success">-{{ number_format($promo['discount_amount']) }}đ</div>
                        <div class="small text-muted">Còn: {{ number_format($promo['final_price']) }}đ</div>
                    </div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function applyPromotion() {
    const code = document.getElementById('promotionCode').value.trim();
    if (!code) {
        showAlert('Vui lòng nhập mã khuyến mại', 'warning');
        return;
    }
    
    const bookingId = '{{ $booking->id ?? "" }}';
    if (!bookingId) return;
    
    fetch(`/bookings/${bookingId}/promotions/apply`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ promotion_code: code })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            document.getElementById('promotionCode').value = '';
            // Reload page để cập nhật thông tin
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error applying promotion:', error);
        showAlert('Có lỗi xảy ra, vui lòng thử lại', 'danger');
    });
}

function removePromotion() {
    const bookingId = '{{ $booking->id ?? "" }}';
    if (!bookingId) return;
    
    if (!confirm('Bạn có chắc muốn gỡ bỏ mã khuyến mại này?')) return;
    
    fetch(`/bookings/${bookingId}/promotions/remove`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            setTimeout(() => location.reload(), 1500);
        } else {
            showAlert(data.message, 'danger');
        }
    })
    .catch(error => {
        console.error('Error removing promotion:', error);
        showAlert('Có lỗi xảy ra, vui lòng thử lại', 'danger');
    });
}

function showAlert(message, type) {
    const alertDiv = document.getElementById('promotionAlert');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertDiv.classList.remove('d-none');
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        alertDiv.classList.add('d-none');
    }, 5000);
}
</script>

<style>
.promotion-form .card-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
}

.promotion-item {
    transition: all 0.3s ease;
    border: 1px solid #e9ecef;
}

.promotion-item:hover {
    border-color: #007bff;
    box-shadow: 0 2px 8px rgba(0, 123, 255, 0.15);
    transform: translateY(-1px);
}

.applied-promotion {
    background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%) !important;
}

.badge.bg-success {
    background-color: #28a745 !important;
}
</style>
