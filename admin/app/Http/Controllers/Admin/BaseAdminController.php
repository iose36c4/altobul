<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Middleware\AdminWebGuardMiddleware;
use App\Services\BackendApiService;
use Illuminate\Http\RedirectResponse;

abstract class BaseAdminController extends Controller
{
    protected BackendApiService $api;

    public function __construct(BackendApiService $api)
    {
        $this->api = $api;
        $this->middleware(AdminWebGuardMiddleware::class);
    }

    protected function apiError($response, string $default = 'Error en la operación'): RedirectResponse
    {
        $message = $response['error'] ?? $default;
        if (isset($response['errors'])) {
            return back()->withErrors($response['errors'])->withInput()->with('error', $message);
        }

        return back()->with('error', $message);
    }

    protected function apiSuccess(string $message = 'Operación exitosa'): RedirectResponse
    {
        return back()->with('success', $message);
    }

    protected function handleApiResponse($response, string $successMessage = 'Operación exitosa'): RedirectResponse
    {
        if (isset($response['error'])) {
            return $this->apiError($response);
        }

        return $this->apiSuccess($successMessage);
    }
}
