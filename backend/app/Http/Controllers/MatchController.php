<?php

namespace App\Http\Controllers;

use App\Events\Broadcast\NewFriendship;
use App\Http\Resources\FriendshipResource;
use App\Http\Resources\UserMatchResource;
use App\Models\Friendship;
use App\Models\UserMatch;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatchController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $matches = UserMatch::between($user, $user)
            ->where('status', 'ACTIVE')
            ->where('expires_at', '>', now())
            ->with(['userA.profile', 'userB.profile'])
            ->latest()
            ->paginate(20);

        return response()->json([
            'matches' => UserMatchResource::collection($matches),
            'pagination' => [
                'current_page' => $matches->currentPage(),
                'last_page' => $matches->lastPage(),
                'per_page' => $matches->perPage(),
                'total' => $matches->total(),
            ],
        ]);
    }

    public function convertToFriendship(UserMatch $match): JsonResponse
    {
        $user = request()->user();

        $target = $match->user_a_id === $user->id
            ? $match->userB
            : $match->userA;

        $this->authz->canConvertMatchToFriendship($user, $target)->throwIfDenied();

        $friendship = DB::transaction(function () use ($user, $target, $match) {
            $lockedMatch = UserMatch::where('id', $match->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedMatch || $lockedMatch->status !== 'ACTIVE') {
                abort(422, 'This match is no longer active.');
            }

            $friendship = Friendship::create([
                'user_a_id' => min($user->id, $target->id),
                'user_b_id' => max($user->id, $target->id),
                'status' => 'ACTIVE',
            ]);

            $lockedMatch->update([
                'status' => 'ENDED',
                'ended_at' => now(),
                'ended_by' => $user->id,
            ]);

            return $friendship;
        });

        $match->refresh();

        // Broadcast new friendship event
        broadcast(new NewFriendship($friendship->load(['userA.profile', 'userB.profile']), $user, $target));

        return response()->json([
            'friendship' => new FriendshipResource($friendship->load(['userA.profile', 'userB.profile'])),
            'match' => new UserMatchResource($match),
        ]);
    }
}
