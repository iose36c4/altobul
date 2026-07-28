<?php

namespace App\Http\Controllers\Profile;

use App\Events\Broadcast\NewGrant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\GrantFieldValueAccessRequest;
use App\Http\Resources\ProfileFieldValueAccessResource;
use App\Models\ProfileFieldValue;
use App\Models\ProfileFieldValueAccess;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileFieldValueAccessController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Request $request, ProfileFieldValue $fieldValue): JsonResponse
    {
        $user = $request->user();

        $this->authorize('manageGrants', $fieldValue);

        $grants = ProfileFieldValueAccess::where('field_value_id', $fieldValue->id)
            ->whereNull('revoked_at')
            ->with(['grantee.profile', 'grantedBy'])
            ->latest('granted_at')
            ->paginate(20);

        return response()->json([
            'grants' => ProfileFieldValueAccessResource::collection($grants),
            'pagination' => [
                'current_page' => $grants->currentPage(),
                'last_page' => $grants->lastPage(),
                'per_page' => $grants->perPage(),
                'total' => $grants->total(),
            ],
        ]);
    }

    public function store(GrantFieldValueAccessRequest $request, ProfileFieldValue $fieldValue): JsonResponse
    {
        $user = $request->user();

        $this->authorize('manageGrants', $fieldValue);

        $grantee = User::findOrFail($request->input('grantee_id'));

        // Check if grantee is the owner
        if ($grantee->id === $fieldValue->profile->user_id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Cannot grant access to the field owner',
            ], 403);
        }

        // Check if grant already exists
        $existing = ProfileFieldValueAccess::where('field_value_id', $fieldValue->id)
            ->where('grantee_id', $grantee->id)
            ->whereNull('revoked_at')
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Conflict',
                'message' => 'Grant already exists for this user',
            ], 409);
        }

        $grant = ProfileFieldValueAccess::create([
            'field_value_id' => $fieldValue->id,
            'grantee_id' => $grantee->id,
            'granted_by' => $user->id,
            'expires_at' => $request->input('expires_at'),
        ]);

        // Broadcast event to grantee
        broadcast(new NewGrant($grant->load(['grantee', 'grantedBy', 'fieldValue']), $fieldValue, $grantee, $user));

        return response()->json([
            'grant' => new ProfileFieldValueAccessResource($grant->load(['grantee.profile', 'grantedBy'])),
        ], 201);
    }

    public function destroy(Request $request, ProfileFieldValue $fieldValue, User $grantee): JsonResponse
    {
        $user = $request->user();

        $this->authorize('manageGrants', $fieldValue);

        $grant = ProfileFieldValueAccess::where('field_value_id', $fieldValue->id)
            ->where('grantee_id', $grantee->id)
            ->whereNull('revoked_at')
            ->firstOrFail();

        $grant->update(['revoked_at' => now()]);

        return response()->json([
            'grant' => new ProfileFieldValueAccessResource($grant->fresh(['grantee.profile', 'grantedBy'])),
            'message' => 'Grant revoked',
        ]);
    }
}
