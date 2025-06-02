<?php

namespace App\Interfaces\Services;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;

interface BookingServiceInterface
{
    /**
     * Lấy thông tin trang đặt phòng
     *
     * @return array
     */
    public function getBookingPageData(): array;
    
    /**
     * Tạo đặt phòng mới
     *
     * @param array $data
     * @return Booking
     */
    public function createBooking(array $data): Booking;
    
    /**
     * Lấy danh sách đặt phòng của người dùng hiện tại
     *
     * @return Collection
     */
    public function getCurrentUserBookings(): Collection;
    
    /**
     * Hủy đặt phòng
     *
     * @param int $bookingId
     * @return bool
     */
    public function cancelBooking(int $bookingId): bool;
} 