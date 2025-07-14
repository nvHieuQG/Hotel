// Admin Panel Main JavaScript

// CSRF Token cho AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Hệ thống thông báo thời gian thực - Phiên bản đơn giản
class NotificationManager {
    constructor() {
        console.log('NotificationManager: Initializing...');
        this.badge = $('#notificationBadge');
        this.list = $('#notificationsList');
        this.markAllBtn = $('#markAllReadBtn');
        this.sidebarBadge = $('#sidebarNotificationBadge');
        
        console.log('NotificationManager: Elements found:', {
            badge: this.badge.length,
            list: this.list.length,
            markAllBtn: this.markAllBtn.length,
            sidebarBadge: this.sidebarBadge.length
        });
        
        if (this.badge.length === 0) {
            console.error('NotificationManager: Badge element not found!');
            return;
        }
        
        this.init();
    }

    init() {
        console.log('NotificationManager: Starting init...');
        this.loadNotifications();
        this.bindEvents();
        this.startPolling();
        console.log('NotificationManager: Init completed');
    }

    bindEvents() {
        console.log('NotificationManager: Binding events...');
        
        if (this.markAllBtn.length > 0) {
            this.markAllBtn.on('click', (e) => {
                e.preventDefault();
                this.markAllAsRead();
            });
        }

        // Đánh dấu đã đọc khi click vào thông báo
        if (this.list.length > 0) {
            this.list.on('click', '.notification-item', (e) => {
                const notificationId = $(e.currentTarget).data('id');
                this.markAsRead(notificationId);
            });
        }
    }

    loadNotifications() {
        console.log('NotificationManager: Loading notifications...');
        
        $.ajax({
            url: '/admin/api/notifications/unread',
            method: 'GET',
            dataType: 'json',
            success: (response) => {
                console.log('NotificationManager: API response:', response);
                if (response.success) {
                    this.updateBadge(response.count);
                    this.renderNotifications(response.notifications);
                }
            },
            error: (xhr, status, error) => {
                console.error('NotificationManager: API failed:', {xhr, status, error});
                this.renderError();
            }
        });
    }

    updateBadge(count) {
        console.log('NotificationManager: Updating badge with count:', count);
        this.badge.text(count);
        this.badge.toggle(count > 0);
        
        // Cập nhật badge trong sidebar
        if (this.sidebarBadge.length > 0) {
            this.sidebarBadge.text(count);
            this.sidebarBadge.toggle(count > 0);
        }
    }

    renderNotifications(notifications) {
        console.log('NotificationManager: Rendering notifications:', notifications);
        
        if (!this.list.length) {
            console.error('NotificationManager: List element not found!');
            return;
        }
        
        if (notifications.length === 0) {
            this.list.html(`
                <div class="dropdown-item text-center small text-gray-500 py-3">
                    <i class="fas fa-check-circle text-success me-2"></i> Không có thông báo mới
                </div>
            `);
            return;
        }

        const html = notifications.map(notification => `
            <div class="dropdown-item notification-item" data-id="${notification.id}">
                <div class="d-flex align-items-start">
                    <div class="icon-circle bg-${notification.color} me-3">
                        <i class="${notification.display_icon} text-white"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <div class="fw-bold small text-truncate" title="${notification.title}">
                                ${notification.title}
                            </div>
                            <div class="d-flex align-items-center gap-1">
                                ${notification.priority === 'urgent' ? '<span class="badge bg-danger small">Khẩn</span>' : ''}
                                ${notification.priority === 'high' ? '<span class="badge bg-warning small">Cao</span>' : ''}
                            </div>
                        </div>
                        <div class="small text-muted text-truncate mb-1" title="${notification.message}">
                            ${notification.message}
                        </div>
                        <div class="small text-gray-500">
                            <i class="fas fa-clock me-1"></i>${notification.time_ago}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        this.list.html(html);
    }

    renderError() {
        if (!this.list.length) return;
        
        this.list.html(`
            <div class="dropdown-item text-center small text-gray-500 py-3">
                <i class="fas fa-exclamation-triangle text-warning me-2"></i> Lỗi tải thông báo
            </div>
        `);
    }

    markAsRead(notificationId) {
        $.post('/admin/api/notifications/mark-read', { notification_id: notificationId })
            .done((response) => {
                if (response.success) {
                    this.updateBadge(response.count);
                    this.loadNotifications();
                }
            });
    }

    markAllAsRead() {
        $.post('/admin/api/notifications/mark-all-read')
            .done((response) => {
                if (response.success) {
                    this.updateBadge(0);
                    this.loadNotifications();
                    this.showToast('Đã đánh dấu tất cả thông báo đã đọc', 'success');
                }
            });
    }

    startPolling() {
        // Cập nhật thông báo mỗi 30 giây
        setInterval(() => {
            this.loadNotifications();
        }, 30000);
    }

    showToast(message, type = 'info') {
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
}

// Animation cho cards
function initCardAnimations() {
    const cards = document.querySelectorAll('.card');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 100 * index);
    });
}

// Hover effect cho buttons
function initButtonEffects() {
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-2px)';
            this.style.boxShadow = '0 4px 8px rgba(0, 0, 0, 0.1)';
        });

        button.addEventListener('mouseleave', function() {
            this.style.transform = '';
            this.style.boxShadow = '';
        });
    });
}

// Toggle sidebar
function initSidebarToggle() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const topbar = document.querySelector('.topbar');
    
    if (sidebarToggle && sidebar && mainContent && topbar) {
        sidebarToggle.addEventListener('click', function() {
            // Toggle sidebar
            sidebar.classList.toggle('collapsed');
            
            // Toggle main content
            mainContent.classList.toggle('expanded');
            
            // Toggle topbar
            topbar.classList.toggle('expanded');
            
            // Update toggle button icon
            const icon = this.querySelector('i');
            if (sidebar.classList.contains('collapsed')) {
                icon.className = 'fas fa-bars';
                this.title = 'Show Sidebar';
            } else {
                icon.className = 'fas fa-times';
                this.title = 'Hide Sidebar';
            }
            
            // Save state to localStorage
            localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
        });
        
        // Restore state from localStorage
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

// Cải thiện dropdown positioning trên mobile
function initDropdownPositioning() {
    const dropdowns = document.querySelectorAll('.dropdown');
    dropdowns.forEach(dropdown => {
        const menu = dropdown.querySelector('.dropdown-menu');
        if (menu) {
            dropdown.addEventListener('show.bs.dropdown', function() {
                const rect = menu.getBoundingClientRect();
                const viewportWidth = window.innerWidth;
                
                if (rect.right > viewportWidth) {
                    menu.style.left = 'auto';
                    menu.style.right = '0';
                }
            });
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

// Khởi tạo tất cả các chức năng khi trang đã load
$(document).ready(function() {
    console.log('Document ready: Starting initialization...');
    
    // Khởi tạo hệ thống thông báo
    try {
        console.log('Document ready: Initializing NotificationManager...');
        window.notificationManager = new NotificationManager();
        console.log('Document ready: NotificationManager initialized successfully');
    } catch (error) {
        console.error('Document ready: Error initializing NotificationManager:', error);
    }
    
    // Khởi tạo các animation và effects
    if (typeof initCardAnimations === 'function') initCardAnimations();
    if (typeof initButtonEffects === 'function') initButtonEffects();
    if (typeof initSidebarToggle === 'function') initSidebarToggle();
    if (typeof initSidebarClose === 'function') initSidebarClose();
    if (typeof initWindowResize === 'function') initWindowResize();
    if (typeof initDropdownPositioning === 'function') initDropdownPositioning();
    if (typeof initDropdownClose === 'function') initDropdownClose();
    
    // Auto-hide alerts sau 5 giây
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
    
    // Smooth scroll cho anchor links
    $('a[href^="#"]').on('click', function(e) {
        e.preventDefault();
        const target = $(this.getAttribute('href'));
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 500);
        }
    });
    
    // Tooltip initialization
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Popover initialization
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Utility functions
window.AdminUtils = {
    // Hiển thị toast notification
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
    },
    
    // Confirm dialog
    confirm: function(message, callback) {
        if (confirm(message)) {
            callback();
        }
    },
    
    // Format currency
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(amount);
    },
    
    // Format date
    formatDate: function(date) {
        return new Intl.DateTimeFormat('vi-VN').format(new Date(date));
    },
    
    // Loading state
    showLoading: function(element) {
        $(element).addClass('loading');
    },
    
    hideLoading: function(element) {
        $(element).removeClass('loading');
    }
}; 