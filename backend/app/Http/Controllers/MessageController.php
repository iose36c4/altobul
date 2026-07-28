<?php

namespace App\Http\Controllers;

use App\Events\Broadcast\NewMessage;
use App\Http\Resources\MessageResource;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Conversation $conversation): JsonResponse
    {
        $user = request()->user();

        $this->authz->canViewConversation($user, $conversation)->throwIfDenied();

        $messages = $conversation->messages()
            ->with('sender')
            ->latest()
            ->paginate(50);

        return response()->json([
            'messages' => MessageResource::collection($messages),
            'pagination' => [
                'current_page' => $messages->currentPage(),
                'last_page' => $messages->lastPage(),
                'per_page' => $messages->perPage(),
                'total' => $messages->total(),
            ],
        ]);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();

        $this->authz->canSendMessage($user, $conversation)->throwIfDenied();

        if (! $conversation->isActive()) {
            return response()->json([
                'error' => 'Invalid state',
                'message' => 'This conversation has ended',
            ], 422);
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
        ]);

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => $user->id,
            'content' => $data['content'],
        ]);

        $conversation->update(['updated_at' => now()]);

        // Broadcast new message event
        broadcast(new NewMessage($message->load('sender')));

        return response()->json([
            'message' => new MessageResource($message->load('sender')),
        ], 201);
    }
}
