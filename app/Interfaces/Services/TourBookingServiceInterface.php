<?php

namespace App\Interfaces\Services;

interface TourBookingServiceInterface
{
    /**
     * Tạo tour booking mới
     */
    public function createTourBooking(array $data);

    /**
     * Lấy danh sách tour bookings của user
     */
    public function getUserTourBookings($userId);

    /**
     * Lấy chi tiết tour booking
     */
    public function getTourBookingById($id);

    /**
     * Lấy chi tiết tour booking theo booking ID
     */
    public function getTourBookingByBookingId($bookingId);

    /**
     * Cập nhật trạng thái tour booking
     */
    public function updateTourBookingStatus($id, $status);

    /**
     * Tính toán giá tour booking
     */
    public function calculateTourBookingPrice(array $roomSelections, $checkInDate, $checkOutDate);

    /**
     * Kiểm tra tính khả dụng của phòng cho tour
     */
    public function checkRoomAvailabilityForTour($roomTypeId, $quantity, $checkInDate, $checkOutDate);

    /**
     * Lấy danh sách loại phòng có sẵn cho tour
     */
    public function getAvailableRoomTypesForTour($checkInDate, $checkOutDate, $totalGuests);

    public function processCreditCardPayment(\Illuminate\Http\Request $request, \App\Models\TourBooking $tourBooking): array;
    public function processBankTransferPayment(\Illuminate\Http\Request $request, \App\Models\TourBooking $tourBooking): array;
    public function createBankTransferPaymentFromSession(array $tempPaymentData, \App\Models\TourBooking $tourBooking): \App\Models\Payment;
}
