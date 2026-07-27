<?php

namespace App\Http\Controllers;

use App\Http\Resources\BlockResource;
use App\Models\Block;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BlockController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $blocks = Block::where('blocker_id', $user->id)
            ->with('blocked.profile')
            ->latest()
            ->paginate(20);

        return response()->json([
            'blocks' => BlockResource::collection($blocks),
            'pagination' => [
                'current_page' => $blocks->currentPage(),
                'last_page' => $blocks->lastPage(),
                'per_page' => $blocks->perPage(),
                'total' => $blocks->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'blocked_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $blocked = User::find($data['blocked_id']);

        $this->authz->canBlock($user, $blocked)->throwIfDenied();

        $block = Block::create([
            'blocker_id' => $user->id,
            'blocked_id' => $blocked->id,
        ]);

        return response()->json([
            'block' => new BlockResource($block->load('blocked.profile')),
        ], 201);
    }

    public function destroy(Block $block): JsonResponse
    {
        $user = request()->user();

        if ($block->blocker_id !== $user->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You can only unblock users you blocked',
            ], 403);
        }

        $this->authz->canUnblock($user, $block->blocked)->throwIfDenied();

        $block->delete();

        return response()->json([
            'message' => 'User unblocked successfully',
        ]);
    }
}
