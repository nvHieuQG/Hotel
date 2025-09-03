<?php

namespace App\Interfaces\Services;

interface ServiceRevenueReportServiceInterface
{
    public function getSummary(array $filters = []): array;
    public function getTop(array $filters = []): array;
}
