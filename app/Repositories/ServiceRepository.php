<?php

namespace App\Repositories;

use App\Interfaces\Repositories\ServiceRepositoryInterface;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function all(): Collection
    {
        return Service::with('category')->get();
    }

    public function getAllWithFilter(?int $categoryId = null, ?int $roomTypeId = null): Collection
    {
        $query = Service::with(['category', 'roomTypes']);
        
        if ($categoryId) {
            $query->where('service_category_id', $categoryId);
        }
        
        if ($roomTypeId) {
            $query->whereHas('roomTypes', function($q) use ($roomTypeId) {
                $q->where('room_type_id', $roomTypeId);
            });
        }
        
        return $query->get();
    }

    public function find(int $id)
    {
        return Service::with('category')->find($id);
    }

    public function create(array $data)
    {
        return Service::create($data);
    }

    public function update(int $id, array $data)
    {
        $service = Service::findOrFail($id);
        $service->update($data);
        return $service;
    }

    public function delete(int $id)
    {
        $service = Service::findOrFail($id);
        return $service->delete();
    }

    public function paginateWithFilter(?int $categoryId = null, ?int $roomTypeId = null, int $perPage = 10)
    {
        $query = Service::with(['category', 'roomTypes']);
        if ($categoryId) {
            $query->where('service_category_id', $categoryId);
        }
        if ($roomTypeId) {
            $query->whereHas('roomTypes', function($q) use ($roomTypeId) {
                $q->where('room_type_id', $roomTypeId);
            });
        }
        return $query->paginate($perPage);
    }
} 