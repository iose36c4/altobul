<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class VerificationController extends BaseController
{
    public function index(): void
    {
        $client = $this->getClient();
        $result = $client->get('/verification-requests');
        $requests = $result['data']['data'] ?? $result['data'] ?? [];

        $this->render('verification/index', ['requests' => $requests]);
    }

    public function show(string $id): void
    {
        $client = $this->getClient();
        $result = $client->get('/verification-requests/' . $id);
        $request = $result['data']['data'] ?? $result['data'] ?? [];

        $this->render('verification/show', ['request' => $request]);
    }

    public function approve(string $id): void
    {
        $client = $this->getClient();
        $client->post('/verification-requests/' . $id . '/approve');
        $this->redirect('/verifications');
    }

    public function reject(string $id): void
    {
        $client = $this->getClient();
        $client->post('/verification-requests/' . $id . '/reject');
        $this->redirect('/verifications');
    }
}
