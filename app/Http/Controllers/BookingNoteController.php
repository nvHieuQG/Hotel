<?php

namespace App\Http\Controllers;

use App\Interfaces\Services\BookingNoteServiceInterface;
use App\Interfaces\Repositories\BookingNoteRepositoryInterface;
use Illuminate\Http\Request;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class BookingNoteController extends Controller
{
    protected $bookingNoteService;
    protected $bookingNoteRepository;

    public function __construct(
        BookingNoteServiceInterface $bookingNoteService,
        BookingNoteRepositoryInterface $bookingNoteRepository
    ) {
        $this->bookingNoteService = $bookingNoteService;
        $this->bookingNoteRepository = $bookingNoteRepository;
    }

    /**
     * Hiển thị danh sách ghi chú của một booking
     */
    public function index(Request $request, $bookingId)
    {
        try {
            $notes = $this->bookingNoteService->getVisibleNotes($bookingId);
            
            // Thêm thông tin quyền cho mỗi ghi chú
            $notesWithPermissions = $notes->map(function ($note) {
                $note->can_edit = $this->bookingNoteService->canEditNote($note->id);
                $note->can_delete = $this->bookingNoteService->canDeleteNote($note->id);
                $note->type_text = $this->getTypeText($note->type);
                $note->visibility_text = $this->getVisibilityText($note->visibility);
                return $note;
            });
            
            return view('booking-notes.index', compact('notesWithPermissions', 'bookingId'));
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể tải danh sách ghi chú: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form tạo ghi chú
     */
    public function create($bookingId)
    {
        // Lấy thông tin booking để hiển thị mã booking
        $booking = \App\Models\Booking::findOrFail($bookingId);
        return view('booking-notes.create', compact('bookingId', 'booking'));
    }

    /**
     * Lưu ghi chú mới
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'content' => 'required|string|max:1000',
                'type' => 'required|in:customer,staff,admin',
                'visibility' => 'required|in:public,private,internal',
                'is_internal' => 'boolean'
            ]);

            $note = $this->bookingNoteService->createNote($validatedData);
            
            // Kiểm tra user role để quay về trang phù hợp
            $user = Auth::user();
            if ($user->role && $user->role->name === 'admin') {
                return redirect()->route('admin.bookings.show', $request->booking_id)
                    ->with('success', 'Tạo ghi chú thành công');
            } else {
                return redirect()->route('booking-notes.index', $request->booking_id)
                    ->with('success', 'Tạo ghi chú thành công');
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Không thể tạo ghi chú: ' . $e->getMessage());
        }
    }

    /**
     * Tạo yêu cầu từ customer
     */
    public function storeRequest(Request $request, $bookingId): RedirectResponse
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000',
            ]);

            // Tạo yêu cầu từ customer
            $note = $this->bookingNoteService->createCustomerRequest(
                $bookingId,
                $request->content,
                Auth::id()
            );

            return back()->with('success', 'Yêu cầu của bạn đã được gửi thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể gửi yêu cầu: ' . $e->getMessage());
        }
    }

    /**
     * Tạo phản hồi từ admin/staff
     */
    public function storeResponse(Request $request, $bookingId): RedirectResponse
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000',
                'visibility' => 'required|in:public,internal',
            ]);

            // Tạo phản hồi từ admin/staff
            $note = $this->bookingNoteService->createAdminResponse(
                $bookingId,
                $request->content,
                Auth::user()->role->name
            );

            return back()->with('success', 'Phản hồi đã được gửi thành công!');
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể gửi phản hồi: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị form chỉnh sửa ghi chú
     */
    public function edit($bookingId, $id)
    {
        try {
            $note = $this->bookingNoteRepository->findById($id);
            
            if (!$note) {
                return back()->with('error', 'Không tìm thấy ghi chú');
            }
            
            if (!$this->bookingNoteService->canEditNote($id)) {
                return back()->with('error', 'Bạn không có quyền chỉnh sửa ghi chú này');
            }
            
            // Lấy thông tin booking để hiển thị mã booking
            $booking = $note->booking;
            return view('booking-notes.edit', compact('note', 'booking'));
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể tải ghi chú: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật ghi chú
     */
    public function update(Request $request, $bookingId, $id)
    {
        try {
            $validatedData = $request->validate([
                'content' => 'required|string|max:1000',
                'visibility' => 'sometimes|in:public,private,internal'
            ]);

            $success = $this->bookingNoteService->updateNote($id, $validatedData);
            
            if ($success) {
                // Kiểm tra user role để quay về trang phù hợp
                $user = Auth::user();
                $note = $this->bookingNoteRepository->findById($id);
                if ($user->role && $user->role->name === 'admin') {
                    return redirect()->route('admin.bookings.show', $note->booking_id)
                        ->with('success', 'Cập nhật ghi chú thành công');
                } else {
                    return back()->with('success', 'Cập nhật ghi chú thành công');
                }
            } else {
                return back()->with('error', 'Không thể cập nhật ghi chú');
            }
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'Không thể cập nhật ghi chú: ' . $e->getMessage());
        }
    }

    /**
     * Xóa ghi chú
     */
    public function destroy($bookingId, $id)
    {
        try {
            // Lấy thông tin ghi chú trước khi xóa
            $note = $this->bookingNoteRepository->findById($id);
            
            if (!$note) {
                return back()->with('error', 'Không tìm thấy ghi chú');
            }
            
            // Kiểm tra quyền xóa
            if (!$this->bookingNoteService->canDeleteNote($id)) {
                return back()->with('error', 'Bạn không có quyền xóa ghi chú này');
            }
            
            // Lưu booking_id trước khi xóa
            $bookingId = $note->booking_id;
            
            $success = $this->bookingNoteService->deleteNote($id);
            
            if ($success) {
                // Kiểm tra user role để quay về trang phù hợp
                $user = Auth::user();
                if ($user->role && $user->role->name === 'admin') {
                    return redirect()->route('admin.bookings.show', $bookingId)
                        ->with('success', 'Xóa ghi chú thành công');
                } else {
                    return back()->with('success', 'Xóa ghi chú thành công');
                }
            } else {
                return back()->with('error', 'Không thể xóa ghi chú');
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể xóa ghi chú: ' . $e->getMessage());
        }
    }

    /**
     * Hiển thị chi tiết ghi chú
     */
    public function show($bookingId, $id)
    {
        try {
            $note = $this->bookingNoteRepository->findById($id);
            
            if (!$note) {
                return back()->with('error', 'Không tìm thấy ghi chú');
            }
            
            if (!$this->bookingNoteService->canViewNote($id)) {
                return back()->with('error', 'Bạn không có quyền xem ghi chú này');
            }
            
            return view('booking-notes.show', compact('note'));
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể tải ghi chú: ' . $e->getMessage());
        }
    }

    /**
     * Tìm kiếm ghi chú
     */
    public function search(Request $request, $bookingId)
    {
        try {
            $request->validate([
                'keyword' => 'required|string|min:1|max:100'
            ]);

            $keyword = $request->input('keyword');
            $notes = $this->bookingNoteService->searchNotes($bookingId, $keyword);
            
            // Thêm thông tin quyền cho mỗi ghi chú
            $notesWithPermissions = $notes->map(function ($note) {
                $note->can_edit = $this->bookingNoteService->canEditNote($note->id);
                $note->can_delete = $this->bookingNoteService->canDeleteNote($note->id);
                $note->type_text = $this->getTypeText($note->type);
                $note->visibility_text = $this->getVisibilityText($note->visibility);
                return $note;
            });
            
            return view('booking-notes.index', compact('notesWithPermissions', 'bookingId', 'keyword'));
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể tìm kiếm ghi chú: ' . $e->getMessage());
        }
    }

    /**
     * Trả về partial danh sách ghi chú cho AJAX
     */
    public function partial()
    {
        $notes = $this->bookingNoteService->getNotesByUser(auth()->id());
        return view('client.profile.notes.partial', compact('notes'));
    }

    

    /**
     * Lấy text cho loại ghi chú
     */
    private function getTypeText(string $type): string
    {
        $types = [
            'customer' => 'Khách hàng',
            'staff' => 'Nhân viên',
            'admin' => 'Quản trị'
        ];
        
        return $types[$type] ?? $type;
    }

    /**
     * Lấy text cho quyền xem
     */
    private function getVisibilityText(string $visibility): string
    {
        $visibilities = [
            'public' => 'Công khai',
            'private' => 'Riêng tư',
            'internal' => 'Nội bộ'
        ];
        
        return $visibilities[$visibility] ?? $visibility;
    }
}
