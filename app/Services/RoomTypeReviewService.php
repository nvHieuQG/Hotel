<?php

namespace App\Services;

use App\Interfaces\Repositories\RoomTypeReviewRepositoryInterface;
use App\Models\RoomTypeReview;
use App\Models\RoomType;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class RoomTypeReviewService
{
    protected $roomTypeReviewRepository;

    public function __construct(RoomTypeReviewRepositoryInterface $roomTypeReviewRepository)
    {
        $this->roomTypeReviewRepository = $roomTypeReviewRepository;
    }

    /**
     * Tạo review mới cho admin
     */
    public function createReview(array $data, int $userId = null, int $roomTypeId = null): RoomTypeReview
    {
        $reviewData = [
            'user_id' => $userId ?? $data['user_id'],
            'room_type_id' => $roomTypeId ?? $data['room_type_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'cleanliness_rating' => $data['cleanliness_rating'] ?? null,
            'comfort_rating' => $data['comfort_rating'] ?? null,
            'location_rating' => $data['location_rating'] ?? null,
            'facilities_rating' => $data['facilities_rating'] ?? null,
            'value_rating' => $data['value_rating'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'status' => $data['status'] ?? 'pending',
        ];

        return $this->roomTypeReviewRepository->createReview($reviewData);
    }

    /**
     * Tạo review mới cho user
     */
    public function createReviewForUser(array $data, int $roomTypeId): RoomTypeReview
    {
        // Kiểm tra xem user đã đánh giá loại phòng này chưa
        if ($this->roomTypeReviewRepository->hasUserReviewedRoomType(Auth::id(), $roomTypeId)) {
            throw new \Exception('Bạn đã đánh giá loại phòng này rồi.');
        }

        // Kiểm tra xem user đã có booking hoàn thành cho loại phòng này chưa
        $hasCompletedBooking = \App\Models\Booking::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->whereHas('room', function($query) use ($roomTypeId) {
                $query->where('room_type_id', $roomTypeId);
            })
            ->exists();

        if (!$hasCompletedBooking) {
            throw new \Exception('Bạn chưa có booking hoàn thành cho loại phòng này.');
        }

        $reviewData = [
            'user_id' => Auth::id(),
            'room_type_id' => $roomTypeId,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'cleanliness_rating' => $data['cleanliness_rating'] ?? null,
            'comfort_rating' => $data['comfort_rating'] ?? null,
            'location_rating' => $data['location_rating'] ?? null,
            'facilities_rating' => $data['facilities_rating'] ?? null,
            'value_rating' => $data['value_rating'] ?? null,
            'is_anonymous' => isset($data['is_anonymous']) && $data['is_anonymous'] == '1',
            'status' => 'approved',
        ];

        return $this->roomTypeReviewRepository->createReview($reviewData);
    }

    /**
     * Cập nhật review
     */
    public function updateReview(int $reviewId, array $data): bool
    {
        $review = $this->roomTypeReviewRepository->getReviewById($reviewId);
        
        if (!$review || $review->user_id !== Auth::id()) {
            throw new \Exception('Không tìm thấy review hoặc không có quyền chỉnh sửa.');
        }

        if (!$this->canBeEdited($review)) {
            throw new \Exception('Không thể chỉnh sửa đánh giá này.');
        }

        return $this->roomTypeReviewRepository->updateReview($reviewId, $data);
    }

    /**
     * Xóa review (Admin)
     */
    public function deleteReviewForAdmin(int $reviewId): bool
    {
        $review = $this->roomTypeReviewRepository->getReviewById($reviewId);
        
        if (!$review) {
            throw new \Exception('Không tìm thấy review.');
        }

        return $this->roomTypeReviewRepository->deleteReview($reviewId);
    }

    /**
     * Xóa review
     */
    public function deleteReview(int $reviewId): bool
    {
        $review = $this->roomTypeReviewRepository->getReviewById($reviewId);
        
        if (!$review || $review->user_id !== Auth::id()) {
            throw new \Exception('Không tìm thấy review hoặc không có quyền xóa.');
        }

        if (!$this->canBeDeleted($review)) {
            throw new \Exception('Không thể xóa đánh giá này.');
        }

        return $this->roomTypeReviewRepository->deleteReview($reviewId);
    }

    /**
     * Lấy review theo ID
     */
    public function getReviewById(int $id): ?RoomTypeReview
    {
        return $this->roomTypeReviewRepository->getReviewById($id);
    }

    /**
     * Lấy reviews của user
     */
    public function getUserReviews(int $perPage = 10): LengthAwarePaginator
    {
        return $this->roomTypeReviewRepository->getReviewsByUser(Auth::id(), $perPage);
    }

    /**
     * Lấy reviews của loại phòng
     */
    public function getRoomTypeReviews(int $roomTypeId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->roomTypeReviewRepository->getReviewsByRoomType($roomTypeId, $perPage);
    }

    /**
     * Lấy tất cả reviews cho admin
     */
    public function getAllReviewsForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->roomTypeReviewRepository->getAllReviewsForAdmin($filters, $perPage);
    }

    /**
     * Duyệt review (Admin)
     */
    public function approveReview(int $reviewId): bool
    {
        return $this->roomTypeReviewRepository->approveReview($reviewId);
    }

    /**
     * Từ chối review (Admin)
     */
    public function rejectReview(int $reviewId): bool
    {
        return $this->roomTypeReviewRepository->rejectReview($reviewId);
    }

    /**
     * Lấy thống kê reviews
     */
    public function getReviewStatistics(): array
    {
        return $this->roomTypeReviewRepository->getReviewStatistics();
    }

    /**
     * Kiểm tra xem review có thể chỉnh sửa không
     */
    public function canBeEdited(RoomTypeReview $review): bool
    {
        return $review->status === 'pending';
    }

    /**
     * Kiểm tra xem review có thể xóa không
     */
    public function canBeDeleted(RoomTypeReview $review): bool
    {
        return $review->status === 'pending';
    }

    /**
     * Lấy rating trung bình của loại phòng
     */
    public function getRoomTypeAverageRating(int $roomTypeId): float
    {
        return $this->roomTypeReviewRepository->getAverageRatingByRoomType($roomTypeId);
    }

    /**
     * Lấy số lượng reviews của loại phòng
     */
    public function getRoomTypeReviewsCount(int $roomTypeId): int
    {
        return $this->roomTypeReviewRepository->getReviewsCountByRoomType($roomTypeId);
    }

    /**
     * Lấy các loại phòng mà user có thể đánh giá
     */
    public function getReviewableRoomTypes(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->roomTypeReviewRepository->getCompletedRoomTypesWithoutReview(Auth::id());
    }

    /**
     * Kiểm tra xem user có thể đánh giá loại phòng này không
     */
    public function canReviewRoomType(int $roomTypeId): bool
    {
        // Kiểm tra xem user đã đăng nhập chưa
        if (!Auth::check()) {
            return false;
        }
        
        // Kiểm tra xem user đã đánh giá chưa
        if ($this->roomTypeReviewRepository->hasUserReviewedRoomType(Auth::id(), $roomTypeId)) {
            return false;
        }

        // Kiểm tra xem user đã có booking hoàn thành cho loại phòng này chưa
        return \App\Models\Booking::where('user_id', Auth::id())
            ->where('status', 'completed')
            ->whereHas('room', function($query) use ($roomTypeId) {
                $query->where('room_type_id', $roomTypeId);
            })
            ->exists();
    }

    /**
     * Kiểm tra xem user đã đánh giá loại phòng này chưa
     */
    public function hasUserReviewedRoomType(int $userId, int $roomTypeId): bool
    {
        return $this->roomTypeReviewRepository->hasUserReviewedRoomType($userId, $roomTypeId);
    }

    /**
     * Validate review data
     */
    public function validateReviewData(array $data): array
    {
        $rules = [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'cleanliness_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'comfort_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'location_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'facilities_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'value_rating' => ['nullable', 'integer', 'min:1', 'max:5'],
            'is_anonymous' => ['boolean'],
            'status' => ['required', 'in:pending,approved,rejected'],
        ];

        $messages = [
            'user_id.required' => 'Vui lòng chọn người đánh giá.',
            'user_id.exists' => 'Người dùng không tồn tại.',
            'room_type_id.required' => 'Vui lòng chọn loại phòng.',
            'room_type_id.exists' => 'Loại phòng không tồn tại.',
            'rating.required' => 'Vui lòng chọn điểm đánh giá.',
            'rating.min' => 'Điểm đánh giá phải từ 1-5.',
            'rating.max' => 'Điểm đánh giá phải từ 1-5.',
            'comment.max' => 'Bình luận không được quá 1000 ký tự.',
            'status.required' => 'Vui lòng chọn trạng thái.',
            'status.in' => 'Trạng thái không hợp lệ.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return $validator->validated();
    }
} 