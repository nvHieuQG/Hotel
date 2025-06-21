<?php

namespace App\Http\Controllers;

use App\Interfaces\Services\BookingServiceInterface;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $bookingService;

    public function __construct(BookingServiceInterface $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    public function confirmPayment($id)
    {
        try {
            $booking = $this->bookingService->getBookingDetail($id);
            return view('client.payment', compact('booking'));
        } catch (\Exception $e) {
            return redirect()->route('index')->withErrors(['message' => $e->getMessage()]);
        }
    }

}
