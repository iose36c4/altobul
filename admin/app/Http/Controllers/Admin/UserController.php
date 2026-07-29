<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ChangeRoleRequest;
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

    public function changeRole(ChangeRoleRequest $request, string $id): RedirectResponse
    {
        if (auth()->user()->backend_id === $id) {
            return back()->with('error', 'No puedes cambiar tu propio rol');
        }

        $response = $this->api->changeUserRole($id, $request->validated()['role']);

        return $this->handleApiResponse($response, 'Rol de usuario actualizado correctamente');
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
