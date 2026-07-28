<?php

namespace App\Http\Controllers;

use App\Events\Broadcast\NewFriendship;
use App\Http\Resources\FriendshipRequestResource;
use App\Http\Resources\FriendshipResource;
use App\Models\Friendship;
use App\Models\FriendshipRequest;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FriendshipRequestController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $sent = FriendshipRequest::where('requester_id', $user->id)
            ->where('status', 'PENDING')
            ->with('addressee.profile')
            ->latest()
            ->paginate(20);

        $received = FriendshipRequest::where('addressee_id', $user->id)
            ->where('status', 'PENDING')
            ->with('requester.profile')
            ->latest()
            ->paginate(20);

        return response()->json([
            'sent' => FriendshipRequestResource::collection($sent)->response()->getData(true),
            'received' => FriendshipRequestResource::collection($received)->response()->getData(true),
        ]);
    }

    public function accept(FriendshipRequest $friendshipRequest): JsonResponse
    {
        $user = request()->user();

        if ($friendshipRequest->addressee_id !== $user->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You are not the addressee of this request',
            ], 403);
        }

        if ($friendshipRequest->status !== 'PENDING') {
            return response()->json([
                'error' => 'Invalid state',
                'message' => 'This request is no longer pending',
            ], 422);
        }

        $this->authz->canAcceptFriendshipRequest($user, $friendshipRequest->requester)->throwIfDenied();

        $friendship = Friendship::create([
            'user_a_id' => min($user->id, $friendshipRequest->requester_id),
            'user_b_id' => max($user->id, $friendshipRequest->requester_id),
            'status' => 'ACTIVE',
        ]);

        $friendshipRequest->update(['status' => 'ACCEPTED']);

        // Broadcast new friendship event
        broadcast(new NewFriendship($friendship->load(['userA.profile', 'userB.profile']), $user, $friendshipRequest->requester));

        return response()->json([
            'friendship' => new FriendshipResource($friendship->load(['userA.profile', 'userB.profile'])),
            'friendship_request' => new FriendshipRequestResource($friendshipRequest->fresh()),
        ]);
    }

    public function reject(FriendshipRequest $friendshipRequest): JsonResponse
    {
        $user = request()->user();

        if ($friendshipRequest->addressee_id !== $user->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You are not the addressee of this request',
            ], 403);
        }

        if ($friendshipRequest->status !== 'PENDING') {
            return response()->json([
                'error' => 'Invalid state',
                'message' => 'This request is no longer pending',
            ], 422);
        }

        $friendshipRequest->update(['status' => 'REJECTED']);

        return response()->json([
            'friendship_request' => new FriendshipRequestResource($friendshipRequest->fresh()),
        ]);
    }

    public function destroy(FriendshipRequest $friendshipRequest): JsonResponse
    {
        $user = request()->user();

        $canCancel = $friendshipRequest->requester_id === $user->id;
        $canReject = $friendshipRequest->addressee_id === $user->id;

        if (! $canCancel && ! $canReject) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You are not part of this request',
            ], 403);
        }

        if ($friendshipRequest->status !== 'PENDING') {
            return response()->json([
                'error' => 'Invalid state',
                'message' => 'This request is no longer pending',
            ], 422);
        }

        $friendshipRequest->update(['status' => 'REJECTED']);

        return response()->json([
            'friendship_request' => new FriendshipRequestResource($friendshipRequest->fresh()),
        ]);
    }
}
