<?php

namespace App\Mail;

use App\Models\TourBooking;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CreditCardRejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tourBooking;
    public $payment;
    public $rejectionReason;

    public function __construct(TourBooking $tourBooking, Payment $payment, string $rejectionReason)
    {
        $this->tourBooking = $tourBooking;
        $this->payment = $payment;
        $this->rejectionReason = $rejectionReason;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thông báo từ chối thanh toán bằng thẻ cho Tour Booking #' . $this->tourBooking->booking_id,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.credit-card-rejection',
        );
    }
}
