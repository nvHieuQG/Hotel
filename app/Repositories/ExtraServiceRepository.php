<?php

namespace App\Repositories;

use App\Interfaces\Repositories\ExtraServiceRepositoryInterface;
use App\Models\ExtraService;
use Illuminate\Support\Collection;

class ExtraServiceRepository implements ExtraServiceRepositoryInterface
{
    public function all(): Collection
    {
        return ExtraService::all();
    }
    public function find(int $id)
    {
        return ExtraService::findOrFail($id);
    }
    public function create(array $data)
    {
        return ExtraService::create($data);
    }
    public function update(int $id, array $data)
    {
        $extraService = ExtraService::findOrFail($id);
        $extraService->update($data);
        return $extraService;
    }
    public function destroy(int $id)
    {
        return ExtraService::destroy($id);
    }
}
