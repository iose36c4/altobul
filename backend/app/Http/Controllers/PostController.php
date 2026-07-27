<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $posts = Post::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->where('expires_at', '>', now())
            ->latest()
            ->paginate(20);

        return response()->json([
            'posts' => PostResource::collection($posts),
            'pagination' => [
                'current_page' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'per_page' => $posts->perPage(),
                'total' => $posts->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'content' => ['required', 'string', 'max:5000'],
            'visibility' => ['required', 'string', 'in:PUBLIC,MATCH,FRIENDS,PRIVATE'],
            'requires_verified' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'after:now', 'before_or_equal:'.now()->addDay()->toDateTimeString()],
        ]);

        $expiresAt = isset($data['expires_at'])
            ? min(now()->addDay()->timestamp, strtotime($data['expires_at']))
            : now()->addDay()->timestamp;

        $post = Post::create([
            'user_id' => $user->id,
            'content_md' => $data['content'],
            'visibility' => $data['visibility'],
            'requires_verified' => $data['requires_verified'] ?? false,
            'expires_at' => now()->setTimestamp($expiresAt),
            'status' => 'ACTIVE',
        ]);

        return response()->json([
            'post' => new PostResource($post->load(['user', 'attachment'])),
        ], 201);
    }

    public function show(Post $post): JsonResponse
    {
        if ($post->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'Not found',
                'message' => 'This post is no longer available',
            ], 404);
        }

        $user = request()->user();

        $this->authz->canViewPost($user, $post->user, $post->id)->throwIfDenied();

        return response()->json([
            'post' => new PostResource($post->load(['user', 'attachment'])),
        ]);
    }

    public function destroy(Post $post): JsonResponse
    {
        $user = request()->user();

        if ($post->user_id !== $user->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You can only delete your own posts',
            ], 403);
        }

        if ($post->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'Invalid state',
                'message' => 'This post is not active',
            ], 422);
        }

        $post->update(['status' => 'DELETED']);

        return response()->json([
            'post' => new PostResource($post->fresh()),
        ]);
    }
}
