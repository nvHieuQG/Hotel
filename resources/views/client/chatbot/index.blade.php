@extends('client.layouts.chatbot')

@section('title', 'Chat với MARRON AI - Khách sạn MARRON')

@push('styles')
    <link rel="stylesheet" href="{{ asset('client/css/chatbot.css') }}">
@endpush

@section('content')
                            <!-- Banner đã được xóa - chỉ giữ menu và nền menu -->

            <!-- Chat Interface -->
    <div class="chat-wrapper">
        <div class="container">
            <div class="chat-container">
                <div class="chat-layout">
                    <!-- Main Chat Area -->
                    <div class="chat-main">
                        <div id="chat-history" class="chat-history">
                            <!-- Messages will be loaded here -->
                        </div>
                        
                        <div class="input-group">
                            <input type="text" id="user-input" class="message-input" placeholder="Nhập câu hỏi của bạn..." onkeypress="checkEnter(event)">
                            <button class="btn-send" onclick="sendMessage()">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                    </div>
                    
                    <!-- FAQ Sidebar -->
                    <div class="chat-sidebar">
                        <h3>💡 Gợi ý & Câu hỏi</h3>
                        
                                                        <div class="faq-container">
                                    <!-- Đặt phòng & Thanh toán -->
                                    <button class="faq-btn suggestion-btn" onclick="sendQuickMessage('📋 Làm thế nào để đặt phòng trực tuyến?')">
                                        <span class="faq-icon">📋</span>
                                        <span class="faq-text">Cách đặt phòng trực tuyến</span>
                                    </button>
                                    
                                    <button class="faq-btn suggestion-btn" onclick="sendQuickMessage('💳 Có những phương thức thanh toán nào?')">
                                        <span class="faq-icon">💳</span>
                                        <span class="faq-text">Phương thức thanh toán</span>
                                    </button>
                                    
                                    <button class="faq-btn suggestion-btn" onclick="sendQuickMessage('❌ Làm thế nào để hủy đặt phòng?')">
                                        <span class="faq-icon">❌</span>
                                        <span class="faq-text">Cách hủy đặt phòng</span>
                                    </button>
                                    
                                    <button class="faq-btn suggestion-btn" onclick="sendQuickMessage('🔄 Có thể thay đổi phòng sau khi đặt không?')">
                                        <span class="faq-icon">🔄</span>
                                        <span class="faq-text">Thay đổi phòng</span>
                                    </button>
                                    
                                    <button class="faq-btn suggestion-btn" onclick="sendQuickMessage('📊 Làm thế nào để xem lịch sử đặt phòng?')">
                                        <span class="faq-icon">📊</span>
                                        <span class="faq-text">Xem lịch sử đặt phòng</span>
                                    </button>
                                    
                                    <button class="faq-btn suggestion-btn" onclick="sendQuickMessage('🎉 Có thể áp dụng mã khuyến mãi không?')">
                                        <span class="faq-icon">🎉</span>
                                        <span class="faq-text">Mã khuyến mãi</span>
                                    </button>
                                    
                                    <!-- Tính năng nâng cao -->
                                    <button class="faq-btn suggestion-btn" onclick="sendQuickMessage('⭐ Làm thế nào để đánh giá khách sạn?')">
                                        <span class="faq-icon">⭐</span>
                                        <span class="faq-text">Đánh giá khách sạn</span>
                                    </button>
                                    
                                    <button class="faq-btn suggestion-btn" onclick="sendQuickMessage('🧾 Có thể yêu cầu hóa đơn VAT không?')">
                                        <span class="faq-icon">🧾</span>
                                        <span class="faq-text">Hóa đơn VAT</span>
                                    </button>
                                    
                                    <button class="faq-btn suggestion-btn" onclick="sendQuickMessage('🆘 Làm thế nào để liên hệ hỗ trợ?')">
                                        <span class="faq-icon">🆘</span>
                                        <span class="faq-text">Liên hệ hỗ trợ</span>
                                    </button>
                                    
                                    <!-- Thông tin khách sạn -->
                                    <button class="faq-btn" onclick="sendQuickMessage('💰 Giá phòng bao nhiêu?')">
                                        <span class="faq-icon">💰</span>
                                        <span class="faq-text">Giá phòng</span>
                                    </button>
                                    
                                    <button class="faq-btn" onclick="sendQuickMessage('🕐 Giờ check-in và check-out?')">
                                        <span class="faq-icon">🕐</span>
                                        <span class="faq-text">Giờ check-in/check-out</span>
                                    </button>
                                    
                                    <button class="faq-btn" onclick="sendQuickMessage('🏊 Có hồ bơi không?')">
                                        <span class="faq-icon">🏊</span>
                                        <span class="faq-text">Hồ bơi</span>
                                    </button>
                                    
                                    <button class="faq-btn" onclick="sendQuickMessage('🚗 Có dịch vụ đưa đón sân bay không?')">
                                        <span class="faq-icon">🚗</span>
                                        <span class="faq-text">Đưa đón sân bay</span>
                                    </button>
                                    
                                    <button class="faq-btn" onclick="sendQuickMessage('🍽️ Nhà hàng có mở cửa 24/7?')">
                                        <span class="faq-icon">🍽️</span>
                                        <span class="faq-text">Nhà hàng</span>
                                    </button>
                                    
                                    <button class="faq-btn" onclick="sendQuickMessage('📞 Số điện thoại liên hệ?')">
                                        <span class="faq-icon">📞</span>
                                        <span class="faq-text">Liên hệ</span>
                                    </button>
                                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Khởi tạo chatbot
document.addEventListener('DOMContentLoaded', function() {
    addWelcomeMessage();
});

// Kiểm tra phím Enter
function checkEnter(event) {
    if (event.key === "Enter") {
        sendMessage();
    }
}

// Gửi tin nhắn
function sendMessage() {
    var userMessage = document.getElementById("user-input").value;
    if (userMessage.trim() !== "") {
        addMessageToChat(userMessage, 'user');
        document.getElementById("user-input").value = "";
        fetchMarronAIResponse(userMessage);
    }
}

        // Thêm tin nhắn vào chat
        function addMessageToChat(message, sender) {
            var chatHistory = document.getElementById("chat-history");
            var newMessage = document.createElement("div");
            newMessage.classList.add("message", sender);

            var avatar = document.createElement("img");
            avatar.classList.add("avatar");
            avatar.src = sender === 'user' ? 'https://img.icons8.com/?size=100&id=108639&format=png&color=000000' : 'https://img.icons8.com/?size=100&id=TIzFca5fs00u&format=png&color=FAB005';
            avatar.alt = sender === 'user' ? 'User' : 'Marron AI';

            var messageText = document.createElement("div");
            messageText.classList.add("message-text");
            
            // Xử lý text dài với xuống dòng
            if (sender === 'bot') {
                // Thay thế dấu chấm và dấu phẩy bằng xuống dòng
                var formattedMessage = message
                    .replace(/\. /g, '.\n')
                    .replace(/, /g, ',\n')
                    .replace(/\*\*/g, '') // Loại bỏ markdown
                    .trim();
                messageText.textContent = formattedMessage;
            } else {
                messageText.textContent = message;
            }

            newMessage.appendChild(avatar);
            newMessage.appendChild(messageText);
            chatHistory.appendChild(newMessage);
            chatHistory.scrollTop = chatHistory.scrollHeight;
        }

// Gửi tin nhắn nhanh
function sendQuickMessage(message) {
    addMessageToChat(message, 'user');
    fetchMarronAIResponse(message);
}

// Gọi API Marron AI
function fetchMarronAIResponse(userMessage) {
    // Hiển thị typing indicator
    showTypingIndicator();
    
    fetch('/chatbot/send-message', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ message: userMessage })
    })
    .then(response => response.json())
    .then(data => {
        // Ẩn typing indicator
        hideTypingIndicator();
        
        let botReply = "Xin lỗi, tôi không hiểu câu hỏi của bạn.";
        if (data && data.success && data.reply) {
            botReply = data.reply;
        }
        addMessageToChat(botReply, 'bot');
    })
    .catch(error => {
        // Ẩn typing indicator
        hideTypingIndicator();
        
        console.error('Error:', error);
        addMessageToChat("Rất tiếc, có lỗi xảy ra! Vui lòng thử lại sau.", 'bot');
    });
}

// Hiển thị typing indicator
function showTypingIndicator() {
    const chatHistory = document.getElementById('chat-history');
    const typingDiv = document.createElement('div');
    typingDiv.classList.add('message', 'bot');
    typingDiv.id = 'typing-indicator';
    
    typingDiv.innerHTML = `
        <img src="https://img.icons8.com/?size=100&id=TIzFca5fs00u&format=png&color=FAB005" alt="Marron AI" class="avatar">
        <div class="typing-indicator">
            MARRON AI đang soạn câu trả lời...
            <div class="typing-dots">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>
    `;
    
    chatHistory.appendChild(typingDiv);
    chatHistory.scrollTop = chatHistory.scrollHeight;
}

// Ẩn typing indicator
function hideTypingIndicator() {
    const typingIndicator = document.getElementById('typing-indicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

// Thêm tin nhắn chào mừng
function addWelcomeMessage() {
    const welcomeMessage = `
        <div class="text-center p-4">
            <h4 class="text-primary mb-3">👋 Chào mừng bạn đến với MARRON AI!</h4>
            <p class="text-muted">Tôi là trợ lý AI thông minh của khách sạn MARRON 5 sao.</p>
            <p class="text-muted mb-2">Tôi có thể giúp bạn:</p>
            <div class="text-left d-inline-block">
                <p class="text-muted mb-1">• 📋 Tìm hiểu thông tin phòng và dịch vụ</p>
                <p class="text-muted mb-1">• 💰 Hỏi về giá cả và khuyến mãi</p>
                <p class="text-muted mb-1">• 📍 Tư vấn địa điểm tham quan gần đó</p>
                <p class="text-muted mb-1">• 🚗 Hỗ trợ thông tin giao thông</p>
                <p class="text-muted mb-1">• ❓ Giải đáp các câu hỏi thường gặp</p>
            </div>
            <p class="text-muted mt-3">Bạn cần tôi giúp gì hôm nay? 😊</p>
        </div>
    `;
    
    const chatHistory = document.getElementById('chat-history');
    chatHistory.innerHTML = welcomeMessage;
}
</script>
@endpush
