<?php

namespace App\Interfaces\Services\Admin;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface AdminBookingServiceInterface
{
    /**
     * Lấy danh sách đặt phòng có phân trang
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getBookingsWithPagination(Request $request): LengthAwarePaginator;
    
    /**
     * Lấy chi tiết đặt phòng
     *
     * @param int $id
     * @return Booking
     */
    public function getBookingDetails(int $id): Booking;
    
    /**
     * Tạo đặt phòng mới
     *
     * @param array $data
     * @return Booking
     */
    public function createBooking(array $data): Booking;
    
    /**
     * Cập nhật đặt phòng
     *
     * @param int $id
     * @param array $data
     * @return Booking
     */
    public function updateBooking(int $id, array $data): Booking;
    
    /**
     * Xóa đặt phòng
     *
     * @param int $id
     * @return bool
     */
    public function deleteBooking(int $id): bool;
    
    /**
     * Cập nhật trạng thái đặt phòng
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateBookingStatus(int $id, string $status): bool;
    
    /**
     * Lấy dữ liệu cho dashboard
     *
     * @return array
     */
    public function getDashboardData(): array;
    
    /**
     * Lấy dữ liệu cho báo cáo
     *
     * @param Request $request
     * @return array
     */
    public function getReportData(Request $request): array;
    
    /**
     * Lấy dữ liệu cho form tạo đặt phòng
     *
     * @return array
     */
    public function getCreateFormData(): array;
    
    /**
     * Lấy dữ liệu cho form chỉnh sửa đặt phòng
     *
     * @param int $id
     * @return array
     */
    public function getEditFormData(int $id): array;

    /**
     * Lấy danh sách trạng thái hợp lệ tiếp theo cho booking
     *
     * @param int $id
     * @return array
     */
    public function getValidNextStatuses(int $id): array;
} 