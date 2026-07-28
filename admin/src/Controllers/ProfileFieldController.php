<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class ProfileFieldController extends BaseController
{
    public function index(): void
    {
        $client = $this->getClient();
        $result = $client->get('/profile-fields');
        $fields = $result['data']['data'] ?? $result['data'] ?? [];

        $this->render('profile-fields/index', ['fields' => $fields]);
    }

    public function show(string $id): void
    {
        $client = $this->getClient();
        $result = $client->get('/profile-fields/' . $id);
        $field = $result['data']['data'] ?? $result['data'] ?? [];

        $this->render('profile-fields/show', ['field' => $field]);
    }

    public function store(): void
    {
        $client = $this->getClient();
        $client->post('/profile-fields', [
            'name' => $_POST['name'] ?? '',
            'slug' => $_POST['slug'] ?? '',
            'type' => $_POST['type'] ?? 'text',
            'privacy_default' => $_POST['privacy_default'] ?? 'PUBLIC',
        ]);
        $this->redirect('/profile-fields');
    }

    public function destroy(string $id): void
    {
        $client = $this->getClient();
        $client->delete('/profile-fields/' . $id);
        $this->redirect('/profile-fields');
    }
}
