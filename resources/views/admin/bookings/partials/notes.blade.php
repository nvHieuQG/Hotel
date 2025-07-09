<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-comments me-1"></i>
                    Ghi chú đặt phòng
                </div>
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                    <i class="fas fa-plus"></i> Thêm ghi chú
                </button>
            </div>
            <div class="card-body">
                <div id="notes-container">
                    <!-- Ghi chú sẽ được load bằng AJAX -->
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Đang tải...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal thêm ghi chú -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addNoteModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Thêm ghi chú mới
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addNoteForm">
                <div class="modal-body">
                    @csrf
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="note_type" class="form-label">Loại ghi chú</label>
                            <select name="type" id="note_type" class="form-select" required>
                                <option value="customer">Khách hàng</option>
                                <option value="staff">Nhân viên</option>
                                <option value="admin">Quản lý</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="note_visibility" class="form-label">Quyền xem</label>
                            <select name="visibility" id="note_visibility" class="form-select" required>
                                <option value="public">Công khai</option>
                                <option value="private">Riêng tư</option>
                                <option value="internal">Nội bộ</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="note_content" class="form-label">Nội dung ghi chú</label>
                        <textarea name="content" id="note_content" class="form-control" rows="4" 
                                  placeholder="Nhập nội dung ghi chú..." required maxlength="1000"></textarea>
                        <div class="form-text">
                            <span id="char-count">0</span>/1000 ký tự
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_internal" id="is_internal" value="1">
                            <label class="form-check-label" for="is_internal">
                                <i class="fas fa-eye-slash text-warning me-1"></i>
                                Ghi chú nội bộ (chỉ admin xem được)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Lưu ghi chú
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal chỉnh sửa ghi chú -->
<div class="modal fade" id="editNoteModal" tabindex="-1" aria-labelledby="editNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editNoteModalLabel">
                    <i class="fas fa-edit me-2"></i>Chỉnh sửa ghi chú
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editNoteForm">
                <div class="modal-body">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="edit_note_id" name="note_id">
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="edit_note_type" class="form-label">Loại ghi chú</label>
                            <select name="type" id="edit_note_type" class="form-select" required>
                                <option value="customer">Khách hàng</option>
                                <option value="staff">Nhân viên</option>
                                <option value="admin">Quản lý</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_note_visibility" class="form-label">Quyền xem</label>
                            <select name="visibility" id="edit_note_visibility" class="form-select" required>
                                <option value="public">Công khai</option>
                                <option value="private">Riêng tư</option>
                                <option value="internal">Nội bộ</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_note_content" class="form-label">Nội dung ghi chú</label>
                        <textarea name="content" id="edit_note_content" class="form-control" rows="4" 
                                  placeholder="Nhập nội dung ghi chú..." required maxlength="1000"></textarea>
                        <div class="form-text">
                            <span id="edit-char-count">0</span>/1000 ký tự
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Cập nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Load ghi chú khi trang được tải
    loadNotes();
    
    // Đếm ký tự cho form thêm ghi chú
    $('#note_content').on('input', function() {
        const length = $(this).val().length;
        $('#char-count').text(length);
    });
    
    // Đếm ký tự cho form chỉnh sửa ghi chú
    $('#edit_note_content').on('input', function() {
        const length = $(this).val().length;
        $('#edit-char-count').text(length);
    });
    
    // Xử lý form thêm ghi chú
    $('#addNoteForm').on('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Đang lưu...');
        
        $.ajax({
            url: '{{ route("booking-notes.store-ajax") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    showToast('Thêm ghi chú thành công!', 'success');
                    $('#addNoteModal').modal('hide');
                    $('#addNoteForm')[0].reset();
                    $('#char-count').text('0');
                    loadNotes();
                } else {
                    showToast(response.message || 'Có lỗi xảy ra!', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response?.message || 'Có lỗi xảy ra!', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Xử lý form chỉnh sửa ghi chú
    $('#editNoteForm').on('submit', function(e) {
        e.preventDefault();
        
        const noteId = $('#edit_note_id').val();
        const formData = new FormData(this);
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Đang cập nhật...');
        
        $.ajax({
            url: `/booking-notes/${noteId}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(response) {
                if (response.success) {
                    showToast('Cập nhật ghi chú thành công!', 'success');
                    $('#editNoteModal').modal('hide');
                    loadNotes();
                } else {
                    showToast(response.message || 'Có lỗi xảy ra!', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response?.message || 'Có lỗi xảy ra!', 'error');
            },
            complete: function() {
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
    
    // Xử lý xóa ghi chú
    $(document).on('click', '.delete-note-btn', function(e) {
        e.preventDefault();
        const noteId = $(this).data('note-id');
        deleteNote(noteId);
    });
    
    // Xử lý chỉnh sửa ghi chú
    $(document).on('click', '.edit-note-btn', function(e) {
        e.preventDefault();
        const noteId = $(this).data('note-id');
        const content = $(this).data('content');
        const type = $(this).data('type');
        const visibility = $(this).data('visibility');
        editNote(noteId, content, type, visibility);
    });
});

// Load danh sách ghi chú
function loadNotes() {
    $.ajax({
        url: '{{ route("booking-notes.index", $booking->id) }}',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                renderNotes(response.data);
            } else {
                $('#notes-container').html('<div class="alert alert-danger">Không thể tải ghi chú</div>');
            }
        },
        error: function() {
            $('#notes-container').html('<div class="alert alert-danger">Không thể tải ghi chú</div>');
        }
    });
}

// Render danh sách ghi chú
function renderNotes(notes) {
    if (notes.length === 0) {
        $('#notes-container').html('<div class="text-center text-muted"><i class="fas fa-comment-slash fa-2x mb-2"></i><p>Chưa có ghi chú nào</p></div>');
        return;
    }
    
    let html = '';
    notes.forEach(function(note) {
        const typeBadge = getTypeBadge(note.type);
        const visibilityBadge = getVisibilityBadge(note.visibility);
        const isInternal = note.is_internal ? '<span class="badge bg-warning ms-1"><i class="fas fa-eye-slash"></i> Nội bộ</span>' : '';
        
        html += `
            <div class="note-item border rounded p-3 mb-3 ${note.is_internal ? 'bg-light' : ''}">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong>${note.user.name}</strong>
                        ${typeBadge}
                        ${visibilityBadge}
                        ${isInternal}
                    </div>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item edit-note-btn" href="#" 
                                   data-note-id="${note.id}" 
                                   data-content="${note.content.replace(/"/g, '&quot;').replace(/'/g, '&#39;')}" 
                                   data-type="${note.type}" 
                                   data-visibility="${note.visibility}">
                                <i class="fas fa-edit me-2"></i>Chỉnh sửa
                            </a></li>
                            <li><a class="dropdown-item text-danger delete-note-btn" href="#" data-note-id="${note.id}">
                                <i class="fas fa-trash me-2"></i>Xóa
                            </a></li>
                        </ul>
                    </div>
                </div>
                <div class="note-content">
                    ${note.content.replace(/\n/g, '<br>')}
                </div>
                <div class="note-meta text-muted small mt-2">
                    <i class="fas fa-clock me-1"></i>${formatDateTime(note.created_at)}
                </div>
            </div>
        `;
    });
    
    $('#notes-container').html(html);
}

// Lấy badge cho loại ghi chú
function getTypeBadge(type) {
    const badges = {
        'customer': '<span class="badge bg-info ms-1">Khách hàng</span>',
        'staff': '<span class="badge bg-primary ms-1">Nhân viên</span>',
        'admin': '<span class="badge bg-danger ms-1">Quản lý</span>'
    };
    return badges[type] || '';
}

// Lấy badge cho quyền xem
function getVisibilityBadge(visibility) {
    const badges = {
        'public': '<span class="badge bg-success ms-1">Công khai</span>',
        'private': '<span class="badge bg-secondary ms-1">Riêng tư</span>',
        'internal': '<span class="badge bg-warning ms-1">Nội bộ</span>'
    };
    return badges[visibility] || '';
}

// Chỉnh sửa ghi chú
function editNote(noteId, content, type, visibility) {
    $('#edit_note_id').val(noteId);
    $('#edit_note_content').val(content);
    $('#edit_note_type').val(type);
    $('#edit_note_visibility').val(visibility);
    $('#edit-char-count').text(content.length);
    $('#editNoteModal').modal('show');
}

// Xóa ghi chú
function deleteNote(noteId) {
    if (confirm('Bạn có chắc chắn muốn xóa ghi chú này?')) {
        $.ajax({
            url: `/booking-notes/${noteId}`,
            type: 'DELETE',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showToast('Xóa ghi chú thành công!', 'success');
                    loadNotes();
                } else {
                    showToast(response.message || 'Có lỗi xảy ra!', 'error');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                showToast(response?.message || 'Có lỗi xảy ra!', 'error');
            }
        });
    }
}

// Format datetime
function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('vi-VN');
}

// Hiển thị toast
function showToast(message, type) {
    const toastClass = type === 'success' ? 'bg-success' : 'bg-danger';
    const toast = `
        <div class="toast align-items-center text-white ${toastClass} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;
    
    $('.toast-container').append(toast);
    $('.toast').toast('show');
}
</script> 