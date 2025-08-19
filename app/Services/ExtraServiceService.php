<?php
namespace App\Services;

use App\Interfaces\Services\ExtraServiceServiceInterface;
use App\Interfaces\Repositories\ExtraServiceRepositoryInterface;
use Illuminate\Support\Collection;

class ExtraServiceService implements ExtraServiceServiceInterface
{
    protected $extraServiceRepository;

    public function __construct(ExtraServiceRepositoryInterface $extraServiceRepository)
    {
        $this->extraServiceRepository = $extraServiceRepository;
    }

    /**
     * Lấy tất cả dịch vụ bổ sung
     *
     * @return Collection
     */
    public function getAllExtraServices(): Collection
    {
        return $this->extraServiceRepository->all();
    }

    /**
     * Tạo dịch vụ bổ sung mới
     *
     * @param array $data
     * @return mixed
     */
    public function createExtraService(array $data)
    {
        return $this->extraServiceRepository->create($data);
    }

    /**
     * Cập nhật dịch vụ bổ sung
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateExtraService(int $id, array $data)
    {
        return $this->extraServiceRepository->update($id, $data);
    }

    /**
     * Xóa dịch vụ bổ sung
     *
     * @param int $id
     * @return bool
     */
    public function destroyExtraService(int $id)
    {
        return $this->extraServiceRepository->destroy($id);
    }

    /**
     * Lấy dịch vụ bổ sung theo id, throw nếu không tồn tại
     *
     * @param int $id
     * @return \App\Models\ExtraService
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findOrFail(int $id)
    {
        return $this->extraServiceRepository->find($id);
    }
    
}