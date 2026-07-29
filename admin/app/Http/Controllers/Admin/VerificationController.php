<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\RejectVerificationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VerificationController extends BaseAdminController
{
    public function index(): View
    {
        $filters = request()->only(['status', 'method', 'date_from', 'date_to', 'page', 'per_page']);
        $response = $this->api->getVerificationRequests($filters);
        $data = $response['data'] ?? $response;

        return view('admin.verifications.index', [
            'requests' => $data['requests'] ?? [],
            'pagination' => $data['pagination'] ?? null,
            'filters' => $filters,
        ]);
    }

    public function show(string $id): View
    {
        $response = $this->api->getVerificationRequest($id);
        $data = $response['data'] ?? $response;

        return view('admin.verifications.show', [
            'verification' => $data['request'] ?? $data,
        ]);
    }

    public function approve(string $id): RedirectResponse
    {
        $response = $this->api->approveVerification($id);

        return $this->handleApiResponse($response, 'Verificación aprobada correctamente');
    }

    public function reject(RejectVerificationRequest $request, string $id): RedirectResponse
    {
        $data = $request->validated();
        $response = $this->api->rejectVerification($id, $data['rejection_reason']);

        return $this->handleApiResponse($response, 'Verificación rechazada correctamente');
    }
}
