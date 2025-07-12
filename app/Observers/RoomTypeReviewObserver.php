<?php

namespace App\Observers;

use App\Models\RoomTypeReview;
use App\Services\AdminNotificationService;

class RoomTypeReviewObserver
{
    protected $adminNotificationService;

    public function __construct(AdminNotificationService $adminNotificationService)
    {
        $this->adminNotificationService = $adminNotificationService;
    }

    /**
     * Handle the RoomTypeReview "created" event.
     */
    public function created(RoomTypeReview $roomTypeReview): void
    {
        // Tạo thông báo admin khi có đánh giá mới
        $this->adminNotificationService->notifyRoomTypeReviewCreated($roomTypeReview);
    }

    /**
     * Handle the RoomTypeReview "updated" event.
     */
    public function updated(RoomTypeReview $roomTypeReview): void
    {
        // Tạo thông báo admin khi đánh giá được cập nhật
        $this->adminNotificationService->notifyRoomTypeReviewUpdated($roomTypeReview);
    }

    /**
     * Handle the RoomTypeReview "deleted" event.
     */
    public function deleted(RoomTypeReview $roomTypeReview): void
    {
        // Tạo thông báo admin khi đánh giá bị xóa
        $this->adminNotificationService->notifyRoomTypeReviewDeleted($roomTypeReview);
    }

    /**
     * Handle the RoomTypeReview "restored" event.
     */
    public function restored(RoomTypeReview $roomTypeReview): void
    {
        // Có thể thêm thông báo khi đánh giá được khôi phục nếu cần
    }

    /**
     * Handle the RoomTypeReview "force deleted" event.
     */
    public function forceDeleted(RoomTypeReview $roomTypeReview): void
    {
        // Có thể thêm thông báo khi đánh giá bị xóa vĩnh viễn nếu cần
    }
}
