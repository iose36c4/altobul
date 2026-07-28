<?php

declare(strict_types=1);

namespace App\Http\Controllers;

class GeoZoneController extends BaseController
{
    public function index(): void
    {
        $client = $this->getClient();
        $result = $client->get('/geo-zones');
        $zones = $result['data']['data'] ?? $result['data'] ?? [];

        $this->render('geo-zones/index', ['zones' => $zones]);
    }

    public function show(string $id): void
    {
        $client = $this->getClient();
        $result = $client->get('/geo-zones/' . $id);
        $zone = $result['data']['data'] ?? $result['data'] ?? [];

        $this->render('geo-zones/show', ['zone' => $zone]);
    }

    public function store(): void
    {
        $client = $this->getClient();
        $client->post('/geo-zones', [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
        ]);
        $this->redirect('/geo-zones');
    }

    public function update(string $id): void
    {
        $client = $this->getClient();
        $client->put('/geo-zones/' . $id, [
            'name' => $_POST['name'] ?? '',
            'description' => $_POST['description'] ?? '',
        ]);
        $this->redirect('/geo-zones/' . $id);
    }

    public function destroy(string $id): void
    {
        $client = $this->getClient();
        $client->delete('/geo-zones/' . $id);
        $this->redirect('/geo-zones');
    }
}
