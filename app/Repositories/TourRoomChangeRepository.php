<?php

namespace App\Repositories;

use App\Interfaces\Repositories\TourRoomChangeRepositoryInterface;
use App\Models\TourRoomChange;

class TourRoomChangeRepository implements TourRoomChangeRepositoryInterface
{
    public function create(array $data): TourRoomChange
    {
        return TourRoomChange::create($data);
    }

    public function findById(int $id): ?TourRoomChange
    {
        return TourRoomChange::find($id);
    }

    public function update(TourRoomChange $entity, array $data): bool
    {
        return $entity->update($data);
    }

    public function getByTourBookingId(int $tourBookingId, array $filters = [])
    {
        $q = TourRoomChange::where('tour_booking_id', $tourBookingId);
        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }
        return $q->orderByDesc('created_at')->get();
    }
}


