/**
 * Support Realtime System
 * Handles real-time support messages using Server-Sent Events
 */

class SupportRealtimeSystem {
    constructor() {
        this.eventSource = null;
        this.reconnectAttempts = 0;
        this.maxReconnectAttempts = 5;
        this.isConnected = false;

        this.init();
    }

    init() {
        console.log('🔧 Initializing Support Realtime System...');
        this.connectToStream();
        this.setupPeriodicSync();
    }

    connectToStream() {
        console.log('🔌 Attempting to connect to support stream...');

        if (this.eventSource) {
            this.eventSource.close();
        }

        try {
            this.eventSource = new EventSource('/admin/support/stream');
            console.log('✅ Support EventSource created successfully');

            this.eventSource.onopen = (event) => {
                console.log('🟢 Connected to support stream');
                this.reconnectAttempts = 0;
                this.isConnected = true;
                this.refreshUnreadCount();
            };

            this.eventSource.onmessage = (event) => {
                try {
                    const data = JSON.parse(event.data);
                    console.log('📨 Support SSE message received:', data);

                    if (data.type === 'new_support_message') {
                        console.log('💬 Handling new support message:', data);
                        this.handleNewMessage(data);
                    } else if (data.type === 'heartbeat') {
                        console.log('💓 Support heartbeat received:', data.timestamp);
                    } else if (data.type === 'connected') {
                        console.log('🔗 Connected to support stream:', data.message);
                    }
                } catch (error) {
                    console.error('❌ Error parsing support data:', error);
                }
            };

            this.eventSource.onerror = (event) => {
                console.error('❌ Support stream error:', event);
                this.isConnected = false;
                this.eventSource.close();

                // Attempt to reconnect
                if (this.reconnectAttempts < this.maxReconnectAttempts) {
                    this.reconnectAttempts++;
                    console.log(`🔄 Attempting to reconnect support stream (${this.reconnectAttempts}/${this.maxReconnectAttempts})...`);
                    setTimeout(() => {
                        this.connectToStream();
                    }, 5000 * this.reconnectAttempts);
                } else {
                    console.error('🚫 Max support reconnection attempts reached');
                }
            };
        } catch (error) {
            console.error('❌ Failed to create Support EventSource:', error);
        }
    }

    refreshUnreadCount() {
        console.log('🔄 Refreshing support unread count...');

        $.get('/admin/support/unread-count', (response) => {
            console.log('📊 Support unread count response:', response);
            if (response && response.success) {
                const count = parseInt(response.count) || 0;
                console.log('📈 Support unread count:', count);
                this.updateBadges(count);
            }
        }).fail((xhr, status, error) => {
            console.error('❌ Failed to fetch support unread count:', {xhr, status, error});
        });
    }

    updateBadges(count) {
        // Update support badge in sidebar
        const $supportLink = $('a[href="/admin/support"]');
        let $supportBadge = $supportLink.find('.badge');

        if ($supportBadge.length === 0 && count > 0) {
            $supportLink.append(`<span class="badge bg-danger ms-2">${count}</span>`);
        } else if ($supportBadge.length > 0) {
            $supportBadge.text(count);
            if (count === 0) {
                $supportBadge.remove();
            }
        }
    }

    handleNewMessage(data) {
        console.log('💬 Processing new support message notification');

        // Update support badge count
        this.refreshUnreadCount();

        // Show support notification toast
        this.showToast(data);

        // Update support dropdown if it's open
        if ($('#messagesDropdown').hasClass('show')) {
            this.updateDropdown();
        }
    }

    showToast(data) {
        console.log('🍞 Showing support toast notification:', data);

        let toastHtml = '';

        if (data.role !== 'admin') {
            toastHtml = `
                <div class="toast align-items-center text-white bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="5000">
                    <div class="d-flex">
                        <div class="toast-body">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-headset me-2"></i>
                                <div>
                                    <strong>Tin nhắn hỗ trợ mới từ ${data.user_name}</strong>
                                    <div class="small">${data.subject || 'Không có chủ đề'}</div>
                                    <div class="small">${data.message}</div>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>`;
        }


        const $toast = $(toastHtml);
        $('.toast-container').append($toast);

        // Make toast clickable to go to conversation
        $toast.css('cursor', 'pointer').on('click', function(e) {
            if (!$(e.target).hasClass('btn-close')) {
                window.location.href = data.url;
            }
        });

        const toast = new bootstrap.Toast($toast[0]);
        toast.show();

        // Remove toast element after it's hidden
        $toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }

    updateDropdown() {
        console.log('🔄 Updating support dropdown...');

        $.get('/admin/support/recent-conversations', (response) => {
            console.log('💬 Support conversations response:', response);
            if (response.success) {
                let html = '';
                if (response.conversations.length > 0) {
                    response.conversations.forEach((conversation) => {
                        const badgeHtml = conversation.unread_count > 0 ?
                            `<span class="badge bg-danger ms-1" style="font-size: 0.6rem;">${conversation.unread_count}</span>` :
                            '';

                        html += `
                        <div class="dropdown-item notification-item d-flex align-items-start justify-content-between gap-2">
                            <a href="${conversation.url}" class="flex-grow-1 text-decoration-none text-dark">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="icon-circle ${conversation.unread_count > 0 ? 'bg-danger' : 'bg-success'}">
                                        <i class="fas fa-headset text-white"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold small text-truncate" title="${conversation.user_name}">
                                            ${conversation.user_name}
                                            ${badgeHtml}
                                        </div>
                                        <div class="small text-muted text-truncate mb-1" title="${conversation.subject}">
                                            ${conversation.subject || 'Không có chủ đề'}
                                        </div>
                                        <div class="small text-muted text-truncate mb-1" title="${conversation.message}">
                                            ${conversation.message}
                                        </div>
                                        <div class="small text-gray-500">
                                            <i class="fas fa-clock me-1"></i>${conversation.created_at}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    `;
                    });
                } else {
                    html = `
                    <div class="dropdown-item text-center small text-gray-500 py-3">
                        <i class="fas fa-check-circle text-success me-2"></i> Không có tin nhắn hỗ trợ mới
                    </div>
                `;
                }
                $('#messagesList').html(html);
            }
        }).fail((xhr, status, error) => {
            console.error('❌ Failed to fetch support conversations:', {xhr, status, error});
        });
    }

    setupPeriodicSync() {
        // Sync every 60 seconds
        setInterval(() => {
            this.refreshUnreadCount();
        }, 60000);
    }

    testSystem() {
        console.log('🧪 Testing support system...');
        $('#testStatus').html('Testing support system...');

        // Test unread count
        this.refreshUnreadCount();

        // Test conversations
        this.updateDropdown();

        // Test SSE connection
        if (this.isConnected) {
            $('#testStatus').html('✅ Support SSE Connected');
        } else {
            $('#testStatus').html('❌ Support SSE Not Connected');
        }
    }

    createTestMessage() {
        console.log('📝 Creating test support message...');
        $('#testStatus').html('Creating test support message...');

        $.post('/admin/support/test-message', {
            _token: $('meta[name="csrf-token"]').attr('content')
        }, (response) => {
            console.log('📝 Test support message response:', response);
            if (response.success) {
                $('#testStatus').html('✅ Test support message created');
                // Refresh the count
                setTimeout(() => {
                    this.refreshUnreadCount();
                }, 1000);
            } else {
                $('#testStatus').html('❌ Failed to create test support message');
            }
        }).fail((xhr, status, error) => {
            console.error('❌ Failed to create test support message:', {xhr, status, error});
            $('#testStatus').html('❌ Error: ' + error);
        });
    }

    destroy() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }
}

// Initialize when document is ready
$(document).ready(function() {
    window.supportRealtime = new SupportRealtimeSystem();

    // Add test buttons
    $('body').append(`
        <div style="position: fixed; top: 10px; right: 10px; z-index: 9999; background: #333; color: white; padding: 10px; border-radius: 5px; font-size: 12px;">
            <div style="margin-bottom: 5px;">
                <button onclick="window.supportRealtime.testSystem()" style="background: #28a745; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer;">Test Support</button>
                <button onclick="window.supportRealtime.createTestMessage()" style="background: #ffc107; color: white; border: none; padding: 5px 10px; border-radius: 3px; cursor: pointer; margin-left: 5px;">Create Support</button>
            </div>
            <div id="testStatus" style="margin-top: 5px;"></div>
        </div>
    `);
});

// Clean up on page unload
$(window).on('beforeunload', function() {
    if (window.supportRealtime) {
        window.supportRealtime.destroy();
    }
});
