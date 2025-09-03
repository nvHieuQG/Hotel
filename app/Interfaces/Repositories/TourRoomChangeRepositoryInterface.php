<?php

namespace App\Interfaces\Repositories;

use App\Models\TourRoomChange;

interface TourRoomChangeRepositoryInterface
{
    public function create(array $data): TourRoomChange;
    public function findById(int $id): ?TourRoomChange;
    public function update(TourRoomChange $entity, array $data): bool;
    public function getByTourBookingId(int $tourBookingId, array $filters = []);
}


