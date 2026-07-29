<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\UpdateConfigRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ConfigController extends BaseAdminController
{
    public function index(): View
    {
        $response = $this->api->getConfig();
        $data = $response['data'] ?? $response;

        return view('admin.config.index', [
            'configs' => $data['configs'] ?? [],
        ]);
    }

    public function update(UpdateConfigRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $response = $this->api->updateConfig($data);

        return $this->handleApiResponse($response, 'Configuración actualizada correctamente');
    }
}
