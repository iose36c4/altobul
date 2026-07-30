<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\ConversationResource;
use App\Http\Resources\FriendshipResource;
use App\Http\Resources\MessageResource;
use App\Http\Resources\PhotoResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\TokeResource;
use App\Http\Resources\UserMatchResource;
use App\Models\Conversation;
use App\Models\Friendship;
use App\Models\Message;
use App\Models\Photo;
use App\Models\Post;
use App\Models\Toke;
use App\Models\User;
use App\Models\UserMatch;
use App\Services\Admin\AuditLogService;
use Illuminate\Http\JsonResponse;

class ContentModerationController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLog
    ) {}

    // === Posts ===

    public function userPosts(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $posts = $user->posts()->with('attachment')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'posts' => PostResource::collection($posts),
        ]);
    }

    public function deletePost(Post $post): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $post->update(['status' => 'REMOVED']);
        $post->delete();

        $this->auditLog->log('post.deleted', 'Post', $post->id, [
            'user_id' => $post->user_id,
        ], request()->user());

        return response()->json(['message' => 'Post removed successfully']);
    }

    // === Photos ===

    public function userPhotos(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $photos = $user->photos()->orderBy('sort_order')->get();

        return response()->json([
            'photos' => PhotoResource::collection($photos),
        ]);
    }

    public function deletePhoto(Photo $photo): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $photo->delete();

        $this->auditLog->log('photo.deleted', 'Photo', $photo->id, [
            'user_id' => $photo->user_id,
        ], request()->user());

        return response()->json(['message' => 'Photo removed successfully']);
    }

    // === Tokes ===

    public function userTokes(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $sent = $user->sentTokes()->with(['sender', 'receiver'])->orderBy('created_at', 'desc')->get();
        $received = $user->receivedTokes()->with(['sender', 'receiver'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'sent_tokes' => TokeResource::collection($sent),
            'received_tokes' => TokeResource::collection($received),
        ]);
    }

    public function deleteToke(Toke $toke): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $toke->delete();

        $this->auditLog->log('toke.deleted', 'Toke', $toke->id, [
            'sender_id' => $toke->sender_id,
            'receiver_id' => $toke->receiver_id,
        ], request()->user());

        return response()->json(['message' => 'Toke removed successfully']);
    }

    // === Matches ===

    public function userMatches(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $matchesAsA = $user->matchesAsA()->with(['userA', 'userB'])->orderBy('created_at', 'desc')->get();
        $matchesAsB = $user->matchesAsB()->with(['userA', 'userB'])->orderBy('created_at', 'desc')->get();

        $matches = $matchesAsA->merge($matchesAsB)->sortByDesc('created_at')->values();

        return response()->json([
            'matches' => UserMatchResource::collection($matches),
        ]);
    }

    public function deleteMatch(UserMatch $match): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $match->delete();

        $this->auditLog->log('match.deleted', 'UserMatch', $match->id, [
            'user_a_id' => $match->user_a_id,
            'user_b_id' => $match->user_b_id,
        ], request()->user());

        return response()->json(['message' => 'Match removed successfully']);
    }

    // === Friendships ===

    public function userFriendships(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $friendshipsAsA = $user->friendshipsAsA()->with(['userA', 'userB'])->orderBy('created_at', 'desc')->get();
        $friendshipsAsB = $user->friendshipsAsB()->with(['userA', 'userB'])->orderBy('created_at', 'desc')->get();

        $friendships = $friendshipsAsA->merge($friendshipsAsB)->sortByDesc('created_at')->values();

        return response()->json([
            'friendships' => FriendshipResource::collection($friendships),
        ]);
    }

    public function deleteFriendship(Friendship $friendship): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $friendship->delete();

        $this->auditLog->log('friendship.deleted', 'Friendship', $friendship->id, [
            'user_a_id' => $friendship->user_a_id,
            'user_b_id' => $friendship->user_b_id,
        ], request()->user());

        return response()->json(['message' => 'Friendship removed successfully']);
    }

    // === Conversations ===

    public function userConversations(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $conversations = $user->conversations()->with(['userA', 'userB', 'lastMessage'])->orderBy('created_at', 'desc')->get();

        return response()->json([
            'conversations' => ConversationResource::collection($conversations),
        ]);
    }

    public function conversationMessages(Conversation $conversation): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $messages = $conversation->messages()->with('sender')->orderBy('created_at', 'asc')->get();

        return response()->json([
            'messages' => MessageResource::collection($messages),
        ]);
    }

    public function deleteConversation(Conversation $conversation): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $conversation->messages()->delete();
        $conversation->delete();

        $this->auditLog->log('conversation.deleted', 'Conversation', $conversation->id, [
            'user_a_id' => $conversation->user_a_id,
            'user_b_id' => $conversation->user_b_id,
        ], request()->user());

        return response()->json(['message' => 'Conversation removed successfully']);
    }

    public function deleteMessage(Message $message): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        $message->delete();

        $this->auditLog->log('message.deleted', 'Message', $message->id, [
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
        ], request()->user());

        return response()->json(['message' => 'Message removed successfully']);
    }

    private function authorizeAdmin(?User $user): void
    {
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Administrative privileges required');
        }
    }
}
