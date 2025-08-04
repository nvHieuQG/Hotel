<?php
namespace App\Interfaces\Repositories;
use Illuminate\Support\Collection;

interface ExtraServiceRepositoryInterface 
{
    public function all():Collection;
    public function find(int $id);
    public function create(array $data);
    public function update(int $id, array $data);
    public function destroy(int $id);
}
