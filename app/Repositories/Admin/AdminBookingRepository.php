<?php

namespace App\Repositories\Admin;

use App\Models\Booking;
use App\Interfaces\Repositories\Admin\AdminBookingRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AdminBookingRepository implements AdminBookingRepositoryInterface
{
    protected $model;

    public function __construct(Booking $model)
    {
        $this->model = $model;
    }

    /**
     * Lấy tất cả đặt phòng có phân trang
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getAllWithPagination(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'room']);
        
        // Áp dụng bộ lọc
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }
        
        return $query->latest()->paginate($perPage);
    }
    
    /**
     * Lấy đặt phòng theo ID
     *
     * @param int $id
     * @return Booking|null
     */
    public function findById(int $id): ?Booking
    {
        return $this->model->with(['user', 'room'])->find($id);
    }
    
    /**
     * Tạo đặt phòng mới
     *
     * @param array $data
     * @return Booking
     */
    public function create(array $data): Booking
    {
        return $this->model->create($data);
    }
    
    /**
     * Cập nhật đặt phòng
     *
     * @param Booking $booking
     * @param array $data
     * @return bool
     */
    public function update(Booking $booking, array $data): bool
    {
        return $booking->update($data);
    }
    
    /**
     * Xóa đặt phòng
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->model->destroy($id) > 0;
    }
    
    /**
     * Cập nhật trạng thái đặt phòng
     *
     * @param int $id
     * @param string $status
     * @return bool
     */
    public function updateStatus(int $id, string $status): bool
    {
        $booking = $this->model->find($id);
        if ($booking) {
            $booking->status = $status;
            return $booking->save();
        }
        return false;
    }
    
    /**
     * Lấy đặt phòng gần đây
     *
     * @param int $limit
     * @return Collection
     */
    public function getRecent(int $limit = 5): Collection
    {
        return $this->model->with(['user', 'room'])
                           ->latest()
                           ->limit($limit)
                           ->get();
    }
    
    /**
     * Đếm số đặt phòng theo trạng thái
     *
     * @param string $status
     * @return int
     */
    public function countByStatus(string $status): int
    {
        return $this->model->where('status', $status)->count();
    }
    
    /**
     * Đếm số đặt phòng hôm nay
     *
     * @return int
     */
    public function countToday(): int
    {
        return $this->model->whereDate('created_at', Carbon::today())->count();
    }
    
    /**
     * Tính tổng doanh thu trong tháng
     *
     * @return float
     */
    public function calculateMonthlyRevenue(): float
    {
        return $this->model->whereMonth('created_at', Carbon::now()->month)
                           ->whereYear('created_at', Carbon::now()->year)
                           ->where('status', '!=', 'cancelled')
                           ->sum('price');
    }
    
    /**
     * Lấy đặt phòng theo khoảng thời gian và trạng thái
     *
     * @param array $filters
     * @return Collection
     */
    public function getBookingsForReport(array $filters = []): Collection
    {
        $query = $this->model->with(['user', 'room']);
        
        // Lọc theo ngày
        if (isset($filters['from_date']) && $filters['from_date']) {
            $query->where('check_in_date', '>=', $filters['from_date']);
        }
        
        if (isset($filters['to_date']) && $filters['to_date']) {
            $query->where('check_out_date', '<=', $filters['to_date']);
        }
        
        // Lọc theo trạng thái
        if (isset($filters['status']) && $filters['status'] && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }
        
        return $query->get();
    }
} 