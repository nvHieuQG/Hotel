<?php

namespace App\Repositories;

use App\Interfaces\Repositories\ServiceRevenueReportRepositoryInterface;
use App\Models\Booking;
use App\Models\ExtraService;
use App\Models\BookingService;
use App\Models\Service as AdminService;
use Illuminate\Support\Arr;

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

            // track services selected in this booking to count 'uses' per booking
            $servicesInBooking = [];

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
                        'total_uses' => 0, // số lượt sử dụng: mỗi booking góp 1 lượt nếu có chọn dịch vụ này
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

                // mark this service as present in this booking for total_uses later
                $servicesInBooking[$sid] = true;
            }

            // After processing all items of this booking, increment total_uses ONCE per service present
            if (!empty($servicesInBooking)) {
                foreach (array_keys($servicesInBooking) as $sid) {
                    if (!isset($byService[$sid])) {
                        // should not happen because we created rows above, but guard anyway
                        $byService[$sid] = [
                            'service_id' => $sid,
                            'service_name' => null,
                            'charge_type' => '',
                            'bookings_count' => 0,
                            'total_uses' => 0,
                            'unique_customers' => collect(),
                            'total_revenue' => 0.0,
                            'total_quantity' => 0,
                            'total_days' => 0,
                            'total_adults_used' => 0,
                            'total_children_used' => 0,
                        ];
                    }
                    $byService[$sid]['total_uses'] += 1;
                }
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

        // Tính doanh thu dịch vụ do admin thêm từ bảng booking_services, theo cùng bộ lọc booking
        $bookingIds = $bookings->pluck('id')->filter()->values();
        $adminByService = [];
        $adminTotalRevenue = 0.0;
        if ($bookingIds->isNotEmpty()) {
            // Map booking id -> booking code
            $bookingCodeMap = Booking::query()->whereIn('id', $bookingIds)->pluck('booking_id', 'id');
            $adminItems = BookingService::query()
                ->whereIn('booking_id', $bookingIds)
                ->get(['service_id', 'total_price', 'booking_id']);

            foreach ($adminItems as $it) {
                $sid = (int) ($it->service_id ?? 0);
                if (!$sid) continue;
                if (!isset($adminByService[$sid])) {
                    $adminByService[$sid] = [
                        'service_id' => $sid,
                        'service_name' => null,
                        'bookings_count' => 0,
                        'total_uses' => 0,
                        'total_revenue' => 0.0,
                        'booking_ids' => [],
                        'booking_codes' => [],
                    ];
                }
                $adminByService[$sid]['total_uses'] += 1;
                $adminByService[$sid]['total_revenue'] += (float) $it->total_price;
                $adminByService[$sid]['bookings_count'] += 1; // mỗi dòng xem như 1 lần ghi cho 1 booking
                if ($it->booking_id) {
                    $adminByService[$sid]['booking_ids'][] = (int) $it->booking_id;
                    $code = (string) ($bookingCodeMap[$it->booking_id] ?? '');
                    if ($code !== '') {
                        $adminByService[$sid]['booking_codes'][] = $code;
                    }
                }
                $adminTotalRevenue += (float) $it->total_price;
            }

            // Gắn tên dịch vụ từ bảng services (dịch vụ admin)
            $adminServiceIds = array_keys($adminByService);
            if (!empty($adminServiceIds)) {
                $adminMap = AdminService::whereIn('id', $adminServiceIds)->pluck('name', 'id');
                foreach ($adminByService as $sid => &$row2) {
                    $row2['service_name'] = $adminMap[$sid] ?? ('Service #' . $sid);
                    $row2['total_revenue'] = round($row2['total_revenue'], 2);
                    // unique & sort booking ids for readability
                    $row2['booking_ids'] = array_values(array_unique($row2['booking_ids']));
                    $row2['booking_codes'] = array_values(array_unique($row2['booking_codes']));
                }
                unset($row2);
            }
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
            'admin_by_service' => array_values($adminByService),
            'totals' => [
                'bookings_with_services' => $bookingsWithServices,
                'unique_customers' => $uniqueUsers->unique()->count(),
                'total_revenue' => round($grandRevenue, 2),
            ],
            'admin_totals' => [
                'total_revenue' => round($adminTotalRevenue, 2),
            ],
        ];
    }

    public function getTopServices(array $filters = []): array
    {
        $metric = $filters['metric'] ?? 'total_revenue';
        $limitRaw = $filters['limit'] ?? null;
        $limit = is_null($limitRaw) ? null : (int) $limitRaw;

        $summary = $this->getSummary($filters);
        $rows = $summary['by_service'] ?? [];

        usort($rows, function ($a, $b) use ($metric) {
            $av = $a[$metric] ?? 0; $bv = $b[$metric] ?? 0;
            if ($av === $bv) return 0;
            return $av < $bv ? 1 : -1; // desc
        });

        if (is_null($limit)) {
            return $rows; // return all
        }
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
