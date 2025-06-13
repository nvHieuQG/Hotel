<?php

namespace App\Interfaces\Services;
use App\Models\User;
use Illuminate\Http\Request;

interface RoomServiceInterface
{
    /**
     * Tìm kiếm phòng theo các bộ lọc
     *
     * @return array
     */
    public function searchRooms(array $filters);
}