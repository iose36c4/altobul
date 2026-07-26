<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show(User $user): JsonResponse
    {
        \Log::info('UserController@show', ['user_id' => $user->id, 'user' => $user]);
        $this->authorize('view', $user);
        
        return response()->json([
            'user' => new UserResource($user->load('profile')),
        ]);
    }

    public function photos(User $user): JsonResponse
    {
        $photos = $user->photos()
            ->where('status', 'ACTIVE')
            ->whereNull('deleted_at')
            ->orderBy('sort_order')
            ->get();
        
        return response()->json([
            'photos' => \App\Http\Resources\PhotoResource::collection($photos),
        ]);
    }

    public function posts(User $user): JsonResponse
    {
        $posts = $user->posts()
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();
        
        return response()->json([
            'posts' => \App\Http\Resources\PostResource::collection($posts),
        ]);
    }
}