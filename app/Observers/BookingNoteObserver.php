<?php

namespace App\Observers;

use App\Models\BookingNote;
use App\Services\AdminNotificationService;

class BookingNoteObserver
{
    protected $adminNotificationService;

    public function __construct(AdminNotificationService $adminNotificationService)
    {
        $this->adminNotificationService = $adminNotificationService;
    }

    /**
     * Handle the BookingNote "created" event.
     */
    public function created(BookingNote $bookingNote): void
    {
        // Tạo thông báo admin khi có ghi chú mới
        $this->adminNotificationService->notifyBookingNoteCreated($bookingNote);
    }

    /**
     * Handle the BookingNote "updated" event.
     */
    public function updated(BookingNote $bookingNote): void
    {
        // Tạo thông báo admin khi ghi chú được cập nhật
        $this->adminNotificationService->notifyBookingNoteUpdated($bookingNote);
    }

    /**
     * Handle the BookingNote "deleted" event.
     */
    public function deleted(BookingNote $bookingNote): void
    {
        // Tạo thông báo admin khi ghi chú bị xóa
        $this->adminNotificationService->notifyBookingNoteDeleted($bookingNote);
    }

    /**
     * Handle the BookingNote "restored" event.
     */
    public function restored(BookingNote $bookingNote): void
    {
        // Tạo thông báo admin khi ghi chú được khôi phục
        $this->adminNotificationService->notifyBookingNoteRestored($bookingNote);
    }

    /**
     * Handle the BookingNote "force deleted" event.
     */
    public function forceDeleted(BookingNote $bookingNote): void
    {
        // Tạo thông báo admin khi ghi chú bị xóa vĩnh viễn
        $this->adminNotificationService->notifyBookingNoteForceDeleted($bookingNote);
    }
}
