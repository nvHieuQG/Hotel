<?php

namespace App\Repositories;

use App\Interfaces\Repositories\TourBookingRepositoryInterface;
use App\Models\TourBooking;

class TourBookingRepository implements TourBookingRepositoryInterface
{
    /**
     * Tạo tour booking mới
     */
    public function create(array $data)
    {
        return TourBooking::create($data);
    }

    /**
     * Lấy tour booking theo ID
     */
    public function findById($id)
    {
        return TourBooking::with(['user', 'tourBookingRooms.roomType'])->find($id);
    }

    /**
     * Lấy tour booking theo booking ID
     */
    public function findByBookingId($bookingId)
    {
        return TourBooking::with(['user', 'tourBookingRooms.roomType'])->where('booking_id', $bookingId)->first();
    }

    /**
     * Lấy danh sách tour bookings của user
     */
    public function getByUserId($userId)
    {
        return TourBooking::with(['tourBookingRooms.roomType'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Cập nhật tour booking
     */
    public function update($id, array $data)
    {
        $tourBooking = TourBooking::find($id);
        if ($tourBooking) {
            $tourBooking->update($data);
            return $tourBooking;
        }
        return null;
    }

    /**
     * Xóa tour booking
     */
    public function delete($id)
    {
        $tourBooking = TourBooking::find($id);
        if ($tourBooking) {
            return $tourBooking->delete();
        }
        return false;
    }

    /**
     * Lấy tất cả tour bookings
     */
    public function getAll()
    {
        return TourBooking::with(['user', 'tourBookingRooms.roomType'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Lấy tour bookings theo trạng thái
     */
    public function getByStatus($status)
    {
        return TourBooking::with(['user', 'tourBookingRooms.roomType'])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
