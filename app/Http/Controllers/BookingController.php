<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Interfaces\Services\BookingServiceInterface;
use Illuminate\Support\Facades\Auth;

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
            return view('client.booking.index', $data);
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
            // Chuyển hướng đến trang thanh toán với ID đặt phòng
            return redirect()->route('confirm-info-payment', ['booking' => $booking->id]);
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

        return view('client.profile.bookings.index', compact('bookings'));
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

    /**
     * Hiển thị chi tiết booking cho người dùng
     */
    public function showDetail($id)
    {
        $booking = $this->bookingService->getBookingDetail($id);
        // Kiểm tra quyền truy cập: chỉ cho phép user xem booking của chính mình
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem đặt phòng này.');
        }
        return view('client.booking.detail', compact('booking'));
    }

    /**
     * Trả về partial danh sách đặt phòng cho AJAX
     */
    public function partial()
    {
        $bookings = $this->bookingService->getCurrentUserBookings();
        return view('client.profile.bookings.partial', compact('bookings'));
    }

    /**
     * Trả về partial chi tiết booking cho AJAX/modal (client)
     */
    public function detailPartial($id)
    {
        $booking = $this->bookingService->getBookingDetail($id);
        if ($booking->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền xem đặt phòng này.');
        }
        return view('client.profile.bookings.detail', compact('booking'));
    }
}