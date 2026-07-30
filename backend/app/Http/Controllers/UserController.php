<?php

namespace App\Http\Controllers;

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

        if ($profile) {
            $profile->load('fieldValues.field.selectedOptions');
        }

        return response()->json([
            'user' => new UserResource($user),
            'profile' => $profile ? new PublicProfileResource($profile, app(AuthorizationService::class), $viewer) : null,
        ]);
    }
}
