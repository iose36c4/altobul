<?php

namespace App\Http\Controllers;

use App\Http\Resources\PhotoResource;
use App\Models\Photo;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $photos = Photo::where('user_id', $user->id)
            ->where('status', 'ACTIVE')
            ->latest()
            ->paginate(20);

        return response()->json([
            'photos' => PhotoResource::collection($photos),
            'pagination' => [
                'current_page' => $photos->currentPage(),
                'last_page' => $photos->lastPage(),
                'per_page' => $photos->perPage(),
                'total' => $photos->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'storage_key' => ['required', 'string', 'max:500'],
            'visibility' => ['required', 'string', 'in:PUBLIC,MATCH,FRIENDS,PRIVATE'],
            'requires_verified' => ['nullable', 'boolean'],
        ]);

        $photo = Photo::create([
            'user_id' => $user->id,
            'storage_key' => $data['storage_key'],
            'mime_type' => 'image/jpeg',
            'width' => 1,
            'height' => 1,
            'size_bytes' => 1,
            'visibility' => $data['visibility'],
            'requires_verified' => $data['requires_verified'] ?? false,
            'status' => 'ACTIVE',
        ]);

        return response()->json([
            'photo' => new PhotoResource($photo->fresh()),
        ], 201);
    }

    public function show(Photo $photo): JsonResponse
    {
        if ($photo->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'Not found',
                'message' => 'This photo is no longer available',
            ], 404);
        }

        $user = request()->user();

        $result = $this->authz->canViewPhoto($user, $photo->user, $photo->id);
        $result->throwIfDenied();

        return response()->json([
            'photo' => new PhotoResource($photo->fresh()),
        ]);
    }

    public function destroy(Photo $photo): JsonResponse
    {
        $user = request()->user();

        if ($photo->user_id !== $user->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'You can only delete your own photos',
            ], 403);
        }

        if ($photo->status !== 'ACTIVE') {
            return response()->json([
                'error' => 'Invalid state',
                'message' => 'This photo is not active',
            ], 422);
        }

        $photo->update(['status' => 'DELETED']);

        return response()->json([
            'photo' => new PhotoResource($photo->fresh()),
        ]);
    }
}
