<?php

namespace App\Services;

use App\Interfaces\Services\UserServiceInterface;
use App\Interfaces\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserService implements UserServiceInterface
{
    protected $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    /**
     * Lấy tất cả người dùng
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return $this->userRepository->getAll();
    }

    /**
     * Tìm người dùng theo ID
     *
     * @param int $id
     * @return mixed
     */
    public function findById(int $id)
    {
        return $this->userRepository->findById($id);
    }
} 