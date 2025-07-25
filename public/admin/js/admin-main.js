// Admin Panel Main JavaScript

// CSRF Token cho AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Toggle sidebar
function initSidebarToggle() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const topbar = document.querySelector('.topbar');
    
    if (sidebarToggle && sidebar && mainContent && topbar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('collapsed');
            mainContent.classList.toggle('expanded');
            topbar.classList.toggle('expanded');
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.className = 'fas fa-bars';
                this.title = 'Show Sidebar';
            } else {
                icon.className = 'fas fa-times';
                this.title = 'Hide Sidebar';
            }
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed) {
            sidebar.classList.add('collapsed');
            mainContent.classList.add('expanded');
            topbar.classList.add('expanded');
            const icon = sidebarToggle.querySelector('i');
            icon.className = 'fas fa-bars';
            sidebarToggle.title = 'Show Sidebar';
        }
    }
}

// Đóng sidebar khi click bên ngoài trên mobile
function initSidebarClose() {
    document.addEventListener('click', function(e) {
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        if (window.innerWidth <= 992 && sidebar.classList.contains('show')) {
            if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });
}

// Xử lý window resize
function initWindowResize() {
    window.addEventListener('resize', function() {
        const sidebar = document.querySelector('.sidebar');
        if (window.innerWidth > 992) {
            sidebar.classList.remove('show');
        }
    });
}

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
    if (typeof initSidebarToggle === 'function') initSidebarToggle();
    if (typeof initSidebarClose === 'function') initSidebarClose();
    if (typeof initWindowResize === 'function') initWindowResize();
    if (typeof initDropdownClose === 'function') initDropdownClose();
    setTimeout(function() {
        $('.alert').fadeOut();
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
    $.get('/admin/get-unread-notification-count', function(res) {
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