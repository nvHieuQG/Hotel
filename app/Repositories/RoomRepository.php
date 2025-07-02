<?php

namespace App\Repositories;

use App\Models\Room;
use App\Interfaces\Repositories\RoomRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;

class RoomRepository implements RoomRepositoryInterface
{
    protected Room $model;

    public function __construct(Room $model)
    {
        $this->model = $model;
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function findById(int $id): ?Room
    {
        return $this->model->with('roomType')->find($id);
    }

    public function newQuery(): Builder
    {
        return $this->model->newQuery();
    }

    public function search(array $filters): Collection
    {
        return $this->model->newQuery()->get();
    }
    
    public function findAvailableRoomByType(int $roomTypeId, string $checkIn, string $checkOut): ?Room
    {
        return $this->model
            ->where('room_type_id', $roomTypeId)
            ->whereDoesntHave('bookings', function ($query) use ($checkIn, $checkOut) {
                $query->where(function ($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in_date', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                        ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                            $q2->where('check_in_date', '<=', $checkIn)
                                ->where('check_out_date', '>=', $checkOut);
                        });
                })->where('status', '!=', 'cancelled');
            })
            ->first();
    }

    public function getByRoomType(int $roomTypeId, int $limit = null): Collection
    {
        $query = $this->model->where('room_type_id', $roomTypeId)->with('roomType');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
}
