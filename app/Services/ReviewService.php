<?php

namespace App\Services;

use App\Interfaces\Repositories\ReviewRepositoryInterface;
use App\Models\Review;
use App\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class ReviewService
{
    protected $reviewRepository;

    public function __construct(ReviewRepositoryInterface $reviewRepository)
    {
        $this->reviewRepository = $reviewRepository;
    }

    /**
     * Tạo review mới cho admin
     */
    public function createReview(array $data): Review
    {
        $reviewData = [
            'user_id' => $data['user_id'],
            'room_id' => $data['room_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_anonymous' => $data['is_anonymous'] ?? false,
            'status' => $data['status'] ?? 'pending',
        ];

        // Thêm booking_id nếu có
        if (isset($data['booking_id']) && $data['booking_id']) {
            $reviewData['booking_id'] = $data['booking_id'];
        }

        return $this->reviewRepository->createReview($reviewData);
    }

    /**
     * Tạo review mới
     */
    public function createReviewFromBooking(array $data, int $bookingId): Review
    {
        $booking = Booking::where('id', $bookingId)
            ->where('user_id', Auth::id())
            ->where('status', 'completed')
            ->whereDoesntHave('review')
            ->firstOrFail();

        $reviewData = [
            'user_id' => Auth::id(),
            'booking_id' => $booking->id,
            'room_id' => $booking->room_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'is_anonymous' => isset($data['is_anonymous']) && $data['is_anonymous'] == '1',
            'status' => 'pending',
        ];

        return $this->reviewRepository->createReview($reviewData);
    }

    /**
     * Cập nhật review
     */
    public function updateReview(int $reviewId, array $data): bool
    {
        $review = $this->reviewRepository->getReviewById($reviewId);
        
        if (!$review || $review->user_id !== Auth::id()) {
            throw new \Exception('Không tìm thấy review hoặc không có quyền chỉnh sửa.');
        }

        if (!$this->canBeEdited($review)) {
            throw new \Exception('Không thể chỉnh sửa đánh giá này.');
        }

        return $this->reviewRepository->updateReview($reviewId, $data);
    }

    /**
     * Xóa review (Admin)
     */
    public function deleteReviewForAdmin(int $reviewId): bool
    {
        $review = $this->reviewRepository->getReviewById($reviewId);
        
        if (!$review) {
            throw new \Exception('Không tìm thấy review.');
        }

        return $this->reviewRepository->deleteReview($reviewId);
    }

    /**
     * Xóa review
     */
    public function deleteReview(int $reviewId): bool
    {
        $review = $this->reviewRepository->getReviewById($reviewId);
        
        if (!$review || $review->user_id !== Auth::id()) {
            throw new \Exception('Không tìm thấy review hoặc không có quyền xóa.');
        }

        if (!$this->canBeDeleted($review)) {
            throw new \Exception('Không thể xóa đánh giá này.');
        }

        return $this->reviewRepository->deleteReview($reviewId);
    }

    /**
     * Lấy review theo ID
     */
    public function getReviewById(int $id): ?Review
    {
        return $this->reviewRepository->getReviewById($id);
    }

    /**
     * Lấy reviews của user
     */
    public function getUserReviews(int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviewRepository->getReviewsByUser(Auth::id(), $perPage);
    }

    /**
     * Lấy reviews của phòng
     */
    public function getRoomReviews(int $roomId, int $perPage = 10): LengthAwarePaginator
    {
        return $this->reviewRepository->getReviewsByRoom($roomId, $perPage);
    }

    /**
     * Lấy tất cả reviews cho admin
     */
    public function getAllReviewsForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->reviewRepository->getAllReviewsForAdmin($filters, $perPage);
    }

    /**
     * Duyệt review (Admin)
     */
    public function approveReview(int $reviewId): bool
    {
        return $this->reviewRepository->approveReview($reviewId);
    }

    /**
     * Từ chối review (Admin)
     */
    public function rejectReview(int $reviewId): bool
    {
        return $this->reviewRepository->rejectReview($reviewId);
    }

    /**
     * Lấy thống kê reviews
     */
    public function getReviewStatistics(): array
    {
        return $this->reviewRepository->getReviewStatistics();
    }

    /**
     * Kiểm tra xem booking có thể đánh giá không
     */
    public function canBeReviewed(Booking $booking): bool
    {
        return $booking->isCompleted() && !$booking->review;
    }

    /**
     * Kiểm tra xem review có thể chỉnh sửa không
     */
    public function canBeEdited(Review $review): bool
    {
        return $review->status === 'pending';
    }

    /**
     * Kiểm tra xem review có thể xóa không
     */
    public function canBeDeleted(Review $review): bool
    {
        return $review->status === 'pending';
    }

    /**
     * Lấy rating trung bình của phòng
     */
    public function getRoomAverageRating(int $roomId): float
    {
        return $this->reviewRepository->getAverageRatingByRoom($roomId);
    }

    /**
     * Lấy số lượng reviews của phòng
     */
    public function getRoomReviewsCount(int $roomId): int
    {
        return $this->reviewRepository->getReviewsCountByRoom($roomId);
    }

    /**
     * Validate review data
     */
    public function validateReviewData(array $data): array
    {
        $rules = [
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'is_anonymous' => ['boolean'],
        ];

        $messages = [
            'rating.required' => 'Vui lòng chọn điểm đánh giá.',
            'rating.min' => 'Điểm đánh giá phải từ 1-5.',
            'rating.max' => 'Điểm đánh giá phải từ 1-5.',
            'comment.max' => 'Bình luận không được quá 1000 ký tự.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first());
        }

        return $validator->validated();
    }
} 