<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\Admin\AuditLogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        $user->load([
            'profile.fieldValues',
            'photos',
            'posts.attachment',
            'sentTokes.sender',
            'sentTokes.receiver',
            'receivedTokes.sender',
            'receivedTokes.receiver',
            'matchesAsA.userA',
            'matchesAsA.userB',
            'matchesAsB.userA',
            'matchesAsB.userB',
            'friendshipsAsA.userA',
            'friendshipsAsA.userB',
            'friendshipsAsB.userA',
            'friendshipsAsB.userB',
            'blocksAsBlocker.blocked',
            'blocksAsBlocker.blocker',
            'blocksAsBlocked.blocked',
            'blocksAsBlocked.blocker',
            'verificationRequests',
            'friendshipRequestsSent.requester',
            'friendshipRequestsSent.addressee',
            'friendshipRequestsReceived.requester',
            'friendshipRequestsReceived.addressee',
        ]);

        $user->setRelation('conversations', $user->conversations()->with(['userA', 'userB', 'lastMessage'])->orderBy('created_at', 'desc')->get());

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validate([
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['sometimes', 'string', 'in:user,admin'],
            'status' => ['sometimes', 'string', 'in:active,suspended,banned'],
        ]);

        $user = User::create([
            'id' => Str::uuid()->toString(),
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => $validated['role'] ?? 'user',
            'status' => $validated['status'] ?? 'active',
        ]);

        $this->auditLog->log('user.created', 'User', $user->id, [
            'email' => $user->email,
            'role' => $user->role,
        ], $request->user());

        return response()->json([
            'message' => 'User created successfully',
            'user' => new UserResource($user),
        ], 201);
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $this->authorizeAdmin($request->user());

        $validated = $request->validate([
            'email' => ['sometimes', 'email', 'unique:users,email,'.$user->id],
            'password' => ['sometimes', 'string', 'min:8'],
            'role' => ['sometimes', 'string', 'in:user,admin'],
            'status' => ['sometimes', 'string', 'in:active,suspended,banned'],
        ]);

        if (isset($validated['password'])) {
            $validated['password_hash'] = Hash::make($validated['password']);
            unset($validated['password']);
        }

        $user->update($validated);

        $this->auditLog->log('user.updated', 'User', $user->id, $validated, $request->user());

        return response()->json([
            'message' => 'User updated successfully',
            'user' => new UserResource($user->fresh()),
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

    public function ban(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        if ($user->status === 'banned') {
            return response()->json([
                'message' => 'User is already banned',
            ], 422);
        }

        $user->update(['status' => 'banned']);

        $this->auditLog->log('user.ban', 'User', $user->id, [], request()->user());

        return response()->json([
            'message' => 'User banned successfully',
            'user' => new UserResource($user->fresh()),
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorizeAdmin(request()->user());

        if ($user->id === request()->user()->id) {
            return response()->json([
                'error' => 'Forbidden',
                'message' => 'Cannot delete your own account',
            ], 403);
        }

        $user->update(['status' => 'deleted']);
        $user->delete();

        $this->auditLog->log('user.deleted', 'User', $user->id, [], request()->user());

        return response()->json([
            'message' => 'User deleted successfully',
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
