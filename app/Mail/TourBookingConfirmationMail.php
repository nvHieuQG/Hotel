<?php

namespace App\Mail;

use App\Models\TourBooking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TourBookingConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tourBooking;
    public $user;

    /**
     * Create a new message instance.
     *
     * @param TourBooking $tourBooking
     * @return void
     */
    public function __construct(TourBooking $tourBooking)
    {
        $this->tourBooking = $tourBooking;
        $this->user = $tourBooking->user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Xác nhận đặt phòng tour thành công - ' . $this->tourBooking->booking_id;

        return $this->subject($subject)
            ->view('emails.tour-booking-confirmation');
    }
}
