<?php

namespace App\Services;

use App\Interfaces\Services\RoomServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use App\Interfaces\Repositories\RoomRepositoryInterface;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Pagination\LengthAwarePaginator;

class RoomService implements RoomServiceInterface
{
    protected $roomRepo;

    public function __construct(RoomRepositoryInterface $roomRepo)
    {
        $this->roomRepo = $roomRepo;
    }

    public function validateFilters(array $filters)
    {
        $validator = Validator::make($filters, [
            'keyword'   => 'nullable|string',
            'capacity'    => 'nullable|integer|min:1',
            'type'      => 'nullable|integer',
            'price_min' => 'nullable|numeric|min:0',
            'price_max' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    public function searchRooms(array $filters): Collection
    {
        $this->validateFilters($filters);
        $query = $this->roomRepo->newQuery();

        if (!empty($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function ($q) use ($keyword) {
                $q->where('room_number', 'like', '%' . $keyword . '%')
                    ->orWhereHas('roomType', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', '%' . $keyword . '%');
                    });
            });
        }

        if (!empty($filters['capacity']) || !empty($filters['price_min']) || !empty($filters['price_max'])) {
            $query->whereHas('roomType', function ($q) use ($filters) {
                if (!empty($filters['capacity'])) {
                    $q->where('capacity', '>=', (int)$filters['capacity']);
                }
                if (!empty($filters['price_min'])) {
                    $q->where('price', '>=', (int)$filters['price_min']);
                }
                if (!empty($filters['price_max'])) {
                    $q->where('price', '<=', (int)$filters['price_max']);
                }
            });
        }

        if (!empty($filters['type'])) {
            $query->where('room_type_id', $filters['type']);
            return $query->orderBy('room_number')->get();
        }

        return $query->get();
    }
}
