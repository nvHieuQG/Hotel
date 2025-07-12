<?php

namespace App\Repositories;

use App\Interfaces\Repositories\ServiceRepositoryInterface;
use App\Models\Service;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository implements ServiceRepositoryInterface
{
    public function all(): Collection
    {
        return Service::all();
    }

    public function find(int $id)
    {
        return Service::find($id);
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
} 