<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateFieldValueRequest;
use App\Http\Requests\Profile\UpdateLocationRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\ProfileFieldDefinitionResource;
use App\Http\Resources\ProfileFieldValueResource;
use App\Http\Resources\ProfileResource;
use App\Models\ProfileFieldDefinition;
use App\Models\User;
use App\Services\Profile\ProfileFieldService;
use App\Services\Profile\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(
        private ProfileService $profileService,
        private ProfileFieldService $profileFieldService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('view', $user);

        return response()->json([
            'profile' => new ProfileResource($user->profile),
        ]);
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('update', $user->profile);

        $profile = $this->profileService->updateFixedFields($user, $request->validated());

        return response()->json([
            'profile' => new ProfileResource($profile),
        ]);
    }

    public function updateLocation(UpdateLocationRequest $request): JsonResponse
    {
        $user = $request->user();
        $this->authorize('update', $user->profile);

        $profile = $this->profileService->updateLocation($user, $request->validated());

        return response()->json([
            'profile' => new ProfileResource($profile),
        ]);
    }

    public function getField(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $field = ProfileFieldDefinition::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $value = $this->profileFieldService->getValue($user->profile, $slug);

        if (! $value) {
            return response()->json(['value' => null], 200);
        }

        $this->authorize('viewField', $value);

        return response()->json([
            'value' => new ProfileFieldValueResource($value),
        ]);
    }

    public function setField(UpdateFieldValueRequest $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $field = ProfileFieldDefinition::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // User can always update their own field values
        $value = $this->profileFieldService->setValue($user->profile, $field, $request->input('value'));

        return response()->json([
            'value' => new ProfileFieldValueResource($value->load('field', 'selectedOptions')),
        ]);
    }

    public function deleteField(Request $request, string $slug): JsonResponse
    {
        $user = $request->user();
        $field = ProfileFieldDefinition::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $deleted = $this->profileFieldService->deleteValue($user->profile, $slug);

        return response()->json([
            'deleted' => $deleted,
        ]);
    }

    public function listFields(Request $request): JsonResponse
    {
        $fields = ProfileFieldDefinition::active()->orderBy('sort_order')->get();

        return response()->json([
            'fields' => ProfileFieldDefinitionResource::collection($fields),
        ]);
    }
}
