<?php

namespace App\Http\Controllers;

use App\Http\Resources\FriendshipRequestResource;
use App\Http\Resources\FriendshipResource;
use App\Models\Friendship;
use App\Models\FriendshipRequest;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendshipController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $friendships = Friendship::where(function ($q) use ($user) {
            $q->where('user_a_id', $user->id)
                ->orWhere('user_b_id', $user->id);
        })->where('status', 'ACTIVE')
            ->with(['userA.profile', 'userB.profile'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'friendships' => FriendshipResource::collection($friendships),
            'pagination' => [
                'current_page' => $friendships->currentPage(),
                'last_page' => $friendships->lastPage(),
                'per_page' => $friendships->perPage(),
                'total' => $friendships->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'addressee_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $addressee = User::findOrFail($data['addressee_id']);

        $this->authz->canRequestFriendship($user, $addressee)->throwIfDenied();

        $friendshipRequest = FriendshipRequest::create([
            'requester_id' => $user->id,
            'addressee_id' => $addressee->id,
            'status' => 'PENDING',
            'expires_at' => now()->addDays(7),
        ]);

        return response()->json([
            'friendship_request' => new FriendshipRequestResource($friendshipRequest->load(['requester.profile', 'addressee.profile'])),
        ], 201);
    }

    public function destroy(Friendship $friendship): JsonResponse
    {
        $user = request()->user();

        $isParticipant = $friendship->user_a_id === $user->id || $friendship->user_b_id === $user->id;

        if (! $isParticipant) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You are not part of this friendship',
            ], 403);
        }

        if ($friendship->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'Invalid state',
                'message' => 'This friendship is not active',
            ], 422);
        }

        $otherUser = $friendship->user_a_id === $user->id ? $friendship->userB : $friendship->userA;
        $this->authz->canEndFriendship($user, $otherUser)->throwIfDenied();

        $friendship->update(['status' => 'ENDED']);

        return response()->json([
            'friendship' => new FriendshipResource($friendship->fresh(['userA.profile', 'userB.profile'])),
        ]);
    }
}
