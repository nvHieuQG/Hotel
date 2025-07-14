<?php

namespace App\Observers;

use App\Models\BookingNote;
use App\Interfaces\Services\BookingServiceInterface;

class BookingNoteObserver
{
    protected $bookingService;

    public function __construct(BookingServiceInterface $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Handle the BookingNote "created" event.
     */
    public function created(BookingNote $bookingNote)
    {
        // Tạo thông báo admin khi có ghi chú mới
        // $this->bookingService->notifyBookingNoteCreated($bookingNote);
    }

    /**
     * Handle the BookingNote "updated" event.
     */
    public function updated(BookingNote $bookingNote)
    {
        // Tạo thông báo admin khi ghi chú được cập nhật
        // $this->bookingService->notifyBookingNoteUpdated($bookingNote);
    }

    /**
     * Handle the BookingNote "deleted" event.
     */
    public function deleted(BookingNote $bookingNote)
    {
        // Tạo thông báo admin khi ghi chú bị xóa
        // $this->bookingService->notifyBookingNoteDeleted($bookingNote);
    }

    /**
     * Handle the BookingNote "restored" event.
     */
    public function restored(BookingNote $bookingNote)
    {
        // Tạo thông báo admin khi ghi chú được khôi phục
        // $this->bookingService->notifyBookingNoteRestored($bookingNote);
    }

    /**
     * Handle the BookingNote "force deleted" event.
     */
    public function forceDeleted(BookingNote $bookingNote)
    {
        // Tạo thông báo admin khi ghi chú bị xóa vĩnh viễn
        // $this->bookingService->notifyBookingNoteForceDeleted($bookingNote);
    }
}
