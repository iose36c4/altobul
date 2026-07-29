<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;

class DashboardController extends BaseAdminController
{
    public function index(): View
    {
        $metricsResponse = $this->api->getDashboardMetrics();
        $chartsResponse = $this->api->getDashboardCharts();

        $metrics = [];
        $charts = [];

        if (! isset($metricsResponse['error'])) {
            $metrics = $metricsResponse['metrics'] ?? [];
        }

        if (! isset($chartsResponse['error'])) {
            $charts = $chartsResponse['charts'] ?? [];
        }

        return view('admin.dashboard.index', [
            'metrics' => $metrics,
            'charts' => $charts,
        ]);
    }
}
