<?php

namespace App\Interfaces\Repositories;

interface TourBookingRepositoryInterface
{
    /**
     * Tạo tour booking mới
     */
    public function create(array $data);

    /**
     * Lấy tour booking theo ID
     */
    public function findById($id);

    /**
     * Lấy tour booking theo booking ID
     */
    public function findByBookingId($bookingId);

    /**
     * Lấy danh sách tour bookings của user
     */
    public function getByUserId($userId);

    /**
     * Cập nhật tour booking
     */
    public function update($id, array $data);

    /**
     * Xóa tour booking
     */
    public function delete($id);

    /**
     * Lấy tất cả tour bookings
     */
    public function getAll();

    /**
     * Lấy tour bookings theo trạng thái
     */
    public function getByStatus($status);
}
