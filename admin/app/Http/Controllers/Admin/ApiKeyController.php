<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreApiKeyRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ApiKeyController extends BaseAdminController
{
    public function index(): View
    {
        $response = $this->api->getApiKeys(request()->only(['page', 'per_page']));
        $data = $response['data'] ?? $response;

        return view('admin.api-keys.index', [
            'apiKeys' => $data['api_keys'] ?? $data,
            'pagination' => $data['pagination'] ?? null,
        ]);
    }

    public function create(): View
    {
        return view('admin.api-keys.create');
    }

    public function store(StoreApiKeyRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $response = $this->api->createApiKey($data);

        if (($response['status'] ?? 0) >= 400) {
            return back()->withErrors(['error' => $response['data']['message'] ?? 'Error al crear API Key'])
                ->withInput();
        }

        session()->flash('raw_key', $response['data']['raw_key'] ?? $response['raw_key']);

        return redirect()->route('admin.api-keys.show-created')
            ->with('success', 'API Key creada correctamente');
    }

    public function showCreated(): View
    {
        $rawKey = session('raw_key');

        if (! $rawKey) {
            return redirect()->route('admin.api-keys.index')
                ->with('error', 'No hay clave API para mostrar');
        }

        return view('admin.api-keys.created', [
            'rawKey' => $rawKey,
        ]);
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->api->revokeApiKey($id);

        return $this->handleApiResponse($response, 'API Key revocada correctamente');
    }
}
