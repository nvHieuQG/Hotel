<?php

namespace App\Repositories;

use App\Interfaces\Repositories\ServiceRevenueReportRepositoryInterface;
use App\Models\Booking;
use App\Models\ExtraService;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class ServiceRevenueReportRepository implements ServiceRevenueReportRepositoryInterface
{
    public function getSummary(array $filters = []): array
    {
        [$dateFrom, $dateTo, $dateField, $serviceId, $statuses] = $this->normalizeFilters($filters);

        $query = Booking::query()
            ->whereNotNull('extra_services')
            ->whereJsonLength('extra_services', '>', 0);

        if (!empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        if ($dateFrom) {
            $query->whereDate($dateField, '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate($dateField, '<=', $dateTo);
        }

        $bookings = $query->get(['id', 'user_id', 'extra_services', 'extra_services_total']);

        $byService = [];
        $bookingsWithServices = 0;
        $uniqueUsers = collect();
        $grandRevenue = 0;

        foreach ($bookings as $b) {
            $items = $b->extra_services ?? [];
            if (!is_array($items) || empty($items)) {
                continue;
            }
            $bookingsWithServices++;
            $uniqueUsers->push($b->user_id);
            $grandRevenue += (float) $b->extra_services_total;

            foreach ($items as $item) {
                $sid = (int) Arr::get($item, 'id');
                if ($serviceId && $sid !== (int) $serviceId) {
                    continue;
                }
                if (!$sid) continue;

                $subtotal = (float) (Arr::get($item, 'subtotal', 0));
                $adultsUsed = (int) (Arr::get($item, 'adults_used', 0));
                $childrenUsed = (int) (Arr::get($item, 'children_used', 0));
                $qty = Arr::get($item, 'quantity');
                $days = Arr::get($item, 'days');
                $chargeType = (string) Arr::get($item, 'charge_type', '');

                if (!isset($byService[$sid])) {
                    $byService[$sid] = [
                        'service_id' => $sid,
                        'service_name' => null,
                        'charge_type' => $chargeType,
                        'bookings_count' => 0,
                        'unique_customers' => collect(),
                        'total_revenue' => 0.0,
                        'total_quantity' => 0,
                        'total_days' => 0,
                        'total_adults_used' => 0,
                        'total_children_used' => 0,
                    ];
                }

                $byService[$sid]['bookings_count'] += 1; // đếm mỗi lần xuất hiện trong booking
                $byService[$sid]['unique_customers']->push($b->user_id);
                $byService[$sid]['total_revenue'] += $subtotal;
                $byService[$sid]['total_adults_used'] += $adultsUsed;
                $byService[$sid]['total_children_used'] += $childrenUsed;
                if (is_numeric($qty)) $byService[$sid]['total_quantity'] += (int) $qty;
                if (is_numeric($days)) $byService[$sid]['total_days'] += (int) $days;
            }
        }

        // Gắn tên dịch vụ từ bảng extra_services để hiển thị
        $serviceIds = array_keys($byService);
        if (!empty($serviceIds)) {
            $map = ExtraService::whereIn('id', $serviceIds)->pluck('name', 'id');
            foreach ($byService as $sid => &$row) {
                $row['service_name'] = $map[$sid] ?? ('Service #' . $sid);
                $row['unique_customers'] = $row['unique_customers']->unique()->count();
                // làm tròn 2 số lẻ
                $row['total_revenue'] = round($row['total_revenue'], 2);
            }
            unset($row);
        }

        return [
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'date_field' => $dateField,
                'service_id' => $serviceId,
                'statuses' => $statuses,
            ],
            'by_service' => array_values($byService),
            'totals' => [
                'bookings_with_services' => $bookingsWithServices,
                'unique_customers' => $uniqueUsers->unique()->count(),
                'total_revenue' => round($grandRevenue, 2),
            ],
        ];
    }

    public function getTopServices(array $filters = []): array
    {
        $metric = $filters['metric'] ?? 'total_revenue';
        $limit = (int) ($filters['limit'] ?? 10);

        $summary = $this->getSummary($filters);
        $rows = $summary['by_service'] ?? [];

        usort($rows, function ($a, $b) use ($metric) {
            $av = $a[$metric] ?? 0; $bv = $b[$metric] ?? 0;
            if ($av === $bv) return 0;
            return $av < $bv ? 1 : -1; // desc
        });

        return array_slice($rows, 0, max(1, $limit));
    }

    private function normalizeFilters(array $filters): array
    {
        $dateFrom = $filters['date_from'] ?? null;
        $dateTo = $filters['date_to'] ?? null;
        $dateField = in_array(($filters['date_field'] ?? ''), ['created_at','check_in_date','check_out_date'], true)
            ? $filters['date_field'] : 'check_out_date';
        $serviceId = $filters['service_id'] ?? null;
        $statuses = $filters['statuses'] ?? ['checked_out','completed'];
        return [$dateFrom, $dateTo, $dateField, $serviceId, $statuses];
    }
}
