<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotoResource;
use App\Http\Resources\PostResource;
use App\Http\Resources\PublicProfileResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        $viewer = request()->user();
        $profile = $user->profile;
        $profile->load('fieldValues.field.selectedOptions');

        return response()->json([
            'user' => new UserResource($user),
            'profile' => new PublicProfileResource($profile, app(AuthorizationService::class), $viewer),
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
            'photos' => PhotoResource::collection($photos),
        ]);
    }

    public function posts(User $user): JsonResponse
    {
        $posts = $user->posts()
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'posts' => PostResource::collection($posts),
        ]);
    }
}
