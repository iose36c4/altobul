<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VerificationRequest;
use App\Services\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function __construct(
        private AuthService $authService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status', 'PENDING');

        $requests = VerificationRequest::with('user.profile')
            ->where('status', $status)
            ->orderBy('submitted_at')
            ->paginate($request->query('per_page', 20));

        return response()->json([
            'requests' => $requests->map(fn($r) => [
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

    public function show(VerificationRequest $request): JsonResponse
    {
        $request->load('user.profile', 'reviewedBy');

        return response()->json([
            'request' => [
                'id' => $request->id,
                'user' => [
                    'id' => $request->user->id,
                    'email' => $request->user->email,
                    'verification_status' => $request->user->verification_status,
                    'profile' => $request->user->profile ? [
                        'title' => $request->user->profile->title,
                        'description' => $request->user->profile->description,
                        'birth_date' => $request->user->profile->birth_date?->format('Y-m-d'),
                    ] : null,
                ],
                'status' => $request->status,
                'verification_method' => $request->verification_method,
                'external_reference' => $request->external_reference,
                'submitted_at' => $request->submitted_at?->toISOString(),
                'reviewed_at' => $request->reviewed_at?->toISOString(),
                'reviewed_by' => $request->reviewedBy ? [
                    'id' => $request->reviewedBy->id,
                    'email' => $request->reviewedBy->email,
                ] : null,
                'rejection_reason' => $request->rejection_reason,
            ],
        ]);
    }

    public function approve(VerificationRequest $request): JsonResponse
    {
        $reviewed = $this->authService->reviewVerification($request, 'approve');

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