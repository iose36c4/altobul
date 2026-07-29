<?php

namespace App\Http\Controllers\Admin;

use Illuminate\View\View;

class AuditLogController extends BaseAdminController
{
    public function index(): View
    {
        $filters = request()->only(['action', 'target_type', 'admin_id', 'date_from', 'date_to', 'page', 'per_page']);
        $response = $this->api->getAuditLogs($filters);
        $data = $response['data'] ?? $response;

        return view('admin.audit-logs.index', [
            'logs' => $data['audit_logs'] ?? [],
            'pagination' => $data['pagination'] ?? null,
            'filters' => $filters,
        ]);
    }
}
