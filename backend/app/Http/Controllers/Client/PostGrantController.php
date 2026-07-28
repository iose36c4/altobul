<?php

namespace App\Http\Controllers\Client;

use App\Events\Broadcast\NewGrant;
use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostAccess;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostGrantController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $this->authorize('manage', $post);

        $grants = PostAccess::where('post_id', $post->id)
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

    public function store(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();

        $this->authorize('manage', $post);

        if ($post->visibility !== 'PRIVATE') {
            return response()->json([
                'error' => 'Invalid state',
                'message' => 'Grants are only available for PRIVATE posts',
            ], 422);
        }

        $result = $this->authz->canGrantAccess($user, $user, 'post', $post->id);
        if (! $result->allowed) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => $result->reason?->value,
            ], 403);
        }

        $grantee = User::findOrFail($request->input('grantee_id'));

        if ($grantee->id === $post->user_id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Cannot grant access to the post owner',
            ], 403);
        }

        $existing = PostAccess::where('post_id', $post->id)
            ->where('grantee_id', $grantee->id)
            ->whereNull('revoked_at')
            ->first();

        if ($existing) {
            return response()->json([
                'error' => 'Conflict',
                'message' => 'Grant already exists for this user',
            ], 409);
        }

        $grant = PostAccess::create([
            'post_id' => $post->id,
            'grantee_id' => $grantee->id,
            'granted_by' => $user->id,
            'expires_at' => $request->input('expires_at'),
        ]);

        broadcast(new NewGrant(
            $grant->load(['grantee', 'grantedBy', 'post']),
            $post,
            $grantee,
            $user
        ));

        return response()->json([
            'grant' => $grant->load(['grantee.profile', 'grantedBy']),
        ], 201);
    }

    public function destroy(Request $request, Post $post, User $grantee): JsonResponse
    {
        $user = $request->user();

        $this->authorize('manage', $post);

        $grant = PostAccess::where('post_id', $post->id)
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
