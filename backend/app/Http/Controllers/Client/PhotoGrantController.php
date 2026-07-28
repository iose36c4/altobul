<?php

namespace App\Http\Controllers\Client;

use App\Events\Broadcast\NewGrant;
use App\Http\Controllers\Controller;
use App\Models\Photo;
use App\Models\PhotoAccess;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotoGrantController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Request $request, Photo $photo): JsonResponse
    {
        $user = $request->user();

        $this->authorize('manage', $photo);

        $grants = PhotoAccess::where('photo_id', $photo->id)
            ->whereNull('revoked_at')
            ->with(['grantee.profile', 'grantedBy'])
            ->latest('granted_at')
            ->paginate(20);

        return response()->json([
            'grants' => $grants->items(),
            'pagination' => [
                'current_page' => $grants->currentPage(),
                'last_page' => $grants->lastPage(),
                'per_page' => $grants->perPage(),
                'total' => $grants->total(),
            ],
        ]);
    }

    public function store(Request $request, Photo $photo): JsonResponse
    {
        $user = $request->user();

        $this->authorize('manage', $photo);

        if ($photo->visibility !== 'PRIVATE') {
            return response()->json([
                'error' => 'Invalid state',
                'message' => 'Grants are only available for PRIVATE photos',
            ], 422);
        }

        $result = $this->authz->canGrantAccess($user, $user, 'photo', $photo->id);
        if (! $result->allowed) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => $result->reason?->value,
            ], 403);
        }

        $grantee = User::findOrFail($request->input('grantee_id'));

        if ($grantee->id === $photo->user_id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Cannot grant access to the photo owner',
            ], 403);
        }

        $existing = PhotoAccess::where('photo_id', $photo->id)
            ->where('grantee_id', $grantee->id)
            ->whereNull('revoked_at')
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Conflict',
                'message' => 'Grant already exists for this user',
            ], 409);
        }

        $grant = PhotoAccess::create([
            'photo_id' => $photo->id,
            'grantee_id' => $grantee->id,
            'granted_by' => $user->id,
            'expires_at' => $request->input('expires_at'),
        ]);

        broadcast(new NewGrant(
            $grant->load(['grantee', 'grantedBy', 'photo']),
            $photo,
            $grantee,
            $user
        ));

        return response()->json([
            'grant' => $grant->load(['grantee.profile', 'grantedBy']),
        ], 201);
    }

    public function destroy(Request $request, Photo $photo, User $grantee): JsonResponse
    {
        $user = $request->user();

        $this->authorize('manage', $photo);

        $grant = PhotoAccess::where('photo_id', $photo->id)
            ->where('grantee_id', $grantee->id)
            ->whereNull('revoked_at')
            ->firstOrFail();

        $grant->update(['revoked_at' => now()]);

        return response()->json([
            'grant' => $grant->fresh(['grantee.profile', 'grantedBy']),
            'message' => 'Grant revoked',
        ]);
    }
}
