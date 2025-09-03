<?php

namespace App\Interfaces\Repositories;

interface ServiceRevenueReportRepositoryInterface
{
    /**
     * Lấy tổng quan doanh thu dịch vụ theo filter.
     *
     * Expected keys trong $filters:
     * - date_from (Y-m-d) | null
     * - date_to (Y-m-d) | null
     * - date_field: created_at|check_in_date|check_out_date (mặc định check_out_date)
     * - service_id: int|null
     * - statuses: string[] (mặc định ['checked_out','completed'])
     */
    public function getSummary(array $filters = []): array;

    /**
     * Lấy danh sách top dịch vụ theo metric (mặc định total_revenue).
     *
     * Expected keys trong $filters:
     * - metric: total_revenue|bookings_count (mặc định total_revenue)
     * - limit: int (mặc định 10)
     *  + các key filter như getSummary
     */
    public function getTopServices(array $filters = []): array;
}
