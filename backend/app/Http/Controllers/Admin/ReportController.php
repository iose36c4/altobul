<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\User;
use App\Services\Admin\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $query = Report::with(['reporter', 'reviewedBy']);

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->input('reason'));
        }

        if ($request->filled('reportable_type')) {
            $query->where('reportable_type', $request->input('reportable_type'));
        }

        $reports = $query->orderBy('created_at', 'desc')
            ->paginate(min((int) $request->input('per_page', 20), 100));

        $reports->getCollection()->transform(function ($report) {
            try {
                $report->load('reportable');
            } catch (\Throwable) {
                // Ignore if polymorphic relation fails
            }

            return $report;
        });

        return response()->json([
            'reports' => $reports->items(),
            'pagination' => [
                'current_page' => $reports->currentPage(),
                'last_page' => $reports->lastPage(),
                'per_page' => $reports->perPage(),
                'total' => $reports->total(),
            ],
        ]);
    }

    public function show(Report $report): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $report->load(['reporter', 'reviewedBy']);

        try {
            $report->load('reportable');
        } catch (\Throwable) {
            // Ignore if polymorphic relation fails
        }

        return response()->json([
            'report' => $report,
        ]);
    }

    public function dismiss(Request $request, Report $report): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        if ($report->status !== 'PENDING') {
            return response()->json(['message' => 'Report is already '.$report->status], 422);
        }

        $report->update([
            'status' => 'DISMISSED',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        $this->auditLog->log('report.dismissed', 'Report', $report->id, [
            'reason' => $report->reason,
        ], $request->user());

        return response()->json([
            'message' => 'Report dismissed successfully',
            'report' => $report->fresh()->load(['reporter', 'reviewedBy']),
        ]);
    }

    public function action(Request $request, Report $report): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        if ($report->status !== 'PENDING') {
            return response()->json(['message' => 'Report is already '.$report->status], 422);
        }

        $report->update([
            'status' => 'ACTIONED',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
            'admin_notes' => $request->input('admin_notes'),
        ]);

        $this->auditLog->log('report.actioned', 'Report', $report->id, [
            'reason' => $report->reason,
        ], $request->user());

        return response()->json([
            'message' => 'Report actioned successfully',
            'report' => $report->fresh()->load(['reporter', 'reviewedBy']),
        ]);
    }

    private function authorizeAdmin(?User $user): void
    {
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Administrative privileges required');
        }
    }
}
