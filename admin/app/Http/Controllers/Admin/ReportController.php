<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportController extends BaseAdminController
{
    public function index(): View
    {
        $filters = request()->only(['status', 'reason', 'reportable_type', 'page', 'per_page']);
        $response = $this->api->getReports($filters);

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        return view('admin.reports.index', [
            'reports' => $response['reports'] ?? [],
            'pagination' => $response['pagination'] ?? [],
            'filters' => $filters,
        ]);
    }

    public function show(string $id): View
    {
        $response = $this->api->getReport($id);

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        return view('admin.reports.show', [
            'report' => $response['report'] ?? [],
        ]);
    }

    public function dismiss(string $id): RedirectResponse
    {
        $notes = request()->input('admin_notes');
        $response = $this->api->dismissReport($id, $notes);

        return $this->handleApiResponse($response, 'Reporte descartado correctamente');
    }

    public function action(string $id): RedirectResponse
    {
        $notes = request()->input('admin_notes');
        $response = $this->api->actionReport($id, $notes);

        return $this->handleApiResponse($response, 'Reporte marcado como accionado correctamente');
    }
}
