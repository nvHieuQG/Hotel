<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TourBooking;

class TourVatInvoiceRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tourBooking;
    public $rejectionReason;

    public function __construct(TourBooking $tourBooking, $rejectionReason)
    {
        $this->tourBooking = $tourBooking;
        $this->rejectionReason = $rejectionReason;
    }

    public function build()
    {
        return $this->view('emails.tour-vat-invoice-rejected')
                    ->subject('Yêu cầu hóa đơn VAT bị từ chối - Tour Booking #' . $this->tourBooking->booking_id);
    }
}
