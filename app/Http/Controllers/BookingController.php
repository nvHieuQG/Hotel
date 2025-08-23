<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Services\BookingServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\RoomType;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(
        BookingServiceInterface $bookingService
    ) {
        $this->bookingService = $bookingService;
    }

    // ==================== BOOKING METHODS ====================

    /**
     * Hiển thị trang xác nhận đặt phòng
     */
    public function confirm(Request $request)
    {
        try {
            $data = $this->bookingService->getBookingPageData();
            // Lấy danh sách dịch vụ bổ sung đang hoạt động để hiển thị ở trang xác nhận
            try {
                $data['extraServices'] = \App\Models\ExtraService::query()
                    ->where('is_active', true)
                    ->orderBy('name')
                    ->get();
            } catch (\Throwable $e) {
                $data['extraServices'] = collect();
            }

            // Xử lý tham số từ URL để pre-fill form
            $bookingData = [
                'room_type_id' => $request->get('room_type_id'),
                'check_in_date' => $request->get('check_in_date'),
                'check_out_date' => $request->get('check_out_date'),
                'guests' => $request->get('guests'),
                'phone' => $request->get('phone'),
                'notes' => $request->get('notes'),
            ];

            // Loại bỏ các giá trị null và validate dữ liệu
            $bookingData = array_filter($bookingData, function ($value) {
                return $value !== null && $value !== '';
            });

            // Validate room_type_id nếu có
            if (isset($bookingData['room_type_id'])) {
                $roomType = RoomType::find($bookingData['room_type_id']);
                if (!$roomType) {
                    unset($bookingData['room_type_id']);
                }
            }

            // Validate dates nếu có
            if (isset($bookingData['check_in_date'])) {
                if (!strtotime($bookingData['check_in_date']) || strtotime($bookingData['check_in_date']) < strtotime('today')) {
                    unset($bookingData['check_in_date']);
                }
            }

            if (isset($bookingData['check_out_date'])) {
                if (
                    !strtotime($bookingData['check_out_date']) ||
                    (isset($bookingData['check_in_date']) && strtotime($bookingData['check_out_date']) <= strtotime($bookingData['check_in_date']))
                ) {
                    unset($bookingData['check_out_date']);
                }
            }

            // Validate guests nếu có
            if (isset($bookingData['guests'])) {
                $guests = (int) $bookingData['guests'];
                if ($guests < 1 || $guests > 5) {
                    unset($bookingData['guests']);
                } else {
                    $bookingData['guests'] = $guests;
                }
            }

            // Thêm bookingData vào data để truyền đến view
            // Truyền promotion_id nếu có từ URL (được gắn từ trang chi tiết phòng)
            if ($request->filled('promotion_id')) {
                $bookingData['promotion_id'] = (int) $request->get('promotion_id');
            }

            $data['bookingData'] = $bookingData;

            return view('client.booking.confirm', $data);
        } catch (\Exception $e) {
            Log::error('Booking confirm error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->withErrors(['message' => 'Có lỗi xảy ra khi tải trang đặt phòng. Vui lòng thử lại.']);
        }
    }

    /**
     * Hiển thị trang đặt phòng (chuyển hướng đến trang confirm)
     */
    public function booking(Request $request)
    {
        // Chuyển hướng đến trang confirm với các tham số từ URL
        $params = $request->only(['room_type_id', 'check_in_date', 'check_out_date', 'guests', 'phone', 'notes']);

        if (!empty($params)) {
            return redirect()->route('booking.confirm', $params);
        }

        return redirect()->route('booking.confirm');
    }

    /**
     * Lưu thông tin đặt phòng (không dùng nữa, chuyển sang ajaxStoreBooking)
     */
    public function storeBooking(Request $request)
    {
        // Chuyển hướng đến trang confirm
        return redirect()->route('booking.confirm');
    }

    public function ajaxStoreBooking(Request $request)
    {
        try {
            // Validate cơ bản trước
            $baseValidated = $request->validate([
                'room_type_id' => 'required|exists:room_types,id',
                'check_in_date' => 'required|date|after_or_equal:today',
                'check_out_date' => 'required|date|after:check_in_date',
                'guests' => 'required|integer|min:1',
                'adults' => 'nullable|integer|min:1',
                'children' => 'nullable|integer|min:0',
                'infants' => 'nullable|integer|min:0',
                'phone' => 'required|string|max:20',
                'notes' => 'nullable|string|max:1000',
                'total_booking_price' => 'nullable|numeric|min:0',
                'surcharge' => 'nullable|numeric|min:0',
                'extra_services' => 'sometimes|string', // JSON string from frontend
                'extra_services_total' => 'sometimes|numeric|min:0',
            ]);

            // Lấy capacity từ room_type để ràng buộc guests theo sức chứa
            $roomType = RoomType::findOrFail($baseValidated['room_type_id']);
            $capacity = max(1, (int)($roomType->capacity ?? 1));
            $extraAllowed = $capacity <= 3 ? 1 : 0;
            $capacityLimit = $capacity + $extraAllowed; // Giới hạn guests = adults + children

            if ((int)$baseValidated['guests'] > $capacityLimit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Số khách vượt quá giới hạn cho phép của loại phòng (' . $capacityLimit . ')'
                ], 422);
            }

            $validated = $baseValidated;

            // Tạo booking với trạng thái pending_payment (chờ thanh toán)
            $booking = $this->bookingService->createPendingBooking($validated + ['user_id' => Auth::id()]);
            $booking->load('user', 'room.roomType', 'room.primaryImage', 'room.firstImage');

            // Không lưu total_booking_price trực tiếp vào DB (không có cột).
            // Tổng tiền sẽ được lưu vào booking->price tại BookingService.

            // Lấy URL ảnh phòng
            $roomImage = null;
            if ($booking->room->primaryImage) {
                $roomImage = $booking->room->primaryImage->full_image_url;
            } elseif ($booking->room->firstImage) {
                $roomImage = $booking->room->firstImage->full_image_url;
            }

            return response()->json([
                'success' => true,
                'booking' => $booking,
                'user' => $booking->user,
                'roomType' => $booking->room->roomType ?? null,
                'roomImage' => $roomImage,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }



    /**
     * Lấy ảnh phòng cho loại phòng
     */
    public function getRoomTypeImage($id)
    {
        try {
            $roomType = \App\Models\RoomType::findOrFail($id);

            // Lấy phòng đầu tiên của loại phòng này
            $representativeRoom = $roomType->rooms()->with(['primaryImage', 'firstImage'])->first();

            $imageUrl = null;
            if ($representativeRoom) {
                if ($representativeRoom->primaryImage) {
                    $imageUrl = $representativeRoom->primaryImage->full_image_url;
                } elseif ($representativeRoom->firstImage) {
                    $imageUrl = $representativeRoom->firstImage->full_image_url;
                }
            }

            return response()->json([
                'success' => true,
                'image_url' => $imageUrl,
                'room_type_name' => $roomType->name
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Không thể tải ảnh phòng'
            ], 404);
        }
    }

    /**
     * Hiển thị danh sách đặt phòng của người dùng
     */
    public function myBookings()
    {
        $bookings = $this->bookingService->getCurrentUserBookings();
        return view('client.profile.bookings.index', compact('bookings'));
    }

    /**
     * Hủy đặt phòng
     */
    public function cancelBooking($id)
    {
        try {
            $this->bookingService->cancelBooking($id);
            return back()->with('success', 'Đã hủy đặt phòng thành công.');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Hiển thị chi tiết booking cho người dùng
     */
    public function showDetail($id)
    {
        $booking = $this->bookingService->getBookingDetail($id);
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem đặt phòng này.');
        }

        // Lấy thông tin thanh toán
        $paymentHistory = $booking->payments()->orderBy('created_at', 'desc')->get();
        $canPay = in_array($booking->status, ['pending', 'confirmed']) && !$booking->hasSuccessfulPayment();

        return view('client.booking.detail', compact('booking', 'paymentHistory', 'canPay'));
    }

    /**
     * Trả về partial danh sách đặt phòng cho AJAX
     */
    public function partial()
    {
        $bookings = $this->bookingService->getCurrentUserBookings();
        return view('client.profile.bookings.partial', compact('bookings'));
    }

    /**
     * Trả về partial chi tiết booking cho AJAX/modal (client)
     */
    public function detailPartial($id)
    {
        $booking = $this->bookingService->getBookingDetail($id);
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem đặt phòng này.');
        }
        return view('client.profile.bookings.detail', compact('booking'));
    }

    // ==================== BOOKING NOTE METHODS ====================

    /**
     * Hiển thị danh sách ghi chú của một booking
     */
    public function notesIndex(Request $request, $bookingId)
    {
        try {
            $notes = $this->bookingService->getVisibleNotes($bookingId);

            $notesWithPermissions = $notes->map(function ($note) {
                $note->can_edit = $this->bookingService->canEditNote($note->id);
                $note->can_delete = $this->bookingService->canDeleteNote($note->id);
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
    public function notesCreate($bookingId)
    {
        $booking = \App\Models\Booking::findOrFail($bookingId);
        return view('booking-notes.create', compact('bookingId', 'booking'));
    }

    /**
     * Lưu ghi chú mới
     */
    public function notesStore(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'content' => 'required|string|max:1000',
                'type' => 'required|in:customer,staff,admin',
                'visibility' => 'required|in:public,private,internal',
                'is_internal' => 'boolean'
            ]);

            $note = $this->bookingService->createNote($validatedData);

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
    public function notesStoreRequest(Request $request, $bookingId): RedirectResponse
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000',
            ]);

            $note = $this->bookingService->createCustomerRequest(
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
    public function notesStoreResponse(Request $request, $bookingId): RedirectResponse
    {
        try {
            $request->validate([
                'content' => 'required|string|max:1000',
                'visibility' => 'required|in:public,internal',
            ]);

            $note = $this->bookingService->createAdminResponse(
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
    public function notesEdit($bookingId, $id)
    {
        try {
            $note = $this->bookingService->getNoteById($id);

            if (!$note) {
                return back()->with('error', 'Không tìm thấy ghi chú');
            }

            if (!$this->bookingService->canEditNote($id)) {
                return back()->with('error', 'Bạn không có quyền chỉnh sửa ghi chú này');
            }

            $booking = $note->booking;
            return view('booking-notes.edit', compact('note', 'booking'));
        } catch (\Exception $e) {
            return back()->with('error', 'Không thể tải ghi chú: ' . $e->getMessage());
        }
    }

    /**
     * Cập nhật ghi chú
     */
    public function notesUpdate(Request $request, $bookingId, $id)
    {
        try {
            $validatedData = $request->validate([
                'content' => 'required|string|max:1000',
                'visibility' => 'sometimes|in:public,private,internal'
            ]);

            $success = $this->bookingService->updateNote($id, $validatedData);

            if ($success) {
                $user = Auth::user();
                $note = $this->bookingService->getNoteById($id);
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
    public function notesDestroy($bookingId, $id)
    {
        try {
            $note = $this->bookingService->getNoteById($id);

            if (!$note) {
                return back()->with('error', 'Không tìm thấy ghi chú');
            }

            if (!$this->bookingService->canDeleteNote($id)) {
                return back()->with('error', 'Bạn không có quyền xóa ghi chú này');
            }

            $bookingId = $note->booking_id;
            $success = $this->bookingService->deleteNote($id);

            if ($success) {
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
    public function notesShow($bookingId, $id)
    {
        try {
            $note = $this->bookingService->getNoteById($id);

            if (!$note) {
                return back()->with('error', 'Không tìm thấy ghi chú');
            }

            if (!$this->bookingService->canViewNote($id)) {
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
    public function notesSearch(Request $request, $bookingId)
    {
        try {
            $request->validate([
                'keyword' => 'required|string|min:1|max:100'
            ]);

            $keyword = $request->input('keyword');
            $notes = $this->bookingService->searchNotes($bookingId, $keyword);

            $notesWithPermissions = $notes->map(function ($note) {
                $note->can_edit = $this->bookingService->canEditNote($note->id);
                $note->can_delete = $this->bookingService->canDeleteNote($note->id);
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
    public function notesPartial()
    {
        // Lấy tất cả booking của user hiện tại
        $bookings = $this->bookingService->getCurrentUserBookings();
        $allNotes = collect();

        // Lấy ghi chú từ tất cả booking
        foreach ($bookings as $booking) {
            $notes = $this->bookingService->getVisibleNotes($booking->id);
            $allNotes = $allNotes->merge($notes);
        }

        return view('client.profile.notes.partial', compact('allNotes'));
    }

    // ==================== HELPER METHODS ====================

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
