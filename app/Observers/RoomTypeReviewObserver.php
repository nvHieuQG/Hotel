<?php

namespace App\Observers;

use App\Models\RoomTypeReview;
use App\Interfaces\Services\Admin\AdminBookingServiceInterface;

class RoomTypeReviewObserver
{
    protected $adminBookingService;

    public function __construct(AdminBookingServiceInterface $adminBookingService)
    {
        $this->adminBookingService = $adminBookingService;
    }

    /**
     * Handle the RoomTypeReview "created" event.
     */
    public function created(RoomTypeReview $roomTypeReview)
    {
        // Tạo thông báo admin khi có đánh giá mới
        // $this->adminBookingService->notifyRoomTypeReviewCreated($roomTypeReview);
    }

    /**
     * Handle the RoomTypeReview "updated" event.
     */
    public function updated(RoomTypeReview $roomTypeReview)
    {
        // Tạo thông báo admin khi đánh giá được cập nhật
        // $this->adminBookingService->notifyRoomTypeReviewUpdated($roomTypeReview);
    }

    /**
     * Handle the RoomTypeReview "deleted" event.
     */
    public function deleted(RoomTypeReview $roomTypeReview)
    {
        // Tạo thông báo admin khi đánh giá bị xóa
        // $this->adminBookingService->notifyRoomTypeReviewDeleted($roomTypeReview);
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
