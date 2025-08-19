<?php
namespace App\Interfaces\Services;
use Illuminate\Support\Collection;

interface ExtraServiceServiceInterface{
    /**
     * Lấy tất cả dịch vụ bổ sung
     *
     * @return Collection
     */
    public function getAllExtraServices(): Collection;

    /**
     * Tạo dịch vụ bổ sung mới
     *
     * @param array $data
     * @return mixed
     */
    public function createExtraService(array $data);

    /**
     * Cập nhật dịch vụ bổ sung
     *
     * @param int $id
     * @param array $data
     * @return mixed
     */
    public function updateExtraService(int $id, array $data);

    /**
     * Xóa dịch vụ bổ sung
     *
     * @param int $id
     * @return bool
     */
    public function destroyExtraService(int $id);
}