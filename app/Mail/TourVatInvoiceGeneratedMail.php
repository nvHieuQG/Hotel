<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\TourBooking;

class TourVatInvoiceGeneratedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $tourBooking;

    public function __construct(TourBooking $tourBooking)
    {
        $this->tourBooking = $tourBooking;
    }

    public function build()
    {
        return $this->view('emails.tour-vat-invoice-generated')
                    ->subject('Hóa đơn VAT đã được tạo - Tour Booking #' . $this->tourBooking->booking_id);
    }
}
