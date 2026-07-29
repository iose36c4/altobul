<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationRequest;
use App\Services\Admin\AuditLogService;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private AuditLogService $auditLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'PENDING');

        $requests = VerificationRequest::with('user.profile')
            ->where('status', $status)
            ->orderBy('submitted_at')
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json([
            'requests' => $requests->map(fn ($r) => [
                'id' => $r->id,
                'user' => [
                    'id' => $r->user->id,
                    'email' => $r->user->email,
                    'profile' => $r->user->profile ? [
                        'title' => $r->user->profile->title,
                        'description' => $r->user->profile->description,
                    ] : null,
                ],
                'status' => $r->status,
                'verification_method' => $r->verification_method,
                'external_reference' => $r->external_reference,
                'submitted_at' => $r->submitted_at?->toISOString(),
                'rejection_reason' => $r->rejection_reason,
            ]),
            'pagination' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ],
        ]);
    }

    public function show(VerificationRequest $verificationRequest): JsonResponse
    {
        $verificationRequest->load('user.profile', 'reviewedBy');

        return response()->json([
            'request' => [
                'id' => $verificationRequest->id,
                'user' => [
                    'id' => $verificationRequest->user->id,
                    'email' => $verificationRequest->user->email,
                    'verification_status' => $verificationRequest->user->verification_status,
                    'profile' => $verificationRequest->user->profile ? [
                        'title' => $verificationRequest->user->profile->title,
                        'description' => $verificationRequest->user->profile->description,
                        'birth_date' => $verificationRequest->user->profile->birth_date?->format('Y-m-d'),
                    ] : null,
                ],
                'status' => $verificationRequest->status,
                'verification_method' => $verificationRequest->verification_method,
                'external_reference' => $verificationRequest->external_reference,
                'submitted_at' => $verificationRequest->submitted_at?->toISOString(),
                'reviewed_at' => $verificationRequest->reviewed_at?->toISOString(),
                'reviewed_by' => $verificationRequest->reviewedBy ? [
                    'id' => $verificationRequest->reviewedBy->id,
                    'email' => $verificationRequest->reviewedBy->email,
                ] : null,
                'rejection_reason' => $verificationRequest->rejection_reason,
            ],
        ]);
    }

    public function approve(VerificationRequest $verificationRequest): JsonResponse
    {
        $reviewed = $this->authService->reviewVerification($verificationRequest, 'approve');

        $this->auditLog->log('verification.approve', 'VerificationRequest', $verificationRequest->id, [
            'request_id' => $verificationRequest->id,
            'user_id' => $verificationRequest->user_id,
        ], request()->user(), request());

        return response()->json([
            'request' => [
                'id' => $reviewed->id,
                'status' => $reviewed->status,
                'reviewed_at' => $reviewed->reviewed_at?->toISOString(),
                'user_verification_status' => $reviewed->user->verification_status,
            ],
        ]);
    }

    public function reject(Request $request, VerificationRequest $verificationRequest): JsonResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'max:500'],
        ]);

        $reviewed = $this->authService->reviewVerification(
            $verificationRequest,
            'reject',
            $validated['rejection_reason']
        );

        $this->auditLog->log('verification.reject', 'VerificationRequest', $verificationRequest->id, [
            'request_id' => $verificationRequest->id,
            'user_id' => $verificationRequest->user_id,
            'reason' => $validated['rejection_reason'],
        ], request()->user(), request());

        return response()->json([
            'request' => [
                'id' => $reviewed->id,
                'status' => $reviewed->status,
                'reviewed_at' => $reviewed->reviewed_at?->toISOString(),
                'rejection_reason' => $reviewed->rejection_reason,
                'user_verification_status' => $reviewed->user->verification_status,
            ],
        ]);
    }
}
