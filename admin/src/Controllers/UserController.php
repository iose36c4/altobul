<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class UserController extends BaseController
{
    public function index(): void
    {
        $client = $this->getClient();
        $result = $client->get('/users');
        $users = $result['data']['data'] ?? $result['data'] ?? [];

        $this->render('users/index', ['users' => $users]);
    }

    public function show(string $id): void
    {
        $client = $this->getClient();
        $result = $client->get('/users/' . $id);
        $user = $result['data']['data'] ?? $result['data'] ?? [];

        $this->render('users/show', ['user' => $user]);
    }

    public function suspend(string $id): void
    {
        $client = $this->getClient();
        $client->post('/users/' . $id . '/suspend');
        $this->redirect('/users/' . $id);
    }

    public function activate(string $id): void
    {
        $client = $this->getClient();
        $client->post('/users/' . $id . '/activate');
        $this->redirect('/users/' . $id);
    }

    public function changeRole(string $id): void
    {
        $client = $this->getClient();
        $client->post('/users/' . $id . '/role', [
            'role' => $_POST['role'] ?? 'user',
        ]);
        $this->redirect('/users/' . $id);
    }
}
