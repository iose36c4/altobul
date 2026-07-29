<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreGeoZoneRequest;
use App\Http\Requests\Admin\StorePolygonRequest;
use App\Http\Requests\Admin\UpdateGeoZoneRequest;
use App\Http\Requests\Admin\UpdatePolygonRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class GeoZoneController extends BaseAdminController
{
    public function index(): View
    {
        $response = $this->api->getGeoZones(request()->only(['page', 'per_page']));

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        return view('admin.geo-zones.index', [
            'zones' => $response['zones'] ?? [],
            'pagination' => $response['pagination'] ?? [],
        ]);
    }

    public function create(): View
    {
        return view('admin.geo-zones.create');
    }

    public function store(StoreGeoZoneRequest $request): RedirectResponse
    {
        $data = $request->validated();
        // polygons comes as array of {name, geometry, sort_order}
        $response = $this->api->createGeoZone($data);

        return $this->handleApiResponse($response, 'GeoZona creada correctamente');
    }

    public function show(string $id): View
    {
        $response = $this->api->getGeoZone($id);

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        return view('admin.geo-zones.show', [
            'zone' => $response['zone'] ?? [],
        ]);
    }

    public function edit(string $id): View
    {
        $response = $this->api->getGeoZone($id);

        if (isset($response['error'])) {
            return $this->apiError(['error' => $response['error']]);
        }

        return view('admin.geo-zones.edit', [
            'zone' => $response['zone'] ?? [],
        ]);
    }

    public function update(UpdateGeoZoneRequest $request, string $id): RedirectResponse
    {
        $response = $this->api->updateGeoZone($id, $request->validated());

        return $this->handleApiResponse($response, 'GeoZona actualizada correctamente');
    }

    public function destroy(string $id): RedirectResponse
    {
        $response = $this->api->deleteGeoZone($id);

        return $this->handleApiResponse($response, 'GeoZona eliminada correctamente');
    }

    public function addPolygon(StorePolygonRequest $request, string $zoneId): RedirectResponse
    {
        $response = $this->api->addPolygon($zoneId, $request->validated());

        return $this->handleApiResponse($response, 'Polígono agregado correctamente');
    }

    public function updatePolygon(UpdatePolygonRequest $request, string $zoneId, string $polygonId): RedirectResponse
    {
        $response = $this->api->updatePolygon($zoneId, $polygonId, $request->validated());

        return $this->handleApiResponse($response, 'Polígono actualizado correctamente');
    }

    public function deletePolygon(string $zoneId, string $polygonId): RedirectResponse
    {
        $response = $this->api->deletePolygon($zoneId, $polygonId);

        return $this->handleApiResponse($response, 'Polígono eliminado correctamente');
    }
}
