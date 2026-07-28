<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Services\Discovery\DiscoveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DiscoveryController extends Controller
{
    public function __construct(
        private DiscoveryService $discovery,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $filters = $this->extractFilters($request);
        $perPage = min((int) $request->get('limit', 20), 50);

        $results = $this->discovery->discover($user, $filters, $perPage);

        return $this->paginatedResponse($results);
    }

    public function online(Request $request): JsonResponse
    {
        $user = $request->user();

        $filters = $this->extractFilters($request);
        $perPage = min((int) $request->get('limit', 20), 50);

        $results = $this->discovery->discoverOnline($user, $filters, $perPage);

        return $this->paginatedResponse($results);
    }

    public function recent(Request $request): JsonResponse
    {
        $user = $request->user();

        $filters = $this->extractFilters($request);
        $perPage = min((int) $request->get('limit', 20), 50);

        $results = $this->discovery->discoverRecent($user, $filters, $perPage);

        return $this->paginatedResponse($results);
    }

    public function nearby(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user->profile || ! $user->profile->location) {
            return response()->json([
                'error' => 'Location required',
                'message' => 'Your profile must have a location set to use nearby discovery',
            ], 422);
        }

        $filters = $this->extractFilters($request);
        $perPage = min((int) $request->get('limit', 20), 50);

        $results = $this->discovery->discoverNearby($user, $filters, $perPage);

        return $this->paginatedResponse($results);
    }

    private function extractFilters(Request $request): array
    {
        $filters = [];

        if ($request->has('verified_only')) {
            $filters['verified_only'] = filter_var($request->get('verified_only'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->has('order')) {
            $filters['order'] = $request->get('order');
        }

        if ($request->has('fields')) {
            $filters['fields'] = $request->get('fields');
        }

        return $filters;
    }

    private function paginatedResponse(LengthAwarePaginator $paginator): JsonResponse
    {
        return response()->json([
            'users' => UserResource::collection($paginator->items()),
            'pagination' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }
}
