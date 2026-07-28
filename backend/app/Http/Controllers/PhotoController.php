<?php

namespace App\Http\Controllers;

use App\Exceptions\PhotoLimitReachedException;
use App\Http\Resources\PhotoResource;
use App\Jobs\ProcessPhotoDeletionJob;
use App\Models\Photo;
use App\Services\Authorization\AuthorizationService;
use App\Services\Photo\PhotoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PhotoController extends Controller
{
    public function __construct(
        private AuthorizationService $authz,
        private PhotoService $photoService,
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

        try {
            $data = $request->validate([
                'photo' => ['required', 'file', 'max:10240', 'mimes:jpeg,png,webp'],
                'visibility' => ['required', 'string', 'in:PUBLIC,MATCH,FRIENDS,PRIVATE'],
                'requires_verified' => ['nullable', 'boolean'],
            ]);
        } catch (ValidationException $e) {
            throw $e;
        }

        try {
            $photo = $this->photoService->upload(
                $user,
                $request->file('photo'),
                $data['visibility'],
                $data['requires_verified'] ?? false,
            );
        } catch (PhotoLimitReachedException $e) {
            return response()->json([
                'error' => 'Limit reached',
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'photo' => new PhotoResource($photo),
        ], 202);
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
            'photo' => new PhotoResource($photo),
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

        $storageKey = $photo->storage_key;

        $photo->update(['status' => 'DELETED']);

        if ($storageKey && str_starts_with($storageKey, 'users/')) {
            ProcessPhotoDeletionJob::dispatch($storageKey);
        }

        return response()->json([
            'photo' => new PhotoResource($photo->fresh()),
        ]);
    }
}
