<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\ReorderProfileFieldsRequest;
use App\Http\Requests\Admin\StoreProfileFieldDefinitionRequest;
use App\Http\Requests\Admin\UpdateProfileFieldDefinitionRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileFieldController extends BaseAdminController
{
    public function index(): View
    {
        $response = $this->api->getProfileFields();

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        return view('admin.profile-fields.index', [
            'fields' => $response['fields'] ?? [],
        ]);
    }

    public function create(): View
    {
        return view('admin.profile-fields.create');
    }

    public function store(StoreProfileFieldDefinitionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $options = $data['options'] ?? [];
        unset($data['options']);

        // Backend expects options in a specific format
        $payload = array_merge($data, ['options' => $options]);
        $response = $this->api->createProfileField($payload);

        return $this->handleApiResponse($response, 'Campo de perfil creado correctamente');
    }

    public function edit(string $id): View
    {
        $response = $this->api->getProfileField($id);

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        return view('admin.profile-fields.edit', [
            'field' => $response['field'] ?? [],
        ]);
    }

    public function update(UpdateProfileFieldDefinitionRequest $request, string $id): RedirectResponse
    {
        $data = $request->validated();
        $options = $data['options'] ?? null;
        unset($data['options']);

        $payload = array_merge($data, ['options' => $options]);
        $response = $this->api->updateProfileField($id, $payload);

        return $this->handleApiResponse($response, 'Campo de perfil actualizado correctamente');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->api->deleteProfileField($id);

        return $this->handleApiResponse($response, 'Campo de perfil eliminado correctamente');
    }

    public function reorder(ReorderProfileFieldsRequest $request): RedirectResponse
    {
        $response = $this->api->reorderProfileFields($request->validated()['ids']);

        return $this->handleApiResponse($response, 'Orden actualizado correctamente');
    }
}
