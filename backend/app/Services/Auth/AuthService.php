<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Models\Profile;
use App\Models\VerificationRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\DB;

class AuthService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $userId = (string) Str::uuid();
            
            DB::table('users')->insert([
                'id' => $userId,
                'email' => $data['email'],
                'password_hash' => Hash::make($data['password']),
                'email_verified_at' => null,
                'verification_status' => 'not_verified',
                'status' => 'active',
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('profiles')->insert([
                'user_id' => $userId,
                'location' => DB::raw("ST_SetSRID(ST_MakePoint(0, 0), 4326)"),
                'location_precision_meters' => config('app.location_default_precision_meters', 1000),
                'discoverable' => true,
                'profile_visibility' => 'PUBLIC',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $user = User::find($userId);
            $user->sendEmailVerificationNotification();

            return $user;
        });
    }

    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password_hash)) {
            throw new \Illuminate\Validation\ValidationException(
                \Illuminate\Validation\Validator::make([], []),
                'Invalid credentials'
            );
        }

        if ($user->status !== 'active') {
            throw new \Illuminate\Validation\ValidationException(
                \Illuminate\Validation\Validator::make([], []),
                'Account is not active'
            );
        }

        // Update last_seen_at
        $user->update(['last_seen_at' => now()]);

        $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        return [
            'user' => $user->fresh(),
            'token' => $token,
            'expires_at' => now()->addDays(30)->toISOString(),
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function refresh(User $user): array
    {
        $user->currentAccessToken()?->delete();
        $user->update(['last_seen_at' => now()]);

        $token = $user->createToken('api-token', ['*'], now()->addDays(30))->plainTextToken;

        return [
            'user' => $user->fresh(),
            'token' => $token,
            'expires_at' => now()->addDays(30)->toISOString(),
        ];
    }

    public function sendPasswordResetLink(string $email): string
    {
        return Password::sendResetLink(['email' => $email]);
    }

    public function resetPassword(array $data): void
    {
        $user = User::where('email', $data['email'])->firstOrFail();

        $user->update([
            'password_hash' => Hash::make($data['password']),
        ]);

        // Revoke all tokens
        $user->tokens()->delete();
    }

    public function requestVerification(User $user, string $method = 'document', ?string $externalReference = null): VerificationRequest
    {
        return DB::transaction(function () use ($user, $method, $externalReference) {
            // Cancel any existing pending requests
            VerificationRequest::where('user_id', $user->id)
                ->where('status', 'PENDING')
                ->update(['status' => 'REJECTED', 'rejection_reason' => 'Superseded by new request']);

            $request = VerificationRequest::create([
                'user_id' => $user->id,
                'status' => 'PENDING',
                'verification_method' => $method,
                'external_reference' => $externalReference,
                'submitted_at' => now(),
            ]);

            $user->update(['verification_status' => 'pending']);

            return $request;
        });
    }

    public function getVerificationStatus(User $user): array
    {
        $latest = VerificationRequest::where('user_id', $user->id)
            ->latest('submitted_at')
            ->first();

        return [
            'verification_status' => $user->verification_status,
            'verified_at' => $user->verified_at?->toISOString(),
            'latest_request' => $latest ? [
                'id' => $latest->id,
                'status' => $latest->status,
                'submitted_at' => $latest->submitted_at?->toISOString(),
                'reviewed_at' => $latest->reviewed_at?->toISOString(),
                'rejection_reason' => $latest->rejection_reason,
                'verification_method' => $latest->verification_method,
            ] : null,
        ];
    }

    public function reviewVerification(VerificationRequest $request, string $action, ?string $rejectionReason = null): VerificationRequest
    {
        return DB::transaction(function () use ($request, $action, $rejectionReason) {
            if ($action === 'approve') {
                $request->update([
                    'status' => 'APPROVED',
                    'reviewed_at' => now(),
                ]);
                $request->user->update([
                    'verification_status' => 'verified',
                    'verified_at' => now(),
                ]);
            } elseif ($action === 'reject') {
                $request->update([
                    'status' => 'REJECTED',
                    'reviewed_at' => now(),
                    'rejection_reason' => $rejectionReason,
                ]);
                $request->user->update(['verification_status' => 'not_verified']);
            }

            return $request->fresh('user', 'reviewedBy');
        });
    }
}