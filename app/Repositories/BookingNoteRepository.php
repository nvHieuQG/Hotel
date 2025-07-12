<?php

namespace App\Repositories;

use App\Models\BookingNote;
use App\Interfaces\Repositories\BookingNoteRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BookingNoteRepository implements BookingNoteRepositoryInterface
{
    protected $model;

    public function __construct(BookingNote $model)
    {
        $this->model = $model;
    }

    /**
     * Lấy tất cả ghi chú của một booking
     *
     * @param int $bookingId
     * @return Collection
     */
    public function getByBookingId(int $bookingId): Collection
    {
        return $this->model->where('booking_id', $bookingId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy ghi chú công khai của một booking
     *
     * @param int $bookingId
     * @return Collection
     */
    public function getPublicNotesByBookingId(int $bookingId): Collection
    {
        return $this->model->where('booking_id', $bookingId)
            ->where('visibility', 'public')
            ->where('is_internal', false)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy ghi chú nội bộ của một booking
     *
     * @param int $bookingId
     * @return Collection
     */
    public function getInternalNotesByBookingId(int $bookingId): Collection
    {
        return $this->model->where('booking_id', $bookingId)
            ->where(function($query) {
                $query->where('visibility', 'internal')
                      ->orWhere('is_internal', true);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy ghi chú mà user có thể xem
     *
     * @param int $bookingId
     * @param int $userId
     * @param array $userRoles
     * @return Collection
     */
    public function getVisibleNotesByBookingId(int $bookingId, int $userId, array $userRoles = []): Collection
    {
        $query = $this->model->where('booking_id', $bookingId)
            ->with('user');

        // Nếu là admin, lấy tất cả
        if (in_array('admin', $userRoles)) {
            return $query->orderBy('created_at', 'desc')->get();
        }

        // Nếu là staff, lấy ghi chú công khai và nội bộ
        if (in_array('staff', $userRoles)) {
            return $query->where(function($q) {
                $q->where('visibility', 'public')
                  ->orWhere('visibility', 'internal')
                  ->orWhere('is_internal', true);
            })->orderBy('created_at', 'desc')->get();
        }

        // Nếu là customer, chỉ lấy ghi chú công khai và riêng tư của mình
        return $query->where(function($q) use ($userId) {
            $q->where('visibility', 'public')
              ->orWhere(function($subQ) use ($userId) {
                  $subQ->where('visibility', 'private')
                       ->where('user_id', $userId);
              });
        })->where('is_internal', false)
          ->where('visibility', '!=', 'internal') // Customer không xem được ghi chú internal
          ->orderBy('created_at', 'desc')
          ->get();
    }

    /**
     * Tạo ghi chú mới
     *
     * @param array $data
     * @return BookingNote
     */
    public function create(array $data): BookingNote
    {
        return $this->model->create($data);
    }

    /**
     * Cập nhật ghi chú
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function update(int $id, array $data): bool
    {
        $note = $this->model->find($id);
        if (!$note) {
            return false;
        }
        return $note->update($data);
    }

    /**
     * Xóa ghi chú
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }

    /**
     * Tìm ghi chú theo ID
     *
     * @param int $id
     * @return BookingNote|null
     */
    public function findById(int $id): ?BookingNote
    {
        return $this->model->with('user', 'booking')->find($id);
    }

    /**
     * Kiểm tra xem user có thể xem ghi chú không
     *
     * @param int $noteId
     * @param int $userId
     * @param array $userRoles
     * @return bool
     */
    public function canUserViewNote(int $noteId, int $userId, array $userRoles = []): bool
    {
        $note = $this->model->find($noteId);
        if (!$note) {
            return false;
        }

        // Admin có thể xem tất cả
        if (in_array('admin', $userRoles)) {
            return true;
        }

        // Ghi chú nội bộ chỉ admin xem được
        if ($note->is_internal) {
            return false;
        }

        // Ghi chú công khai ai cũng xem được
        if ($note->visibility === 'public') {
            return true;
        }

        // Ghi chú riêng tư chỉ người tạo xem được
        if ($note->visibility === 'private') {
            return $note->user_id === $userId;
        }

        // Ghi chú nội bộ chỉ staff và admin xem được
        if ($note->visibility === 'internal') {
            return in_array('admin', $userRoles) || in_array('staff', $userRoles);
        }

        return false;
    }

    /**
     * Kiểm tra xem user có thể chỉnh sửa ghi chú không
     *
     * @param int $noteId
     * @param int $userId
     * @param array $userRoles
     * @return bool
     */
    public function canUserEditNote(int $noteId, int $userId, array $userRoles = []): bool
    {
        $note = $this->model->find($noteId);
        if (!$note) {
            return false;
        }

        // Admin có thể chỉnh sửa tất cả
        if (in_array('admin', $userRoles)) {
            return true;
        }

        // Người tạo ghi chú có thể chỉnh sửa (trừ ghi chú nội bộ)
        if ($note->user_id === $userId && !$note->is_internal) {
            return true;
        }

        return false;
    }

    /**
     * Kiểm tra xem user có thể xóa ghi chú không
     *
     * @param int $noteId
     * @param int $userId
     * @param array $userRoles
     * @return bool
     */
    public function canUserDeleteNote(int $noteId, int $userId, array $userRoles = []): bool
    {
        $note = $this->model->find($noteId);
        if (!$note) {
            return false;
        }

        // Admin có thể xóa tất cả
        if (in_array('admin', $userRoles)) {
            return true;
        }

        // Người tạo ghi chú có thể xóa (trừ ghi chú nội bộ)
        if ($note->user_id === $userId && !$note->is_internal) {
            return true;
        }

        return false;
    }

    /**
     * Lấy ghi chú theo loại
     *
     * @param int $bookingId
     * @param string $type
     * @return Collection
     */
    public function getByType(int $bookingId, string $type): Collection
    {
        return $this->model->where('booking_id', $bookingId)
            ->where('type', $type)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy ghi chú theo visibility
     *
     * @param int $bookingId
     * @param string $visibility
     * @return Collection
     */
    public function getByVisibility(int $bookingId, string $visibility): Collection
    {
        return $this->model->where('booking_id', $bookingId)
            ->where('visibility', $visibility)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy ghi chú có phân trang
     *
     * @param int $bookingId
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $bookingId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->model->where('booking_id', $bookingId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Tìm kiếm ghi chú theo từ khóa
     *
     * @param int $bookingId
     * @param string $keyword
     * @return Collection
     */
    public function search(int $bookingId, string $keyword): Collection
    {
        return $this->model->where('booking_id', $bookingId)
            ->where('content', 'like', '%' . $keyword . '%')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy thống kê ghi chú của booking
     *
     * @param int $bookingId
     * @return array
     */
    public function getStatistics(int $bookingId): array
    {
        $totalNotes = $this->model->where('booking_id', $bookingId)->count();
        $customerNotes = $this->model->where('booking_id', $bookingId)->where('type', 'customer')->count();
        $staffNotes = $this->model->where('booking_id', $bookingId)->where('type', 'staff')->count();
        $adminNotes = $this->model->where('booking_id', $bookingId)->where('type', 'admin')->count();
        $internalNotes = $this->model->where('booking_id', $bookingId)->where('is_internal', true)->count();
        $publicNotes = $this->model->where('booking_id', $bookingId)->where('visibility', 'public')->count();
        $privateNotes = $this->model->where('booking_id', $bookingId)->where('visibility', 'private')->count();

        return [
            'total' => $totalNotes,
            'by_type' => [
                'customer' => $customerNotes,
                'staff' => $staffNotes,
                'admin' => $adminNotes
            ],
            'by_visibility' => [
                'public' => $publicNotes,
                'private' => $privateNotes,
                'internal' => $internalNotes
            ]
        ];
    }
} 