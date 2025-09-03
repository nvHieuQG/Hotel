const chatRealtime = {
    init(conversationId) {
        this.conversationId = conversationId;
        this.isRealtimeEnabled = true;
        this.lastMessageId = this.getLastMessageIdFromDom();
        this.basePollInterval = 7000; // 7s khi tab hoạt động
        this.backgroundPollInterval = 15000; // 15s khi tab nền
        this.pollInterval = this.basePollInterval;
        this.isRequestInFlight = false;
        this.setupRealtime();
    },

    getLastMessageIdFromDom() {
        const last = document.querySelector('.chat-messages .message:last-child');
        const id = last ? parseInt(last.getAttribute('data-message-id') || '0', 10) : 0;
        return Number.isFinite(id) ? id : 0;
    },

    setupRealtime() {
        // Bắt đầu polling
        this.checkNewMessages();

        // Thiết lập audio cho notification
        this.messageSound = new Audio('/admin/sounds/message.mp3');

        // Điều chỉnh chu kỳ theo trạng thái tab (Page Visibility API)
        document.addEventListener('visibilitychange', () => {
            this.pollInterval = document.hidden
                ? this.backgroundPollInterval
                : this.basePollInterval;
        });
    },

    checkNewMessages() {
        if (!this.isRealtimeEnabled) return;

        // Tránh gọi chồng chéo khi request trước chưa xong
        if (this.isRequestInFlight) {
            setTimeout(() => this.checkNewMessages(), this.pollInterval);
            return;
        }

        this.isRequestInFlight = true;

        fetch(
            `/admin/support/conversation/${this.conversationId}/messages?last_id=${this.lastMessageId}`
        )
            .then((res) => res.json())
            .then((data) => {
                if (data.success && data.messages.length > 0) {
                    this.handleNewMessages(data.messages);
                }
            })
            .catch((err) => console.error('Error fetching messages:', err))
            .finally(() => {
                this.isRequestInFlight = false;
                // Tiếp tục polling
                setTimeout(() => this.checkNewMessages(), this.pollInterval);
            });
    },

    handleNewMessages(messages) {
        messages.forEach((msg) => {
            if (msg.id > this.lastMessageId) {
                // Cập nhật UI với tin nhắn mới
                this.addMessageToUI(msg);

                // Cập nhật last message id
                this.lastMessageId = msg.id;

                // Phát âm thanh + thông báo nếu tin nhắn từ user (không phải admin)
                if (msg.sender_type === 'user') {
                    try { this.messageSound.play(); } catch (e) {}
                    this.showNotification(msg);
                }
            }
        });
        this.scrollToBottom();
    },

    addMessageToUI(message) {
        const messageHtml = `
            <div class="message ${
                message.sender_type === 'admin' ? 'sent' : 'received'
            }" data-message-id="${message.id}">
                <div class="message-bubble">
                    ${message.message || ''}
                </div>
                <div class="message-meta">
                    <span class="message-time">${message.created_at}</span>
                </div>
            </div>
        `;

        document
            .querySelector('.chat-messages')
            .insertAdjacentHTML('beforeend', messageHtml);
    },

    scrollToBottom() {
        const chatMessages = document.querySelector('.chat-messages');
        if (chatMessages) chatMessages.scrollTop = chatMessages.scrollHeight;
    },

    showNotification(message) {
        if (!('Notification' in window)) return;
        if (Notification.permission === 'granted' && message.sender_type === 'user') {
            new Notification('Tin nhắn mới', {
                body: message.message || 'Bạn có tin nhắn mới',
                icon: '/admin/images/notification-icon.png',
            });
        }
    },

    stop() {
        this.isRealtimeEnabled = false;
    },
};

// Thêm vào admin-master.blade.php
