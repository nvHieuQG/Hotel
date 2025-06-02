<?php

namespace App\Interfaces\Repositories;

use App\Models\Booking;
use Illuminate\Database\Eloquent\Collection;

interface BookingRepositoryInterface
{
    /**
     * Lấy tất cả đặt phòng của người dùng
     *
     * @param int $userId
     * @return Collection
     */
    public function getByUserId(int $userId): Collection;
    
    /**
     * Tạo đặt phòng mới
     *
     * @param array $data
     * @return Booking
     */
    public function create(array $data): Booking;
    
    /**
     * Tìm đặt phòng theo ID
     *
     * @param int $id
     * @param int $userId
     * @return Booking|null
     */
    public function findByIdAndUserId(int $id, int $userId): ?Booking;
    
    /**
     * Cập nhật đặt phòng
     *
     * @param Booking $booking
     * @param array $data
     * @return bool
     */
    public function update(Booking $booking, array $data): bool;
} 