<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ChangeRoleRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends BaseAdminController
{
    public function index(): View
    {
        $filters = request()->only(['search', 'role', 'status', 'verification_status', 'page', 'per_page']);
        $response = $this->api->getUsers($filters);

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        return view('admin.users.index', [
            'users' => $response['users'] ?? [],
            'pagination' => $response['pagination'] ?? [],
            'filters' => $filters,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $response = $this->api->createUser($request->validated());

        if (isset($response['error'])) {
            return back()->withInput()->with('error', $response['error']);
        }

        return redirect()->route('admin.users.show', $response['user']['id'])
            ->with('success', 'Usuario creado correctamente');
    }

    public function show(string $id): View
    {
        $response = $this->api->getUser($id);

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        return view('admin.users.show', [
            'user' => $response['user'] ?? [],
        ]);
    }

    public function edit(string $id): View
    {
        $response = $this->api->getUser($id);

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        return view('admin.users.edit', [
            'user' => $response['user'] ?? [],
        ]);
    }

    public function update(UpdateUserRequest $request, string $id): RedirectResponse
    {
        $response = $this->api->updateUser($id, $request->validated());

        if (isset($response['error'])) {
            return back()->withInput()->with('error', $response['error']);
        }

        return redirect()->route('admin.users.show', $id)
            ->with('success', 'Usuario actualizado correctamente');
    }

    public function suspend(string $id): RedirectResponse
    {
        $response = $this->api->suspendUser($id);

        return $this->handleApiResponse($response, 'Usuario suspendido correctamente');
    }

    public function activate(string $id): RedirectResponse
    {
        $response = $this->api->activateUser($id);

        return $this->handleApiResponse($response, 'Usuario activado correctamente');
    }

    public function ban(string $id): RedirectResponse
    {
        if (auth()->user()->backend_id === $id) {
            return back()->with('error', 'No puedes banear tu propia cuenta');
        }

        $response = $this->api->banUser($id);

        return $this->handleApiResponse($response, 'Usuario baneado correctamente');
    }

    public function destroy(string $id): RedirectResponse
    {
        if (auth()->user()->backend_id === $id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta');
        }

        $response = $this->api->deleteUser($id);

        if (isset($response['error'])) {
            return back()->with('error', $response['error']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente');
    }

    public function changeRole(ChangeRoleRequest $request, string $id): RedirectResponse
    {
        if (auth()->user()->backend_id === $id) {
            return back()->with('error', 'No puedes cambiar tu propio rol');
        }

        $response = $this->api->changeUserRole($id, $request->validated()['role']);

        return $this->handleApiResponse($response, 'Rol de usuario actualizado correctamente');
    }

    // === Content Moderation Actions ===

    public function deletePost(string $userId, string $postId): RedirectResponse
    {
        $response = $this->api->deletePost($postId);

        return $this->handleApiResponse($response, 'Post eliminado correctamente');
    }

    public function deletePhoto(string $userId, string $photoId): RedirectResponse
    {
        $response = $this->api->deletePhoto($photoId);

        return $this->handleApiResponse($response, 'Foto eliminada correctamente');
    }

    public function deleteToke(string $userId, string $tokeId): RedirectResponse
    {
        $response = $this->api->deleteToke($tokeId);

        return $this->handleApiResponse($response, 'Toke eliminado correctamente');
    }

    public function deleteMatch(string $userId, string $matchId): RedirectResponse
    {
        $response = $this->api->deleteMatch($matchId);

        return $this->handleApiResponse($response, 'Match eliminado correctamente');
    }

    public function deleteFriendship(string $userId, string $friendshipId): RedirectResponse
    {
        $response = $this->api->deleteFriendship($friendshipId);

        return $this->handleApiResponse($response, 'Amistad eliminada correctamente');
    }

    public function deleteConversation(string $userId, string $conversationId): RedirectResponse
    {
        $response = $this->api->deleteConversation($conversationId);

        return $this->handleApiResponse($response, 'Conversación eliminada correctamente');
    }

    public function deleteMessage(string $userId, string $conversationId, string $messageId): RedirectResponse
    {
        $response = $this->api->deleteMessage($messageId);

        return $this->handleApiResponse($response, 'Mensaje eliminado correctamente');
    }

    public function export()
    {
        $filters = request()->only(['search', 'role', 'status', 'verification_status']);
        $response = $this->api->getUsers(array_merge($filters, ['per_page' => 10000]));

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        $users = $response['users'] ?? [];

        $headers = ['ID', 'Email', 'Rol', 'Estado', 'Verificación', 'Creado', 'Último acceso'];
        $callback = function () use ($users, $headers) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user['id'] ?? '',
                    $user['email'] ?? '',
                    $user['role'] ?? '',
                    $user['status'] ?? '',
                    $user['verification_status'] ?? '',
                    $user['created_at'] ?? '',
                    $user['last_seen_at'] ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="users_export_'.date('Y-m-d').'.csv"',
        ]);
    }
}
