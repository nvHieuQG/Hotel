<?php

namespace App\Interfaces\Repositories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;

interface ReviewRepositoryInterface
{
    /**
     * Lấy tất cả reviews
     */
    public function getAllReviews(): Collection;

    /**
     * Lấy reviews theo phòng
     */
    public function getReviewsByRoom(int $roomId, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /**
     * Lấy reviews theo user
     */
    public function getReviewsByUser(int $userId, int $perPage = 10): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /**
     * Lấy review theo ID
     */
    public function getReviewById(int $id): ?Review;

    /**
     * Tạo review mới
     */
    public function createReview(array $data): Review;

    /**
     * Cập nhật review
     */
    public function updateReview(int $id, array $data): bool;

    /**
     * Xóa review
     */
    public function deleteReview(int $id): bool;

    /**
     * Lấy reviews cho admin với filter
     */
    public function getAllReviewsForAdmin(array $filters = [], int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator;

    /**
     * Lấy thống kê reviews
     */
    public function getReviewStatistics(): array;

    /**
     * Duyệt review
     */
    public function approveReview(int $id): bool;

    /**
     * Từ chối review
     */
    public function rejectReview(int $id): bool;

    /**
     * Kiểm tra xem user đã đánh giá phòng này chưa
     */
    public function hasUserReviewedRoom(int $userId, int $roomId): bool;

    /**
     * Lấy rating trung bình của phòng
     */
    public function getAverageRatingByRoom(int $roomId): float;

    /**
     * Lấy số lượng reviews của phòng
     */
    public function getReviewsCountByRoom(int $roomId): int;

    /**
     * Lấy top phòng được đánh giá nhiều nhất
     */
    public function getTopRatedRooms(int $limit = 10): Collection;
} 