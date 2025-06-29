<?php

namespace App\Repositories;

use App\Interfaces\Repositories\ReviewRepositoryInterface;
use App\Models\Review;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * Lấy tất cả reviews
     */
    public function getAllReviews(): Collection
    {
        return Review::with(['user', 'room'])->get();
    }

    /**
     * Lấy reviews theo phòng
     */
    public function getReviewsByRoom(int $roomId, int $perPage = 10): LengthAwarePaginator
    {
        return Review::where('room_id', $roomId)
            ->where('status', 'approved')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Lấy reviews theo user
     */
    public function getReviewsByUser(int $userId, int $perPage = 10): LengthAwarePaginator
    {
        return Review::where('user_id', $userId)
            ->with(['booking', 'room'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Lấy review theo ID
     */
    public function getReviewById(int $id): ?Review
    {
        return Review::find($id);
    }

    /**
     * Tạo review mới
     */
    public function createReview(array $data): Review
    {
        return Review::create($data);
    }

    /**
     * Cập nhật review
     */
    public function updateReview(int $id, array $data): bool
    {
        $review = Review::find($id);
        return $review ? $review->update($data) : false;
    }

    /**
     * Xóa review
     */
    public function deleteReview(int $id): bool
    {
        $review = Review::find($id);
        return $review ? $review->delete() : false;
    }

    /**
     * Lấy reviews cho admin với filter
     */
    public function getAllReviewsForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Review::with(['user', 'booking', 'room']);

        // Filter theo trạng thái
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Filter theo phòng
        if (isset($filters['room_id'])) {
            $query->where('room_id', $filters['room_id']);
        }

        // Filter theo rating
        if (isset($filters['rating'])) {
            $query->where('rating', $filters['rating']);
        }

        // Filter theo user
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Lấy thống kê reviews
     */
    public function getReviewStatistics(): array
    {
        $totalReviews = Review::count();
        $pendingReviews = Review::where('status', 'pending')->count();
        $approvedReviews = Review::where('status', 'approved')->count();
        $rejectedReviews = Review::where('status', 'rejected')->count();

        $averageRating = Review::where('status', 'approved')->avg('rating');
        $averageRating = round($averageRating, 1);

        // Thống kê theo rating
        $ratingStats = [];
        for ($i = 1; $i <= 5; $i++) {
            $ratingStats[$i] = Review::where('status', 'approved')
                ->where('rating', $i)
                ->count();
        }

        // Top phòng được đánh giá nhiều nhất
        $topRooms = \App\Models\Room::withCount(['reviews as approved_reviews_count' => function($query) {
            $query->where('status', 'approved');
        }])
        ->orderBy('approved_reviews_count', 'desc')
        ->limit(5)
        ->get();

        return [
            'total_reviews' => $totalReviews,
            'pending_reviews' => $pendingReviews,
            'approved_reviews' => $approvedReviews,
            'rejected_reviews' => $rejectedReviews,
            'average_rating' => $averageRating,
            'rating_stats' => $ratingStats,
            'top_rooms' => $topRooms
        ];
    }

    /**
     * Duyệt review
     */
    public function approveReview(int $id): bool
    {
        $review = Review::find($id);
        return $review ? $review->update(['status' => 'approved']) : false;
    }

    /**
     * Từ chối review
     */
    public function rejectReview(int $id): bool
    {
        $review = Review::find($id);
        return $review ? $review->update(['status' => 'rejected']) : false;
    }

    /**
     * Kiểm tra xem user đã đánh giá phòng này chưa
     */
    public function hasUserReviewedRoom(int $userId, int $roomId): bool
    {
        return Review::where('user_id', $userId)
            ->where('room_id', $roomId)
            ->exists();
    }

    /**
     * Lấy rating trung bình của phòng
     */
    public function getAverageRatingByRoom(int $roomId): float
    {
        $rating = Review::where('room_id', $roomId)
            ->where('status', 'approved')
            ->avg('rating');
        
        return $rating ? round($rating, 1) : 0;
    }

    /**
     * Lấy số lượng reviews của phòng
     */
    public function getReviewsCountByRoom(int $roomId): int
    {
        return Review::where('room_id', $roomId)
            ->where('status', 'approved')
            ->count();
    }

    /**
     * Lấy top phòng được đánh giá nhiều nhất
     */
    public function getTopRatedRooms(int $limit = 10): Collection
    {
        return \App\Models\Room::withCount(['reviews as approved_reviews_count' => function($query) {
            $query->where('status', 'approved');
        }])
        ->orderBy('approved_reviews_count', 'desc')
        ->limit($limit)
        ->get();
    }
} 