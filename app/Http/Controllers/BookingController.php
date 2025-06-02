<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Services\BookingServiceInterface;

class BookingController extends Controller
{
    protected $bookingService;

    public function __construct(BookingServiceInterface $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Hiển thị trang đặt phòng
     */
    public function booking()
    {
        try {
            $data = $this->bookingService->getBookingPageData();
            return view('client.booking', $data);
        } catch (\Exception $e) {
            return redirect()->route('verification.notice')
                ->with('warning', $e->getMessage());
        }
    }

    /**
     * Lưu thông tin đặt phòng
     */
    public function storeBooking(Request $request)
    {
        try {
            $booking = $this->bookingService->createBooking($request->all());
            
            return redirect()->route('index')
                ->with('success', 'Đặt phòng thành công! Mã đặt phòng của bạn là: ' . $booking->id);
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()])->withInput();
        }
    }
    
    /**
     * Hiển thị danh sách đặt phòng của người dùng
     */
    public function myBookings()
    {
        $bookings = $this->bookingService->getCurrentUserBookings();
        
        return view('client.my-bookings', compact('bookings'));
    }
    
    /**
     * Hủy đặt phòng
     */
    public function cancelBooking($id)
    {
        try {
            $this->bookingService->cancelBooking($id);
            
            return back()->with('success', 'Đã hủy đặt phòng thành công.');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }
} 