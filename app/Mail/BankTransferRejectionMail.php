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

class BankTransferRejectionMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tourBooking;
    public $payment;
    public $rejectionReason;

    /**
     * Create a new message instance.
     */
    public function __construct(TourBooking $tourBooking, Payment $payment, string $rejectionReason)
    {
        $this->tourBooking = $tourBooking;
        $this->payment = $payment;
        $this->rejectionReason = $rejectionReason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Thông báo từ chối chuyển khoản - Tour Booking #' . $this->tourBooking->booking_id,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.bank-transfer-rejection',
            with: [
                'tourBooking' => $this->tourBooking,
                'payment' => $this->payment,
                'rejectionReason' => $this->rejectionReason,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
