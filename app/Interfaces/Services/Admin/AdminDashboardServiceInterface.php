<?php

namespace App\Interfaces\Services\Admin;

interface AdminDashboardServiceInterface
{
    /**
     * Lấy dữ liệu thống kê cho dashboard
     *
     * @return array
     */
    public function getDashboardStatistics(): array;
    
    /**
     * Lấy danh sách đặt phòng gần đây
     *
     * @param int $limit
     * @return array
     */
    public function getRecentBookings(int $limit = 5): array;
    
    /**
     * Lấy thống kê theo trạng thái đặt phòng
     *
     * @return array
     */
    public function getBookingStatusStatistics(): array;
    
    /**
     * Tính tỷ lệ đặt phòng (phòng đã đặt / tổng số phòng)
     *
     * @return int
     */
    public function calculateBookingRate(): int;
} 