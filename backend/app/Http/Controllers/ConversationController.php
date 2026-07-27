<?php

namespace App\Http\Controllers;

use App\Http\Resources\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $conversations = Conversation::where(function ($q) use ($user) {
            $q->where('user_a_id', $user->id)
                ->orWhere('user_b_id', $user->id);
        })->where('status', 'ACTIVE')
            ->with(['userA.profile', 'userB.profile', 'lastMessage.sender'])
            ->latest('updated_at')
            ->paginate(20);

        return response()->json([
            'conversations' => ConversationResource::collection($conversations),
            'pagination' => [
                'current_page' => $conversations->currentPage(),
                'last_page' => $conversations->lastPage(),
                'per_page' => $conversations->perPage(),
                'total' => $conversations->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'recipient_id' => ['required', 'uuid', 'exists:users,id'],
        ]);

        $recipient = User::find($data['recipient_id']);

        $this->authz->canStartConversation($user, $recipient)->throwIfDenied();

        $conversation = Conversation::between($user, $recipient)->first();

        if (! $conversation) {
            $conversation = Conversation::create([
                'user_a_id' => min($user->id, $recipient->id),
                'user_b_id' => max($user->id, $recipient->id),
                'status' => 'ACTIVE',
            ]);
        } elseif (! $conversation->isActive()) {
            $conversation->update([
                'status' => 'ACTIVE',
                'ended_at' => null,
                'ended_by' => null,
            ]);
        }

        return response()->json([
            'conversation' => new ConversationResource($conversation->load(['userA.profile', 'userB.profile'])),
        ], 201);
    }

    public function show(Conversation $conversation): JsonResponse
    {
        $user = request()->user();

        $this->authz->canViewConversation($user, $conversation)->throwIfDenied();

        return response()->json([
            'conversation' => new ConversationResource($conversation->load(['userA.profile', 'userB.profile', 'messages.sender'])),
        ]);
    }

    public function destroy(Conversation $conversation): JsonResponse
    {
        $user = request()->user();

        $this->authz->canAccessConversation($user, $conversation)->throwIfDenied();

        if ($conversation->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'Invalid state',
                'message' => 'This conversation is not active',
            ], 422);
        }

        $conversation->update([
            'status' => 'ENDED',
            'ended_at' => now(),
            'ended_by' => $user->id,
        ]);

        return response()->json([
            'conversation' => new ConversationResource($conversation->fresh()),
        ]);
    }
}
