@extends('client.layouts.master')

@section('styles')
<style>
/* Support Chat Styles */
.support-chat-container {
    max-width: 800px;
    margin: 0 auto;
}

.chat-messages {
    max-height: 500px;
    overflow-y: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    background: #f8f9fa;
}

.message {
    margin-bottom: 16px;
    display: flex;
    flex-direction: column;
}

.message.user {
    align-items: flex-end;
}

.message.admin {
    align-items: flex-start;
}

.message-bubble {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 16px;
    font-size: 14px;
    line-height: 1.4;
    position: relative;
}

.message.user .message-bubble {
    background: #007bff;
    color: white;
    border-bottom-right-radius: 4px;
}

.message.admin .message-bubble {
    background: #f1f1f1;
    color: #333;
    border-bottom-left-radius: 4px;
}

.message-time {
    font-size: 11px;
    color: #6c757d;
    margin-top: 4px;
    text-align: center;
}

/* File input styles */
.file-input-container {
    position: relative;
    margin-bottom: 15px;
}

.file-input-wrapper {
    display: flex;
    align-items: center;
    gap: 10px;
    border: 2px dashed #dee2e6;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s;
    cursor: pointer;
}

.file-input-wrapper:hover {
    border-color: #007bff;
    background: #f8f9fa;
}

.file-input-wrapper.dragover {
    border-color: #007bff;
    background: #e3f2fd;
}

.file-input {
    display: none;
}

.file-input-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    cursor: pointer;
    color: #6c757d;
}

.file-input-label i {
    font-size: 2rem;
    color: #007bff;
}

.file-input-text {
    font-size: 14px;
    font-weight: 500;
}

.file-input-hint {
    font-size: 12px;
    color: #adb5bd;
}

/* File preview */
.file-preview {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #e3f2fd;
    border: 1px solid #bbdefb;
    border-radius: 8px;
    margin: 8px 0;
    max-width: 100%;
}

.file-preview-icon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #1976d2;
}

.file-preview-name {
    flex: 1;
    font-size: 12px;
    color: #1976d2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-preview-remove {
    width: 20px;
    height: 20px;
    border: none;
    background: #ffebee;
    color: #d32f2f;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    transition: all 0.2s;
}

.file-preview-remove:hover {
    background: #ffcdd2;
    transform: scale(1.1);
}

/* Image display in chat */
.message-bubble img {
    max-width: 200px;
    max-height: 150px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
}

.message-bubble img:hover {
    transform: scale(1.05);
}

/* Modal popup cho ảnh */
.image-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
}

.image-modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    max-width: 90%;
    max-height: 90%;
    text-align: center;
}

.image-modal img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
}

.image-modal-close {
    position: absolute;
    top: 20px;
    right: 30px;
    color: white;
    font-size: 40px;
    font-weight: bold;
    cursor: pointer;
    z-index: 10000;
}

.image-modal-close:hover {
    color: #ccc;
}

/* Responsive */
@media (max-width: 768px) {
    .support-chat-container {
        padding: 0 15px;
    }

    .message-bubble {
        max-width: 85%;
    }
}
</style>
@endsection

@section('content')
<div class="container py-5">
    <div class="support-chat-container">
        <h2 class="mb-4">Chi tiết yêu cầu hỗ trợ #{{ $ticket->id }}</h2>
        <div class="mb-3"><b>Chủ đề:</b> {{ $ticket->id }}</div>

        <div class="chat-messages" id="chatMessages">
            @foreach($ticket->messages as $msg)
                <div class="message {{ $msg->sender_type == 'user' ? 'user' : 'admin' }}">
                    @if(!empty(trim((string)$msg->message)))
                        <div class="message-bubble">{{ $msg->message }}</div>
                    @endif
                    @if(!empty($msg->attachment_path) && \Illuminate\Support\Str::startsWith((string)$msg->attachment_type, 'image'))
                        <div class="message-bubble" onclick="openImageModal('{{ asset('storage/'.$msg->attachment_path) }}')" style="cursor: pointer;">
                            <img src="{{ asset('storage/'.$msg->attachment_path) }}" alt="attachment" />
                        </div>
                    @elseif(!empty($msg->attachment_path))
                        <div class="message-bubble">
                            <a href="{{ asset('storage/'.$msg->attachment_path) }}" target="_blank" rel="noopener">{{ $msg->attachment_name ?? 'Tệp đính kèm' }}</a>
                        </div>
                    @endif
                    <div class="message-time">{{ $msg->created_at->format('d/m/Y H:i') }}</div>
                </div>
            @endforeach
        </div>

        <form method="POST" action="{{ route('support.sendMessage', $ticket->id) }}" enctype="multipart/form-data" id="supportForm">
            @csrf
            <div class="file-input-container">
                <div class="file-input-wrapper" id="fileInputWrapper">
                    <input type="file" name="attachment" id="attachmentInput" class="file-input" accept="image/*,application/pdf,application/zip,text/plain" />
                    <label for="attachmentInput" class="file-input-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div class="file-input-text">Chọn tệp đính kèm</div>
                        <div class="file-input-hint">Kéo thả tệp vào đây hoặc click để chọn</div>
                    </label>
                </div>
            </div>

            <!-- File preview area -->
            <div id="filePreview" class="file-preview" style="display: none;">
                <div class="file-preview-icon">
                    <i class="fas fa-file"></i>
                </div>
                <div class="file-preview-name" id="fileName"></div>
                <button type="button" class="file-preview-remove" id="removeFile" title="Xóa tệp">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <div class="mb-3">
                <textarea name="message" id="messageInput" class="form-control" rows="3" placeholder="Nhập tin nhắn..." required></textarea>
            </div>
            <button type="submit" class="btn btn-success" id="sendBtn">Gửi tin nhắn</button>
        </form>
    </div>
</div>

<!-- Modal popup cho ảnh -->
<div id="imageModal" class="image-modal">
    <span class="image-modal-close">&times;</span>
    <div class="image-modal-content">
        <img id="modalImage" src="" alt="Ảnh lớn" />
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Elements
    const supportForm = document.getElementById('supportForm');
    const messageInput = document.getElementById('messageInput');
    const attachmentInput = document.getElementById('attachmentInput');
    const fileInputWrapper = document.getElementById('fileInputWrapper');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const removeFile = document.getElementById('removeFile');
    const sendBtn = document.getElementById('sendBtn');
    const chatMessages = document.getElementById('chatMessages');

    // Modal elements
    const imageModal = document.getElementById('imageModal');
    const modalImage = document.getElementById('modalImage');
    const modalClose = document.querySelector('.image-modal-close');

    // Scroll to bottom of chat
    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    // Scroll to bottom on load
    scrollToBottom();

    // Modal event listeners
    modalClose.addEventListener('click', function() {
        imageModal.style.display = 'none';
    });

    // Đóng modal khi click bên ngoài
    imageModal.addEventListener('click', function(e) {
        if (e.target === imageModal) {
            imageModal.style.display = 'none';
        }
    });

    // Đóng modal bằng ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && imageModal.style.display === 'block') {
            imageModal.style.display = 'none';
        }
    });

    // File input event listeners
    attachmentInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            showFilePreview(file);
            // Bỏ required cho message input nếu có file
            messageInput.removeAttribute('required');
        }
    });

    // Remove file button
    removeFile.addEventListener('click', function() {
        clearFilePreview();
        // Khôi phục required cho message input
        messageInput.setAttribute('required', 'required');
    });

    // Drag and drop functionality
    fileInputWrapper.addEventListener('dragover', function(e) {
        e.preventDefault();
        fileInputWrapper.classList.add('dragover');
    });

    fileInputWrapper.addEventListener('dragleave', function(e) {
        e.preventDefault();
        fileInputWrapper.classList.remove('dragover');
    });

    fileInputWrapper.addEventListener('drop', function(e) {
        e.preventDefault();
        fileInputWrapper.classList.remove('dragover');

        const files = e.dataTransfer.files;
        if (files.length > 0) {
            attachmentInput.files = files;
            const file = files[0];
            if (file) {
                showFilePreview(file);
                messageInput.removeAttribute('required');
            }
        }
    });

    // Form submission
    supportForm.addEventListener('submit', function(e) {
        const message = messageInput.value.trim();
        const hasFile = attachmentInput.files && attachmentInput.files[0];

        if (!message && !hasFile) {
            e.preventDefault();
            alert('Vui lòng nhập tin nhắn hoặc chọn tệp đính kèm');
            return;
        }

        // Disable button to prevent double submission
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Gửi...';
    });

    // Hàm hiển thị file preview
    function showFilePreview(file) {
        fileName.textContent = file.name;

        // Thay đổi icon tùy theo loại file
        const iconElement = filePreview.querySelector('.file-preview-icon i');
        if (file.type.startsWith('image/')) {
            iconElement.className = 'fas fa-image';
        } else if (file.type === 'application/pdf') {
            iconElement.className = 'fas fa-file-pdf';
        } else if (file.type === 'application/zip') {
            iconElement.className = 'fas fa-file-archive';
        } else if (file.type === 'text/plain') {
            iconElement.className = 'fas fa-file-alt';
        } else {
            iconElement.className = 'fas fa-file';
        }

        filePreview.style.display = 'flex';
    }

    // Hàm xóa file preview
    function clearFilePreview() {
        attachmentInput.value = '';
        filePreview.style.display = 'none';
        fileName.textContent = '';
    }

    // Hàm mở modal ảnh
    window.openImageModal = function(imageSrc) {
        modalImage.src = imageSrc;
        imageModal.style.display = 'block';
    };
});
</script>
@endsection
