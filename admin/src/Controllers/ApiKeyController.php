<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class ApiKeyController extends BaseController
{
    public function index(): void
    {
        $client = $this->getClient();
        $result = $client->get('/api-keys');
        $keys = $result['data']['data'] ?? $result['data'] ?? [];

        $this->render('api-keys/index', ['keys' => $keys]);
    }

    public function create(): void
    {
        $this->render('api-keys/create');
    }

    public function store(): void
    {
        $client = $this->getClient();

        $result = $client->post('/api-keys', [
            'name' => $_POST['name'] ?? '',
            'type' => $_POST['type'] ?? 'CLIENT',
            'expires_in_days' => ! empty($_POST['expires_in_days']) ? (int) $_POST['expires_in_days'] : null,
        ]);

        $rawKey = $result['data']['raw_key'] ?? $result['data']['data']['raw_key'] ?? null;

        if ($rawKey) {
            $_SESSION['new_key'] = $rawKey;
            $_SESSION['new_key_name'] = $_POST['name'] ?? '';
            $this->redirect('/api-keys/created');
            exit;
        }

        $this->redirect('/api-keys');
    }

    public function created(): void
    {
        $rawKey = $_SESSION['new_key'] ?? null;
        $keyName = $_SESSION['new_key_name'] ?? '';
        unset($_SESSION['new_key'], $_SESSION['new_key_name']);

        if (! $rawKey) {
            $this->redirect('/api-keys');
            exit;
        }

        $this->render('api-keys/created', ['rawKey' => $rawKey, 'keyName' => $keyName]);
    }

    public function revoke(string $id): void
    {
        $client = $this->getClient();
        $client->delete('/api-keys/' . $id);
        $this->redirect('/api-keys');
    }
}
