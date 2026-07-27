<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = AdminAuditLog::with('admin')
            ->when($request->input('action'), function ($q, $action) {
                $q->where('action', $action);
            })
            ->when($request->input('target_type'), function ($q, $type) {
                $q->where('target_type', $type);
            })
            ->when($request->input('admin_id'), function ($q, $id) {
                $q->where('admin_id', $id);
            })
            ->orderBy('created_at', 'desc');

        $logs = $query->paginate($request->input('per_page', 50));

        return response()->json([
            'audit_logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'admin' => $log->admin ? [
                        'id' => $log->admin->id,
                        'email' => $log->admin->email,
                    ] : null,
                    'action' => $log->action,
                    'target_type' => $log->target_type,
                    'target_id' => $log->target_id,
                    'metadata' => $log->metadata,
                    'ip_address' => $log->ip_address,
                    'user_agent' => $log->user_agent,
                    'created_at' => $log->created_at->toISOString(),
                ];
            }),
            'pagination' => [
                'current_page' => $logs->currentPage(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'total' => $logs->total(),
            ],
        ]);
    }
}
