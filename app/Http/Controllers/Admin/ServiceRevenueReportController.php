<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\Services\ServiceRevenueReportServiceInterface;
use App\Models\ExtraService;
use Illuminate\Http\Request;

class ServiceRevenueReportController extends Controller
{
    public function __construct(
        protected ServiceRevenueReportServiceInterface $service
    ) {}

    public function index(Request $request)
    {
        $filters = [
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
            'date_field' => $request->input('date_field', 'check_out_date'),
            'service_id' => $request->input('service_id'),
            'statuses' => $request->input('statuses', ['checked_out','completed']),
        ];

        $summary = $this->service->getSummary($filters);
        $top = $this->service->getTop(array_merge($filters, [
            'metric' => $request->input('metric', 'total_revenue'),
            'limit' => $request->has('limit') ? (int) $request->input('limit') : null,
        ]));

        $services = ExtraService::query()->where('is_active', true)->orderBy('name')->get(['id','name']);

        return view('admin.reports.service-revenue.index', [
            'filters' => $summary['filters'],
            'summary' => $summary,
            'top' => $top,
            'services' => $services,
        ]);
    }
}
