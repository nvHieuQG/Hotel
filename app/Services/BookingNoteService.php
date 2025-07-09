<?php

namespace App\Services;

use App\Models\BookingNote;
use App\Repositories\BookingNoteRepository;
use App\Repositories\BookingRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Collection;

class BookingNoteService
{
    protected $bookingNoteRepository;
    protected $bookingRepository;

    public function __construct(
        BookingNoteRepository $bookingNoteRepository,
        BookingRepository $bookingRepository
    ) {
        $this->bookingNoteRepository = $bookingNoteRepository;
        $this->bookingRepository = $bookingRepository;
    }

    /**
     * Lấy ghi chú của một booking mà user có thể xem
     */
    public function getVisibleNotes(int $bookingId): Collection
    {
        $user = Auth::user();
        $userRoles = $this->getUserRoles($user);

        return $this->bookingNoteRepository->getVisibleNotesByBookingId(
            $bookingId,
            $user->id,
            $userRoles
        );
    }

    /**
     * Tạo ghi chú mới
     */
    public function createNote(array $data): BookingNote
    {
        $user = Auth::user();

        // Validate dữ liệu
        $validator = Validator::make($data, [
            'booking_id' => 'required|exists:bookings,id',
            'content' => 'required|string|max:1000',
            'type' => 'required|in:customer,staff,admin',
            'visibility' => 'required|in:public,private,internal',
            'is_internal' => 'boolean'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // Kiểm tra quyền tạo ghi chú
        $this->validateNoteCreationPermission($user, $data);

        // Chuẩn bị dữ liệu
        $noteData = [
            'booking_id' => $data['booking_id'],
            'user_id' => $user->id,
            'content' => $data['content'],
            'type' => $data['type'],
            'visibility' => $data['visibility'],
            'is_internal' => $data['is_internal'] ?? false
        ];

        return $this->bookingNoteRepository->create($noteData);
    }

    /**
     * Cập nhật ghi chú
     */
    public function updateNote(int $noteId, array $data): bool
    {
        $user = Auth::user();
        $userRoles = $this->getUserRoles($user);

        // Kiểm tra quyền chỉnh sửa
        if (!$this->bookingNoteRepository->canUserEditNote($noteId, $user->id, $userRoles)) {
            throw new \Exception('Bạn không có quyền chỉnh sửa ghi chú này.');
        }

        // Validate dữ liệu
        $validator = Validator::make($data, [
            'content' => 'required|string|max:1000',
            'visibility' => 'sometimes|in:public,private,internal'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->bookingNoteRepository->update($noteId, $data);
    }

    /**
     * Xóa ghi chú
     */
    public function deleteNote(int $noteId): bool
    {
        $user = Auth::user();
        $userRoles = $this->getUserRoles($user);

        // Kiểm tra quyền xóa
        if (!$this->bookingNoteRepository->canUserDeleteNote($noteId, $user->id, $userRoles)) {
            throw new \Exception('Bạn không có quyền xóa ghi chú này.');
        }

        return $this->bookingNoteRepository->delete($noteId);
    }

    /**
     * Kiểm tra quyền tạo ghi chú
     */
    private function validateNoteCreationPermission($user, array $data): void
    {
        // Admin có thể tạo mọi loại ghi chú
        if ($user->hasRole('admin')) {
            return;
        }

        // Staff có thể tạo ghi chú staff và internal
        if ($user->hasRole('staff')) {
            if (in_array($data['type'], ['staff', 'admin'])) {
                throw new \Exception('Bạn không có quyền tạo ghi chú này.');
            }
            return;
        }

        // Customer chỉ có thể tạo ghi chú customer và public/private
        if ($data['type'] !== 'customer') {
            throw new \Exception('Bạn chỉ có thể tạo ghi chú khách hàng.');
        }

        if ($data['visibility'] === 'internal' || ($data['is_internal'] ?? false)) {
            throw new \Exception('Bạn không có quyền tạo ghi chú nội bộ.');
        }
    }

    /**
     * Lấy roles của user
     */
    private function getUserRoles($user): array
    {
        $roles = [];
        
        if ($user->hasRole('admin')) {
            $roles[] = 'admin';
        }
        
        if ($user->hasRole('staff')) {
            $roles[] = 'staff';
        }
        
        if ($user->hasRole('customer')) {
            $roles[] = 'customer';
        }

        return $roles;
    }
} 