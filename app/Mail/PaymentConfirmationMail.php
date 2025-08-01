<?php

namespace App\Mail;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $payment;
    public $user;

    /**
     * Create a new message instance.
     *
     * @param Booking $booking
     * @param Payment $payment
     * @return void
     */
    public function __construct(Booking $booking, Payment $payment)
    {
        $this->booking = $booking;
        $this->payment = $payment;
        $this->user = $booking->user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $subject = 'Xác nhận thanh toán thành công - ' . $this->booking->booking_id;

        return $this->subject($subject)
            ->view('emails.payment-confirmation');
    }
}
