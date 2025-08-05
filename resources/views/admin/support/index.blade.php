@extends('admin.layouts.admin-master')

@section('title', 'Quản lý hỗ trợ khách hàng')

@section('styles')
<style>
.support-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.support-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
}

.support-header h2 {
    color: #2c3e50;
    margin: 0;
    font-size: 2rem;
    font-weight: 600;
}

.support-stats {
    display: flex;
    gap: 20px;
}

.stat-item {
    text-align: center;
    padding: 15px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    color: white;
    min-width: 100px;
}

.stat-number {
    display: block;
    font-size: 2rem;
    font-weight: bold;
    margin-bottom: 5px;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.support-filters {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    gap: 20px;
}

.filter-tabs {
    display: flex;
    gap: 10px;
}

.filter-tab {
    padding: 10px 20px;
    border: none;
    border-radius: 25px;
    background: #f8f9fa;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.filter-tab:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.filter-tab.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

.search-box {
    position: relative;
    flex: 1;
    max-width: 400px;
}

.search-box input {
    width: 100%;
    padding: 12px 45px 12px 20px;
    border: 2px solid #e9ecef;
    border-radius: 25px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.search-box input:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

.search-box i {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
}

.tickets-list {
    background: white;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

.ticket-item {
    display: flex;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #f1f3f4;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
}

.ticket-item:hover {
    background: #f8f9fa;
    transform: translateX(5px);
}

.ticket-item:last-child {
    border-bottom: none;
}

.ticket-item.new-message {
    animation: newMessagePulse 0.6s ease-in-out;
    border-left: 4px solid #28a745;
    background: #f8fff9;
}

@keyframes newMessagePulse {
    0% { transform: translateX(-10px); opacity: 0.7; }
    50% { transform: translateX(5px); opacity: 1; }
    100% { transform: translateX(0); opacity: 1; }
}

.ticket-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
    margin-right: 15px;
    flex-shrink: 0;
}

.ticket-content {
    flex: 1;
    min-width: 0;
}

.ticket-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.ticket-customer {
    font-weight: 600;
    color: #2c3e50;
    font-size: 1.1rem;
    margin-bottom: 4px;
}

.ticket-time {
    font-size: 0.85rem;
    color: #6c757d;
}

.ticket-subject {
    font-weight: 500;
    color: #495057;
    margin-bottom: 6px;
    font-size: 1rem;
}

.ticket-preview {
    color: #6c757d;
    font-size: 0.9rem;
    line-height: 1.4;
    margin-bottom: 8px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.ticket-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.85rem;
}

.ticket-status {
    padding: 4px 12px;
    border-radius: 15px;
    font-weight: 500;
    font-size: 0.8rem;
}

.ticket-status.open {
    background: #fff3cd;
    color: #856404;
}

.ticket-status.closed {
    background: #d4edda;
    color: #155724;
}

.message-count {
    color: #6c757d;
    font-weight: 500;
}

.unread-badge {
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: bold;
    position: absolute;
    top: 15px;
    right: 15px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: #6c757d;
}

.empty-state i {
    font-size: 4rem;
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    margin-bottom: 10px;
    color: #495057;
}

.empty-state p {
    font-size: 1.1rem;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 768px) {
    .support-header {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }

    .support-stats {
        justify-content: center;
        gap: 10px;
    }

    .stat-item {
        padding: 8px 12px;
    }

    .filter-tabs {
        justify-content: center;
    }

    .ticket-item {
        padding: 12px 15px;
    }

    .ticket-preview {
        max-width: 200px;
    }
}
</style>
@endsection

@section('content')
<div class="support-container">
    <!-- Header với thống kê -->
    <div class="support-header">
        <h2><i class="fas fa-headset me-2"></i>Quản lý hỗ trợ khách hàng</h2>
        <div class="support-stats">
            <div class="stat-item">
                <span class="stat-number" id="unreadCount">{{ $conversations->where('unread_count', '>', 0)->count() }}</span>
                <span class="stat-label">Chưa đọc</span>
            </div>
            <div class="stat-item">
                <span class="stat-number" id="totalCount">{{ $conversations->count() }}</span>
                <span class="stat-label">Tổng cộng</span>
            </div>
        </div>
    </div>

    <!-- Filters và Search -->
    <div class="support-filters">
        <div class="filter-tabs">
            <button class="filter-tab active" data-filter="all">Tất cả (<span id="allCount">{{ $conversations->count() }}</span>)</button>
            <button class="filter-tab" data-filter="unread">Chưa đọc (<span id="unreadFilterCount">{{ $conversations->where('unread_count', '>', 0)->count() }}</span>)</button>
        </div>

        <div class="search-box">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="Tìm kiếm khách hàng hoặc chủ đề...">
        </div>
    </div>

    <!-- Danh sách conversations -->
    <div class="tickets-list" id="conversationsList">
        @if($conversations->count() > 0)
            @foreach($conversations as $conversation)
                <div class="ticket-item" data-conversation-id="{{ $conversation['conversation_id'] }}" data-unread="{{ $conversation['unread_count'] }}">
                    <div class="ticket-avatar">
                        {{ substr($conversation['user']->name ?? 'U', 0, 1) }}
                    </div>

                    <div class="ticket-content">
                        <div class="ticket-header">
                            <div>
                                <div class="ticket-customer">{{ $conversation['user']->name ?? 'Khách hàng' }}</div>
                                <div class="ticket-time">{{ \Carbon\Carbon::parse($conversation['created_at'])->format('d/m/Y H:i') }}</div>
                            </div>
                            @if($conversation['unread_count'] > 0)
                                <div class="unread-badge">{{ $conversation['unread_count'] }}</div>
                            @endif
                        </div>

                        <div class="ticket-subject">{{ $conversation['subject'] ?? 'Hỗ trợ nhanh' }}</div>
                        <div class="ticket-preview">
                            {{ $conversation['last_message'] ?? 'Chưa có tin nhắn' }}
                        </div>

                        <div class="ticket-meta">
                            <span class="message-count">{{ $conversation['unread_count'] > 0 ? $conversation['unread_count'] . ' tin nhắn mới' : 'Đã trả lời' }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Chưa có cuộc trò chuyện nào</h3>
                <p>Khi khách hàng gửi tin nhắn hỗ trợ, chúng sẽ xuất hiện ở đây.</p>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterTabs = document.querySelectorAll('.filter-tab');
    const searchInput = document.getElementById('searchInput');
    let conversationItems = document.querySelectorAll('.ticket-item');
    const conversationsList = document.getElementById('conversationsList');
    
    // Realtime variables
    let isRealtimeEnabled = true;
    let lastUpdateTime = new Date().getTime();

    // Filter functionality
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            filterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filter = this.getAttribute('data-filter');
            filterConversations(filter);
        });
    });

    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        filterConversations('all', searchTerm);
    });

    // Filter conversations function
    function filterConversations(statusFilter, searchTerm = '') {
        conversationItems.forEach(item => {
            const unreadCount = parseInt(item.getAttribute('data-unread'));
            const customerName = item.querySelector('.ticket-customer').textContent.toLowerCase();
            const subject = item.querySelector('.ticket-subject').textContent.toLowerCase();
            const preview = item.querySelector('.ticket-preview').textContent.toLowerCase();

            let showByStatus = true;
            if (statusFilter === 'unread') {
                showByStatus = unreadCount > 0;
            }

            const showBySearch = searchTerm === '' ||
                customerName.includes(searchTerm) ||
                subject.includes(searchTerm) ||
                preview.includes(searchTerm);

            if (showByStatus && showBySearch) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });

        // Show empty state if no conversations match
        const visibleConversations = document.querySelectorAll('.ticket-item[style="display: flex"]');
        const emptyState = document.querySelector('.empty-state');

        if (visibleConversations.length === 0 && conversationItems.length > 0) {
            if (!emptyState) {
                const newEmptyState = document.createElement('div');
                newEmptyState.className = 'empty-state';
                newEmptyState.innerHTML = `
                    <i class="fas fa-search"></i>
                    <h3>Không tìm thấy kết quả</h3>
                    <p>Thử thay đổi từ khóa tìm kiếm hoặc bộ lọc.</p>
                `;
                conversationsList.appendChild(newEmptyState);
            }
        } else if (emptyState) {
            emptyState.remove();
        }
    }

    // Click on conversation to view details
    conversationItems.forEach(item => {
        item.addEventListener('click', function() {
            const conversationId = this.getAttribute('data-conversation-id');
            window.location.href = `/admin/support/conversation/${conversationId}`;
        });
    });

    // Highlight unread conversations
    conversationItems.forEach(item => {
        const unreadBadge = item.querySelector('.unread-badge');
        if (unreadBadge && parseInt(unreadBadge.textContent) > 0) {
            item.style.borderLeft = '4px solid #dc3545';
            item.style.backgroundColor = '#fff5f5';
            item.style.fontWeight = '500';
        }
    });

    // Realtime update function
    function updateConversations() {
        if (!isRealtimeEnabled) return;

        fetch('{{ route("admin.support.getUpdates") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                last_update: lastUpdateTime
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                console.log('Realtime update received:', data);
                
                // Update statistics
                document.getElementById('unreadCount').textContent = data.stats.unread_count;
                document.getElementById('totalCount').textContent = data.stats.total_count;
                document.getElementById('allCount').textContent = data.stats.total_count;
                document.getElementById('unreadFilterCount').textContent = data.stats.unread_count;

                // Update conversations
                if (data.updates && data.updates.length > 0) {
                    data.updates.forEach(update => {
                        updateConversationItem(update);
                    });
                }

                // Add new conversations
                if (data.new_conversations && data.new_conversations.length > 0) {
                    data.new_conversations.forEach(conversation => {
                        addNewConversationItem(conversation);
                    });
                }

                lastUpdateTime = new Date().getTime();
            }
        })
        .catch(error => {
            console.error('Realtime update error:', error);
        })
        .finally(() => {
            if (isRealtimeEnabled) {
                setTimeout(updateConversations, 5000); // Update every 5 seconds
            }
        });
    }

    // Update existing conversation item
    function updateConversationItem(update) {
        console.log('Updating conversation:', update);
        const item = document.querySelector(`[data-conversation-id="${update.conversation_id}"]`);
        if (item) {
            // Cập nhật unread count
            const unreadBadge = item.querySelector('.unread-badge');
            if (update.unread_count > 0) {
                if (unreadBadge) {
                    unreadBadge.textContent = update.unread_count;
                } else {
                    const newBadge = document.createElement('div');
                    newBadge.className = 'unread-badge';
                    newBadge.textContent = update.unread_count;
                    item.querySelector('.ticket-header').appendChild(newBadge);
                }
                item.setAttribute('data-unread', update.unread_count);
                item.style.borderLeft = '4px solid #dc3545';
                item.style.backgroundColor = '#fff5f5';
                item.style.fontWeight = '500';
                
                // Thêm animation cho tin nhắn mới
                item.classList.add('new-message');
                setTimeout(() => item.classList.remove('new-message'), 600);
            } else {
                if (unreadBadge) {
                    unreadBadge.remove();
                }
                item.setAttribute('data-unread', '0');
                item.style.borderLeft = '';
                item.style.backgroundColor = '';
                item.style.fontWeight = '';
            }

            // Cập nhật tin nhắn cuối
            if (update.last_message) {
                const previewElement = item.querySelector('.ticket-preview');
                if (previewElement) {
                    previewElement.textContent = update.last_message;
                }
            }

            // Cập nhật thời gian
            if (update.updated_at) {
                const timeElement = item.querySelector('.ticket-time');
                if (timeElement) {
                    timeElement.textContent = new Date(update.updated_at).toLocaleString('vi-VN');
                }
            }

            // Cập nhật số tin nhắn
            const messageCount = item.querySelector('.message-count');
            if (messageCount) {
                messageCount.textContent = update.unread_count > 0 ? 
                    `${update.unread_count} tin nhắn mới` : 'Đã trả lời';
            }
        }
    }

    // Add new conversation item
    function addNewConversationItem(conversation) {
        // Kiểm tra xem conversation đã tồn tại chưa
        const existingItem = document.querySelector(`[data-conversation-id="${conversation.conversation_id}"]`);
        if (existingItem) {
            console.log('Conversation already exists:', conversation.conversation_id);
            return;
        }

        const newItem = document.createElement('div');
        newItem.className = 'ticket-item new-message';
        newItem.setAttribute('data-conversation-id', conversation.conversation_id);
        newItem.setAttribute('data-unread', conversation.unread_count);

        newItem.innerHTML = `
            <div class="ticket-avatar">
                ${(conversation.user?.name || 'U').charAt(0)}
            </div>
            <div class="ticket-content">
                <div class="ticket-header">
                    <div>
                        <div class="ticket-customer">${conversation.user?.name || 'Khách hàng'}</div>
                        <div class="ticket-time">${new Date(conversation.created_at).toLocaleString('vi-VN')}</div>
                    </div>
                    ${conversation.unread_count > 0 ? `<div class="unread-badge">${conversation.unread_count}</div>` : ''}
                </div>
                <div class="ticket-subject">${conversation.subject || 'Hỗ trợ nhanh'}</div>
                <div class="ticket-preview">${conversation.last_message || 'Chưa có tin nhắn'}</div>
                <div class="ticket-meta">
                    <span class="message-count">${conversation.unread_count > 0 ? `${conversation.unread_count} tin nhắn mới` : 'Đã trả lời'}</span>
                </div>
            </div>
        `;

        // Thêm click event
        newItem.addEventListener('click', function() {
            const conversationId = this.getAttribute('data-conversation-id');
            window.location.href = `/admin/support/conversation/${conversationId}`;
        });

        // Thêm vào đầu danh sách
        const firstItem = conversationsList.querySelector('.ticket-item');
        if (firstItem) {
            conversationsList.insertBefore(newItem, firstItem);
        } else {
            conversationsList.appendChild(newItem);
        }

        // Xóa empty state nếu có
        const emptyState = conversationsList.querySelector('.empty-state');
        if (emptyState) {
            emptyState.remove();
        }

        // Cập nhật conversation items array
        conversationItems = document.querySelectorAll('.ticket-item');
        
        console.log('Added new conversation:', conversation.conversation_id);
    }

    // Start realtime updates
    updateConversations();

    // Stop realtime when page is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            isRealtimeEnabled = false;
        } else {
            isRealtimeEnabled = true;
            updateConversations();
        }
    });
});
</script>
@endsection
