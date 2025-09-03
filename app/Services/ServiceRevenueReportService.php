<?php

namespace App\Services;

use App\Interfaces\Repositories\ServiceRevenueReportRepositoryInterface;
use App\Interfaces\Services\ServiceRevenueReportServiceInterface;

class ServiceRevenueReportService implements ServiceRevenueReportServiceInterface
{
    public function __construct(
        protected ServiceRevenueReportRepositoryInterface $repo
    ) {}

    public function getSummary(array $filters = []): array
    {
        return $this->repo->getSummary($filters);
    }

    public function getTop(array $filters = []): array
    {
        return $this->repo->getTopServices($filters);
    }
}
