<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TourBooking;

class TourVatInvoiceMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tourBooking;

    public function __construct(TourBooking $tourBooking)
    {
        $this->tourBooking = $tourBooking;
    }

    public function build()
    {
        return $this->view('emails.tour-vat-invoice-request')
                    ->subject('Yêu cầu xuất hóa đơn VAT - Tour Booking #' . $this->tourBooking->booking_id);
    }
}

