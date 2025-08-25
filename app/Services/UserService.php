<?php

namespace App\Services;

use App\Interfaces\Services\UserServiceInterface;
use App\Interfaces\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
     * Phân trang người dùng với bộ lọc
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->userRepository->paginate($filters, $perPage);
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

    /**
     * Cập nhật thông tin người dùng
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateUser(int $id, array $data): bool
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return false;
        }
        return $this->userRepository->update($user, $data);
    }

    /**
     * Xóa người dùng
     *
     * @param int $id
     * @return bool
     */
    public function deleteUser(int $id): bool
    {
        $user = $this->userRepository->findById($id);
        if (!$user) {
            return false;
        }
        $user->delete();
        return true;
    }

    /**
     * Tạo người dùng mới
     *
     * @param array $data
     * @return mixed
     */
    public function createUser(array $data)
    {
        return $this->userRepository->create($data);
    }
}