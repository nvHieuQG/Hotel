<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\RoomChange;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RoomChangeSettlementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Booking $booking,
        public RoomChange $roomChange
    ) {}

    public function build()
    {
        $difference = (float) ($this->roomChange->price_difference ?? 0);
        $isRefund = $difference < 0;
        $absAmount = abs($difference);

        $subject = $isRefund
            ? 'Thông báo hoàn tiền do đổi phòng - Booking #' . ($this->booking->booking_id)
            : 'Thông báo chênh lệch cần thanh toán do đổi phòng - Booking #' . ($this->booking->booking_id);

        return $this->subject($subject)
            ->view('emails.room-change-settlement')
            ->with([
                'booking' => $this->booking,
                'roomChange' => $this->roomChange,
                'isRefund' => $isRefund,
                'amount' => $absAmount,
            ]);
    }
}


