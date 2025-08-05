// Admin Panel Main JavaScript

// CSRF Token cho AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});





// Đóng dropdown khi click bên ngoài
function initDropdownClose() {
    document.addEventListener('click', function(e) {
        const dropdowns = document.querySelectorAll('.dropdown-menu.show');
        dropdowns.forEach(dropdown => {
            if (!dropdown.contains(e.target) && !dropdown.previousElementSibling.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    });
}

$(document).ready(function() {
    if (typeof initDropdownClose === 'function') initDropdownClose();
    setTimeout(function() {
        $('.alert:not(.alert-warning):not(.alert-success)').fadeOut();
    }, 5000);
});

// Utility functions
window.AdminUtils = {
    showToast: function(message, type = 'info') {
        const toast = $(`
            <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        $('.toast-container').append(toast);
        const bsToast = new bootstrap.Toast(toast[0]);
        bsToast.show();
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
};

// Real-time notification 
function loadNotificationCount() {
    $.get('/admin/api/notifications/count', function(res) {
        if (res.success) {
            $('#notificationBadge').text(res.count);
            $('#sidebarNotificationBadge').text(res.count);
            console.log('Badge cập nhật:', res.count, $('#sidebarNotificationBadge'));
        } else {
            console.log('Không lấy được số lượng thông báo');
        }
    }).fail(function() {
        console.log('Lỗi khi gọi API get-unread-notification-count');
    });
}
loadNotificationCount();
setInterval(loadNotificationCount, 10000); 