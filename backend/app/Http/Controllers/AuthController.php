<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\VerificationRequest;
use App\Services\Auth\AuthService;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private AuthorizationService $authzService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = $this->authService->register($request->validated());

        return response()->json([
            'user' => new UserResource($user),
            'message' => 'Registration successful. Please verify your email.',
        ], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $this->authService->login($request->validated());

        return response()->json([
            'user' => new UserResource($data['user']),
            'token' => $data['token'],
            'expires_at' => $data['expires_at'],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function refresh(Request $request): JsonResponse
    {
        $data = $this->authService->refresh($request->user());

        return response()->json([
            'user' => new UserResource($data['user']),
            'token' => $data['token'],
            'expires_at' => $data['expires_at'],
        ]);
    }

    public function forgotPassword(ForgotPasswordRequest $request): JsonResponse
    {
        $status = $this->authService->sendPasswordResetLink($request->validated()['email']);

        return response()->json(['message' => __($status)]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $this->authService->resetPassword($request->validated());

        return response()->json(['message' => 'Password reset successfully']);
    }

    public function verifyEmail(Request $request, string $id, string $hash): JsonResponse
    {
        $user = User::find($id);

        if (! $user || ! hash_equals(hash_hmac('sha256', $user->email, app('config')['app.key']), $hash)) {
            return response()->json(['error' => 'Invalid verification link'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        $user->markEmailAsVerified();

        return response()->json(['message' => 'Email verified successfully']);
    }

    public function resendVerificationEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified']);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Verification email sent']);
    }

    // Verification request endpoints
    public function requestVerification(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authzService->canRequestVerification($user)->throwIfDenied();

        $data = $request->validate([
            'verification_method' => ['required', 'string', 'in:document,video,manual'],
            'external_reference' => ['nullable', 'string', 'max:255'],
        ]);

        $verification = $this->authService->requestVerification(
            $user,
            $data['verification_method'],
            $data['external_reference'] ?? null
        );

        return response()->json([
            'verification' => [
                'id' => $verification->id,
                'status' => $verification->status,
                'verification_method' => $verification->verification_method,
                'submitted_at' => $verification->submitted_at?->toISOString(),
            ],
        ], 201);
    }

    public function getVerificationStatus(Request $request): JsonResponse
    {
        $user = $request->user();
        $verification = VerificationRequest::where('user_id', $user->id)
            ->latest('submitted_at')
            ->first();

        return response()->json([
            'verification' => $verification ? [
                'id' => $verification->id,
                'status' => $verification->status,
                'verification_method' => $verification->verification_method,
                'external_reference' => $verification->external_reference,
                'submitted_at' => $verification->submitted_at?->toISOString(),
                'reviewed_at' => $verification->reviewed_at?->toISOString(),
                'rejection_reason' => $verification->rejection_reason,
                'reviewed_by' => $verification->reviewedBy ? [
                    'id' => $verification->reviewedBy->id,
                    'email' => $verification->reviewedBy->email,
                ] : null,
            ] : null,
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'user' => new UserResource($request->user()->load('profile')),
        ]);
    }
}
