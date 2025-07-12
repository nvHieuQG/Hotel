/**
 * Full HD (1080x1920) Optimization JavaScript
 * Tối ưu trải nghiệm người dùng cho màn hình lớn
 */

(function() {
    'use strict';

    // Kiểm tra kích thước màn hình
    const isFullHD = window.innerWidth >= 1920;
    const isUltraWide = window.innerWidth >= 2560;

    // Tối ưu cho màn hình Full HD
    if (isFullHD) {
        initializeFullHDOptimizations();
    }

    // Tối ưu cho màn hình Ultra Wide
    if (isUltraWide) {
        initializeUltraWideOptimizations();
    }

    // Lắng nghe sự kiện resize
    window.addEventListener('resize', debounce(handleResize, 250));

    /**
     * Khởi tạo tối ưu cho màn hình Full HD
     */
    function initializeFullHDOptimizations() {
        console.log('Initializing Full HD optimizations...');

        // Tối ưu sidebar
        optimizeSidebar();

        // Tối ưu tables
        optimizeTables();

        // Tối ưu cards
        optimizeCards();

        // Tối ưu forms
        optimizeForms();

        // Tối ưu modals
        optimizeModals();

        // Tối ưu navigation
        optimizeNavigation();

        // Tối ưu pagination
        optimizePagination();

        // Tối ưu tooltips
        optimizeTooltips();

        // Tối ưu loading states
        optimizeLoadingStates();

        // Tối ưu animations
        optimizeAnimations();
    }

    /**
     * Khởi tạo tối ưu cho màn hình Ultra Wide
     */
    function initializeUltraWideOptimizations() {
        console.log('Initializing Ultra Wide optimizations...');

        // Tăng kích thước container
        const containers = document.querySelectorAll('.container-fluid');
        containers.forEach(container => {
            container.style.maxWidth = '2200px';
        });

        // Tối ưu grid system
        optimizeGridSystem();

        // Tối ưu typography
        optimizeTypography();
    }

    /**
     * Tối ưu sidebar
     */
    function optimizeSidebar() {
        const sidebar = document.querySelector('.sidebar');
        if (sidebar) {
            sidebar.style.width = '320px';
            
            // Tối ưu navigation items
            const navItems = sidebar.querySelectorAll('.nav-link');
            navItems.forEach(item => {
                item.style.padding = '1rem 1.5rem';
                item.style.fontSize = '0.95rem';
            });
        }
    }

    /**
     * Tối ưu tables
     */
    function optimizeTables() {
        const tables = document.querySelectorAll('.table');
        tables.forEach(table => {
            // Tối ưu header
            const headers = table.querySelectorAll('th');
            headers.forEach(header => {
                header.style.fontSize = '0.9rem';
                header.style.padding = '1rem 1.5rem';
                header.style.fontWeight = '600';
            });

            // Tối ưu cells
            const cells = table.querySelectorAll('td');
            cells.forEach(cell => {
                cell.style.fontSize = '0.95rem';
                cell.style.padding = '1rem 1.5rem';
            });

            // Thêm hover effects
            const rows = table.querySelectorAll('tbody tr');
            rows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.001)';
                    this.style.transition = 'all 0.2s ease';
                });

                row.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1)';
                });
            });
        });
    }

    /**
     * Tối ưu cards
     */
    function optimizeCards() {
        const cards = document.querySelectorAll('.card');
        cards.forEach(card => {
            // Tối ưu header
            const header = card.querySelector('.card-header');
            if (header) {
                header.style.padding = '1.5rem 2rem';
                
                const title = header.querySelector('h6');
                if (title) {
                    title.style.fontSize = '1.3rem';
                }
            }

            // Tối ưu body
            const body = card.querySelector('.card-body');
            if (body) {
                body.style.padding = '2rem 2.5rem';
            }

            // Thêm hover effects
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-3px)';
                this.style.boxShadow = '0 8px 30px rgba(0, 0, 0, 0.12)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.08)';
            });
        });
    }

    /**
     * Tối ưu forms
     */
    function optimizeForms() {
        const inputs = document.querySelectorAll('.form-control, .form-select');
        inputs.forEach(input => {
            input.style.padding = '0.75rem 1rem';
            input.style.fontSize = '0.95rem';
            input.style.borderRadius = '8px';

            // Thêm focus effects
            input.addEventListener('focus', function() {
                this.style.transform = 'translateY(-1px)';
                this.style.boxShadow = '0 0 0 0.25rem rgba(193, 155, 118, 0.15)';
            });

            input.addEventListener('blur', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });

        // Tối ưu labels
        const labels = document.querySelectorAll('.form-label');
        labels.forEach(label => {
            label.style.fontSize = '0.95rem';
            label.style.fontWeight = '600';
            label.style.marginBottom = '0.75rem';
        });
    }

    /**
     * Tối ưu modals
     */
    function optimizeModals() {
        const modals = document.querySelectorAll('.modal-dialog');
        modals.forEach(modal => {
            modal.style.maxWidth = '800px';
        });

        const modalHeaders = document.querySelectorAll('.modal-header');
        modalHeaders.forEach(header => {
            header.style.padding = '1.5rem 2rem';
        });

        const modalBodies = document.querySelectorAll('.modal-body');
        modalBodies.forEach(body => {
            body.style.padding = '2rem';
        });

        const modalFooters = document.querySelectorAll('.modal-footer');
        modalFooters.forEach(footer => {
            footer.style.padding = '1.5rem 2rem';
        });
    }

    /**
     * Tối ưu navigation
     */
    function optimizeNavigation() {
        const navLinks = document.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.style.padding = '1rem 1.5rem';
            link.style.fontSize = '0.95rem';

            // Thêm hover effects
            link.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
            });

            link.addEventListener('mouseleave', function() {
                this.style.transform = 'translateX(0)';
            });
        });
    }

    /**
     * Tối ưu pagination
     */
    function optimizePagination() {
        const pagination = document.querySelectorAll('.pagination');
        pagination.forEach(pag => {
            pag.style.marginTop = '2rem';
            pag.style.justifyContent = 'center';
        });

        const pageLinks = document.querySelectorAll('.pagination .page-link');
        pageLinks.forEach(link => {
            link.style.padding = '0.75rem 1rem';
            link.style.fontSize = '0.95rem';
            link.style.borderRadius = '6px';
            link.style.margin = '0 0.2rem';

            // Thêm hover effects
            link.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#c19b76';
                this.style.borderColor = '#c19b76';
                this.style.color = 'white';
                this.style.transform = 'translateY(-1px)';
            });

            link.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
                this.style.borderColor = '';
                this.style.color = '';
                this.style.transform = 'translateY(0)';
            });
        });
    }

    /**
     * Tối ưu tooltips
     */
    function optimizeTooltips() {
        // Khởi tạo tooltips với tùy chọn tối ưu
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                fontSize: '0.9rem',
                delay: { show: 300, hide: 100 }
            });
        });
    }

    /**
     * Tối ưu loading states
     */
    function optimizeLoadingStates() {
        // Thêm loading skeleton cho tables
        const tables = document.querySelectorAll('.table');
        tables.forEach(table => {
            if (table.querySelector('tbody tr').length === 0) {
                const tbody = table.querySelector('tbody');
                const skeletonRow = document.createElement('tr');
                skeletonRow.innerHTML = `
                    <td colspan="100%">
                        <div class="loading-skeleton" style="height: 2rem; border-radius: 8px;"></div>
                    </td>
                `;
                tbody.appendChild(skeletonRow);
            }
        });
    }

    /**
     * Tối ưu animations
     */
    function optimizeAnimations() {
        // Thêm smooth scrolling
        document.documentElement.style.scrollBehavior = 'smooth';

        // Tối ưu transition cho buttons
        const buttons = document.querySelectorAll('.btn');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.15)';
            });

            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    }

    /**
     * Tối ưu grid system cho Ultra Wide
     */
    function optimizeGridSystem() {
        const rows = document.querySelectorAll('.row');
        rows.forEach(row => {
            row.style.marginLeft = '-1rem';
            row.style.marginRight = '-1rem';
        });

        const cols = document.querySelectorAll('.col, [class*="col-"]');
        cols.forEach(col => {
            col.style.paddingLeft = '1rem';
            col.style.paddingRight = '1rem';
        });
    }

    /**
     * Tối ưu typography cho Ultra Wide
     */
    function optimizeTypography() {
        const headings = document.querySelectorAll('h1, h2, h3, h4, h5, h6');
        headings.forEach(heading => {
            const currentSize = parseFloat(window.getComputedStyle(heading).fontSize);
            heading.style.fontSize = (currentSize * 1.1) + 'px';
        });

        const paragraphs = document.querySelectorAll('p');
        paragraphs.forEach(p => {
            p.style.fontSize = '1rem';
            p.style.lineHeight = '1.6';
        });
    }

    /**
     * Xử lý resize window
     */
    function handleResize() {
        const newWidth = window.innerWidth;
        
        if (newWidth >= 2560 && !isUltraWide) {
            location.reload(); // Reload để áp dụng Ultra Wide optimizations
        } else if (newWidth >= 1920 && newWidth < 2560 && !isFullHD) {
            location.reload(); // Reload để áp dụng Full HD optimizations
        }
    }

    /**
     * Debounce function để tối ưu performance
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Thêm CSS variables cho responsive
     */
    function addCSSVariables() {
        const root = document.documentElement;
        
        if (isFullHD) {
            root.style.setProperty('--sidebar-width', '320px');
            root.style.setProperty('--main-padding', '2rem 2.5rem');
            root.style.setProperty('--card-padding', '2rem 2.5rem');
            root.style.setProperty('--table-padding', '1rem 1.5rem');
            root.style.setProperty('--font-size-base', '0.95rem');
        }

        if (isUltraWide) {
            root.style.setProperty('--sidebar-width', '350px');
            root.style.setProperty('--main-padding', '2.5rem 3rem');
            root.style.setProperty('--card-padding', '2.5rem 3rem');
            root.style.setProperty('--table-padding', '1.25rem 2rem');
            root.style.setProperty('--font-size-base', '1rem');
        }
    }

    // Khởi tạo CSS variables
    addCSSVariables();

    // Export functions cho sử dụng global
    window.FullHDOptimizer = {
        isFullHD,
        isUltraWide,
        optimizeSidebar,
        optimizeTables,
        optimizeCards,
        optimizeForms,
        optimizeModals,
        optimizeNavigation,
        optimizePagination,
        optimizeTooltips,
        optimizeLoadingStates,
        optimizeAnimations
    };

})(); 