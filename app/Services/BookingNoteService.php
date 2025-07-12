<?php

namespace App\Services;

use App\Models\BookingNote;
use App\Repositories\BookingNoteRepository;
use App\Repositories\BookingRepository;
use App\Interfaces\Repositories\BookingNoteRepositoryInterface;
use App\Interfaces\Services\BookingNoteServiceInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class BookingNoteService implements BookingNoteServiceInterface
{
    protected $bookingNoteRepository;
    protected $bookingRepository;

    public function __construct(
        BookingNoteRepositoryInterface $bookingNoteRepository,
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
        $userRoles = $this->getUserRoles($user);
        
        // Admin có thể tạo mọi loại ghi chú
        if (in_array('admin', $userRoles)) {
            return;
        }

        // Staff có thể tạo ghi chú customer và staff, không thể tạo admin
        if (in_array('staff', $userRoles)) {
            if ($data['type'] === 'admin') {
                throw new \Exception('Bạn không có quyền tạo ghi chú quản lý.');
            }
            // Staff có thể tạo ghi chú internal
            return;
        }

        // Customer chỉ có thể tạo ghi chú customer với visibility public
        if (in_array('customer', $userRoles)) {
        if ($data['type'] !== 'customer') {
                throw new \Exception('Bạn chỉ có thể tạo yêu cầu từ khách hàng.');
        }
            if ($data['visibility'] !== 'public') {
                throw new \Exception('Yêu cầu của bạn phải là công khai.');
            }
            return;
        }

        // Các trường hợp khác không được phép
        throw new \Exception('Bạn không có quyền tạo ghi chú. Vui lòng liên hệ admin để được hỗ trợ.');
    }

    /**
     * Lấy roles của user
     */
    private function getUserRoles($user): array
    {
        $roles = [];
        
        if ($user->role && $user->role->name === 'admin') {
            $roles[] = 'admin';
        }
        
        if ($user->role && $user->role->name === 'staff') {
            $roles[] = 'staff';
        }
        
        if ($user->role && $user->role->name === 'customer') {
            $roles[] = 'customer';
        }

        return $roles;
    }

    /**
     * Lấy ghi chú theo loại
     */
    public function getNotesByType(int $bookingId, string $type): Collection
    {
        return $this->bookingNoteRepository->getByType($bookingId, $type);
    }

    /**
     * Lấy ghi chú theo visibility
     */
    public function getNotesByVisibility(int $bookingId, string $visibility): Collection
    {
        return $this->bookingNoteRepository->getByVisibility($bookingId, $visibility);
    }

    /**
     * Lấy ghi chú có phân trang
     */
    public function getPaginatedNotes(int $bookingId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->bookingNoteRepository->getPaginated($bookingId, $perPage);
    }

    /**
     * Tìm kiếm ghi chú
     */
    public function searchNotes(int $bookingId, string $keyword): Collection
    {
        return $this->bookingNoteRepository->search($bookingId, $keyword);
    }

    /**
     * Lấy thống kê ghi chú
     */
    public function getNoteStatistics(int $bookingId): array
    {
        return $this->bookingNoteRepository->getStatistics($bookingId);
    }

    /**
     * Kiểm tra quyền xem ghi chú
     */
    public function canViewNote(int $noteId): bool
    {
        $user = Auth::user();
        $userRoles = $this->getUserRoles($user);
        return $this->bookingNoteRepository->canUserViewNote($noteId, $user->id, $userRoles);
    }

    /**
     * Kiểm tra quyền chỉnh sửa ghi chú
     */
    public function canEditNote(int $noteId): bool
    {
        $user = Auth::user();
        $userRoles = $this->getUserRoles($user);
        return $this->bookingNoteRepository->canUserEditNote($noteId, $user->id, $userRoles);
    }

    /**
     * Kiểm tra quyền xóa ghi chú
     */
    public function canDeleteNote(int $noteId): bool
    {
        $user = Auth::user();
        $userRoles = $this->getUserRoles($user);
        return $this->bookingNoteRepository->canUserDeleteNote($noteId, $user->id, $userRoles);
    }

    /**
     * Tạo ghi chú hệ thống tự động
     */
    public function createSystemNote(int $bookingId, string $content, string $type = 'admin'): BookingNote
    {
        $data = [
            'booking_id' => $bookingId,
            'user_id' => 1, // System user ID
            'content' => $content,
            'type' => $type,
            'visibility' => 'internal',
            'is_internal' => true
        ];

        return $this->bookingNoteRepository->create($data);
    }

    /**
     * Tạo ghi chú thông báo cho khách hàng
     */
    public function createCustomerNotification(int $bookingId, string $content): BookingNote
    {
        $data = [
            'booking_id' => $bookingId,
            'user_id' => Auth::id(),
            'content' => $content,
            'type' => 'admin',
            'visibility' => 'public',
            'is_internal' => false
        ];

        return $this->bookingNoteRepository->create($data);
    }

    /**
     * Tạo ghi chú nội bộ cho nhân viên
     */
    public function createInternalNote(int $bookingId, string $content): BookingNote
    {
        $data = [
            'booking_id' => $bookingId,
            'user_id' => Auth::id(),
            'content' => $content,
            'type' => 'staff',
            'visibility' => 'internal',
            'is_internal' => true
        ];

        return $this->bookingNoteRepository->create($data);
    }

    /**
     * Tạo yêu cầu từ customer (chỉ admin/staff mới gọi được)
     */
    public function createCustomerRequest(int $bookingId, string $content, int $customerId): BookingNote
    {
        $data = [
            'booking_id' => $bookingId,
            'user_id' => $customerId,
            'content' => $content,
            'type' => 'customer',
            'visibility' => 'public',
            'is_internal' => false
        ];

        return $this->bookingNoteRepository->create($data);
    }

    /**
     * Tạo phản hồi từ admin/staff cho customer
     */
    public function createAdminResponse(int $bookingId, string $content, string $type = 'admin'): BookingNote
    {
        $data = [
            'booking_id' => $bookingId,
            'user_id' => Auth::id(),
            'content' => $content,
            'type' => $type,
            'visibility' => 'public',
            'is_internal' => false
        ];

        return $this->bookingNoteRepository->create($data);
    }

    /**
     * Lấy ghi chú gần đây nhất
     */
    public function getRecentNotes(int $bookingId, int $limit = 5): Collection
    {
        return $this->bookingNoteRepository->getByBookingId($bookingId)
            ->take($limit);
    }

    /**
     * Lấy ghi chú theo ngày
     */
    public function getNotesByDate(int $bookingId, string $date): Collection
    {
        return $this->bookingNoteRepository->getByBookingId($bookingId)
            ->filter(function($note) use ($date) {
                return $note->created_at->format('Y-m-d') === $date;
            });
    }

    /**
     * Lấy ghi chú theo khoảng thời gian
     */
    public function getNotesByDateRange(int $bookingId, string $startDate, string $endDate): Collection
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        return $this->bookingNoteRepository->getByBookingId($bookingId)
            ->filter(function($note) use ($start, $end) {
                return $note->created_at->between($start, $end);
            });
    }
} 