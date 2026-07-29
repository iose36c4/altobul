<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Admin\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLog
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('verification_status')) {
            $query->where('verification_status', $request->input('verification_status'));
        }

        $users = $query->orderBy('created_at', 'desc')
            ->paginate(min((int) $request->input('per_page', 20), 100));

        return response()->json([
            'users' => UserResource::collection($users),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ],
        ]);
    }

    public function show(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        return response()->json([
            'user' => new UserResource($user->load('profile')),
        ]);
    }

    public function suspend(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        if ($user->status === 'suspended') {
            return response()->json([
                'message' => 'User is already suspended',
            ], 422);
        }

        $user->update(['status' => 'suspended']);

        $this->auditLog->log('user.suspend', 'User', $user->id, [], request()->user());

        return response()->json([
            'message' => 'User suspended successfully',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    public function activate(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        if ($user->status === 'active') {
            return response()->json([
                'message' => 'User is already active',
            ], 422);
        }

        $user->update(['status' => 'active']);

        $this->auditLog->log('user.activate', 'User', $user->id, [], request()->user());

        return response()->json([
            'message' => 'User activated successfully',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    public function changeRole(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $request->validate([
            'role' => ['required', 'string', 'in:user,admin'],
        ]);

        if ($user->id === $request->user()->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Cannot change your own role',
            ], 403);
        }

        if ($user->role === $request->input('role')) {
            return response()->json([
                'message' => 'User already has this role',
            ], 422);
        }

        $oldRole = $user->role;
        $user->update(['role' => $request->input('role')]);

        $this->auditLog->log('user.role_change', 'User', $user->id, [
            'old_role' => $oldRole,
            'new_role' => $request->input('role'),
        ], $request->user());

        return response()->json([
            'message' => 'User role updated successfully',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    private function authorizeAdmin(?User $user): void
    {
        if (! $user || ! $user->isAdmin()) {
            abort(403, 'Administrative privileges required');
        }
    }
}
