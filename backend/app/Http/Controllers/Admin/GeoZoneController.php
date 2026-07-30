<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MergeGeoZonesRequest;
use App\Http\Requests\Admin\SubtractGeoZonesRequest;
use App\Models\ApiKey;
use App\Models\GeoPolygon;
use App\Models\GeoZone;
use App\Rules\GeoJsonPolygon;
use App\Services\Admin\AuditLogService;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GeoZoneController extends Controller
{
    public function __construct(
        protected AuditLogService $auditLog
    ) {}

    protected function getActingUser(Request $request)
    {
        if ($user = $request->user()) {
            return $user;
        }

        $apiKey = $request->attributes->get('api_key');
        if ($apiKey instanceof ApiKey && $apiKey->created_by) {
            return $apiKey->creator;
        }

        return null;
    }

    public function index(Request $request): JsonResponse
    {
        $zones = GeoZone::with('polygons')
            ->orderBy('name')
            ->paginate(min((int) $request->query('per_page', 20), 100));

        return response()->json([
            'zones' => $zones->map(fn ($z) => [
                'id' => $z->id,
                'name' => $z->name,
                'description' => $z->description,
                'is_active' => $z->is_active,
                'created_by' => $z->created_by,
                'created_at' => $z->created_at?->toISOString(),
                'polygons' => $z->polygons->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'geometry' => $p->geometry,
                    'sort_order' => $p->sort_order,
                ]),
            ]),
            'pagination' => [
                'current_page' => $zones->currentPage(),
                'last_page' => $zones->lastPage(),
                'per_page' => $zones->perPage(),
                'total' => $zones->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'polygons' => ['required', 'array', 'min:1'],
            'polygons.*.name' => ['required', 'string', 'max:100'],
            'polygons.*.geometry' => ['required', new GeoJsonPolygon],
            'polygons.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $polygons = $data['polygons'];
        unset($data['polygons']);

        try {
            $zone = DB::transaction(function () use ($data, $polygons, $request) {
                $actingUser = $this->getActingUser($request);
                $data['created_by'] = $actingUser?->id;
                $zone = GeoZone::create($data);

                foreach ($polygons as $index => $polygon) {
                    GeoPolygon::create([
                        'zone_id' => $zone->id,
                        'name' => $polygon['name'],
                        'geometry' => $polygon['geometry'],
                        'sort_order' => $polygon['sort_order'] ?? $index,
                    ]);
                }

                return $zone->load('polygons');
            });
        } catch (QueryException $e) {
            if ($e->getCode() === '23514' || str_contains($e->getMessage(), 'ST_IsValid')) {
                return response()->json(['message' => 'Geometría inválida: el polígono no es válido según PostGIS (ej. auto-intersección, anillos incorrectos)'], 422);
            }
            throw $e;
        }

        $actingUser = $this->getActingUser($request);
        $this->auditLog->log('geo_zone.create', 'GeoZone', $zone->id, [
            'zone_id' => $zone->id,
            'polygons_count' => $zone->polygons->count(),
        ], $actingUser, $request);

        return response()->json([
            'zone' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'description' => $zone->description,
                'is_active' => $zone->is_active,
                'created_at' => $zone->created_at?->toISOString(),
                'polygons' => $zone->polygons->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'geometry' => $p->geometry,
                    'sort_order' => $p->sort_order,
                ]),
            ],
        ], 201);
    }

    public function show(GeoZone $zone): JsonResponse
    {
        $zone->load('polygons');

        return response()->json([
            'zone' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'description' => $zone->description,
                'is_active' => $zone->is_active,
                'created_by' => $zone->created_by,
                'created_at' => $zone->created_at?->toISOString(),
                'updated_at' => $zone->updated_at?->toISOString(),
                'polygons' => $zone->polygons->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'geometry' => $p->geometry,
                    'sort_order' => $p->sort_order,
                ]),
            ],
        ]);
    }

    public function update(Request $request, GeoZone $zone): JsonResponse
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ]);

        $zone->update($data);

        $actingUser = $this->getActingUser($request);
        $this->auditLog->log('geo_zone.update', 'GeoZone', $zone->id, [
            'changes' => $data,
        ], $actingUser, $request);

        $zone->load('polygons');

        return response()->json([
            'zone' => [
                'id' => $zone->id,
                'name' => $zone->name,
                'description' => $zone->description,
                'is_active' => $zone->is_active,
                'created_by' => $zone->created_by,
                'created_at' => $zone->created_at?->toISOString(),
                'updated_at' => $zone->updated_at?->toISOString(),
                'polygons' => $zone->polygons->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'geometry' => $p->geometry,
                    'sort_order' => $p->sort_order,
                ]),
            ],
        ]);
    }

    public function destroy(GeoZone $zone): JsonResponse
    {
        $zoneId = $zone->id;
        $zone->delete();

        $this->auditLog->log('geo_zone.delete', 'GeoZone', $zoneId, [], $this->getActingUser(request()), request());

        return response()->json(['message' => 'Zone deleted']);
    }

    public function addPolygon(Request $request, GeoZone $zone): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'geometry' => ['required', new GeoJsonPolygon],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $maxSort = $zone->polygons()->max('sort_order') ?? 0;
        $data['zone_id'] = $zone->id;
        $data['sort_order'] = $data['sort_order'] ?? $maxSort + 1;

        try {
            $polygon = GeoPolygon::create($data);
        } catch (QueryException $e) {
            if ($e->getCode() === '23514' || str_contains($e->getMessage(), 'ST_IsValid')) {
                return response()->json(['message' => 'Geometría inválida: el polígono no es válido según PostGIS (ej. auto-intersección, anillos incorrectos)'], 422);
            }
            throw $e;
        }

        $this->auditLog->log('geo_zone.update', 'GeoZone', $zone->id, [
            'action' => 'polygon_added',
            'polygon_id' => $polygon->id,
            'polygon_name' => $polygon->name,
        ], $this->getActingUser($request), $request);

        return response()->json([
            'polygon' => [
                'id' => $polygon->id,
                'name' => $polygon->name,
                'geometry' => $polygon->geometry,
                'sort_order' => $polygon->sort_order,
            ],
        ], 201);
    }

    public function updatePolygon(Request $request, GeoZone $zone, GeoPolygon $polygon): JsonResponse
    {
        if ($polygon->zone_id !== $zone->id) {
            return response()->json(['message' => 'Polygon not found in zone'], 404);
        }

        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'geometry' => ['sometimes', new GeoJsonPolygon],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ]);

        try {
            $polygon->update($data);
        } catch (QueryException $e) {
            if ($e->getCode() === '23514' || str_contains($e->getMessage(), 'ST_IsValid')) {
                return response()->json(['message' => 'Geometría inválida: el polígono no es válido según PostGIS'], 422);
            }
            throw $e;
        }

        $this->auditLog->log('geo_zone.update', 'GeoZone', $zone->id, [
            'action' => 'polygon_updated',
            'polygon_id' => $polygon->id,
            'changes' => $data,
        ], $this->getActingUser($request), $request);

        return response()->json([
            'polygon' => [
                'id' => $polygon->id,
                'name' => $polygon->name,
                'geometry' => $polygon->geometry,
                'sort_order' => $polygon->sort_order,
            ],
        ]);
    }

    public function deletePolygon(GeoZone $zone, GeoPolygon $polygon): JsonResponse
    {
        if ($polygon->zone_id !== $zone->id) {
            return response()->json(['message' => 'Polygon not found in zone'], 404);
        }

        $polygonId = $polygon->id;
        $polygon->delete();

        $this->auditLog->log('geo_zone.update', 'GeoZone', $zone->id, [
            'action' => 'polygon_deleted',
            'polygon_id' => $polygonId,
        ], $this->getActingUser($request), $request);

        return response()->json(['message' => 'Polygon deleted']);
    }

    public function merge(MergeGeoZonesRequest $request): JsonResponse
    {
        $zoneA = GeoZone::with('polygons')->findOrFail($request->zone_a_id);
        $zoneB = GeoZone::with('polygons')->findOrFail($request->zone_b_id);

        if ($zoneA->polygons->isEmpty() || $zoneB->polygons->isEmpty()) {
            return response()->json(['message' => 'Ambas zonas deben tener al menos un polígono.'], 422);
        }

        $zoneIds = [$zoneA->id, $zoneB->id];

        try {
            $result = DB::transaction(function () use ($request, $zoneA, $zoneB, $zoneIds) {
                // Ejecutar unión PostGIS
                $unionGeom = DB::selectOne(
                    'SELECT ST_AsGeoJSON(ST_MakeValid(ST_Union(ST_Collect(ST_GeomFromGeoJSON(geometry))))) as geometry
                     FROM geo_polygons WHERE zone_id IN (?, ?)',
                    [$zoneA->id, $zoneB->id]
                );

                if (! $unionGeom || ! $unionGeom->geometry) {
                    throw new \Exception('No se pudo calcular la unión de las geometrías.');
                }

                $geometry = json_decode($unionGeom->geometry, true);

                if (empty($geometry)) {
                    throw new \Exception('Geometría resultante vacía.');
                }

                // Validar geometría resultante
                $isValid = DB::selectOne(
                    'SELECT ST_IsValid(ST_GeomFromGeoJSON(?)) as is_valid',
                    [json_encode($geometry)]
                );

                if (! ($isValid?->is_valid ?? false)) {
                    $fixed = DB::selectOne(
                        'SELECT ST_AsGeoJSON(ST_MakeValid(ST_GeomFromGeoJSON(?))) as geometry',
                        [json_encode($geometry)]
                    );
                    $geometry = json_decode($fixed->geometry, true);
                }

                // Crear nueva zona resultado
                $newZone = GeoZone::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'is_active' => $request->boolean('is_active', true),
                    'created_by' => $this->getActingUser(request())?->id,
                ]);

                // Crear polígono(s) resultado - puede ser MultiPolygon
                $geometries = $geometry['type'] === 'MultiPolygon' ? $geometry['coordinates'] : [$geometry['coordinates']];
                
                foreach ($geometries as $index => $coords) {
                    GeoPolygon::create([
                        'zone_id' => $newZone->id,
                        'name' => 'Polígono ' . ($index + 1),
                        'geometry' => ['type' => 'Polygon', 'coordinates' => $coords],
                        'sort_order' => $index,
                    ]);
                }

                // Eliminar zonas originales
                $zoneA->delete();
                $zoneB->delete();

                return $newZone->load('polygons');
            });
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'ST_IsValid') || $e->getCode() === '23514') {
                return response()->json(['message' => 'Geometría inválida en la operación de fusión.'], 422);
            }
            throw $e;
        }

        $actingUser = $this->getActingUser($request);
        $this->auditLog->log('geo_zone.merge', 'GeoZone', $result->id, [
            'zone_a_id' => $request->zone_a_id,
            'zone_b_id' => $request->zone_b_id,
            'operation' => 'merge',
        ], $actingUser, $request);

        return response()->json([
            'zone' => [
                'id' => $result->id,
                'name' => $result->name,
                'description' => $result->description,
                'is_active' => $result->is_active,
                'created_at' => $result->created_at?->toISOString(),
                'polygons' => $result->polygons->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'geometry' => $p->geometry,
                    'sort_order' => $p->sort_order,
                ]),
            ],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 50,
                'total' => $result->polygons->count(),
            ],
        ], 201);
    }

    public function subtract(SubtractGeoZonesRequest $request): JsonResponse
    {
        $minuend = GeoZone::with('polygons')->findOrFail($request->minuend_id);
        $subtrahend = GeoZone::with('polygons')->findOrFail($request->subtrahend_id);

        if ($minuend->polygons->isEmpty()) {
            return response()->json(['message' => 'La zona minuenda debe tener al menos un polígono.'], 422);
        }

        try {
            $result = DB::transaction(function () use ($request, $minuend, $subtrahend) {
                // Ejecutar diferencia PostGIS: A - B
                $diffGeom = DB::selectOne(
                    'SELECT ST_AsGeoJSON(ST_MakeValid(ST_Difference(
                        ST_Union(ST_Collect(CASE WHEN zone_id = ? THEN ST_GeomFromGeoJSON(geometry) END)),
                        ST_Union(ST_Collect(CASE WHEN zone_id = ? THEN ST_GeomFromGeoJSON(geometry) END))
                    ))) as geometry
                    FROM geo_polygons WHERE zone_id IN (?, ?)',
                    [$minuend->id, $subtrahend->id, $minuend->id, $subtrahend->id]
                );

                if (! $diffGeom || ! $diffGeom->geometry) {
                    return response()->json(['message' => 'La resta resulta en geometría vacía. La zona sustraendo cubre totalmente a la minuenda.'], 422);
                }

                $geometry = json_decode($diffGeom->geometry, true);

                if (empty($geometry)) {
                    return response()->json(['message' => 'La resta resulta en geometría vacía. La zona sustraendo cubre totalmente a la minuenda.'], 422);
                }

                // Verificar si la geometría está vacía (ST_IsEmpty)
                $isEmpty = DB::selectOne(
                    'SELECT ST_IsEmpty(ST_GeomFromGeoJSON(?)) as is_empty',
                    [json_encode($geometry)]
                );

                if (($isEmpty?->is_empty ?? true)) {
                    return response()->json(['message' => 'La resta resulta en geometría vacía. La zona sustraendo cubre totalmente a la minuenda.'], 422);
                }

                // Validar geometría resultante
                $isValid = DB::selectOne(
                    'SELECT ST_IsValid(ST_GeomFromGeoJSON(?)) as is_valid',
                    [json_encode($geometry)]
                );

                if (! ($isValid?->is_valid ?? false)) {
                    $fixed = DB::selectOne(
                        'SELECT ST_AsGeoJSON(ST_MakeValid(ST_GeomFromGeoJSON(?))) as geometry',
                        [json_encode($geometry)]
                    );
                    $geometry = json_decode($fixed->geometry, true);
                }

                // Crear nueva zona resultado
                $newZone = GeoZone::create([
                    'name' => $request->name,
                    'description' => $request->description,
                    'is_active' => $request->boolean('is_active', true),
                    'created_by' => $this->getActingUser(request())?->id,
                ]);

                // Crear polígono(s) resultado
                $geometries = $geometry['type'] === 'MultiPolygon' ? $geometry['coordinates'] : [$geometry['coordinates']];

                foreach ($geometries as $index => $coords) {
                    GeoPolygon::create([
                        'zone_id' => $newZone->id,
                        'name' => 'Polígono ' . ($index + 1),
                        'geometry' => ['type' => 'Polygon', 'coordinates' => $coords],
                        'sort_order' => $index,
                    ]);
                }

                // Eliminar zonas originales
                $minuend->delete();
                $subtrahend->delete();

                return $newZone->load('polygons');
            });
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'ST_IsValid') || $e->getCode() === '23514') {
                return response()->json(['message' => 'Geometría inválida en la operación de resta.'], 422);
            }
            throw $e;
        }

        $actingUser = $this->getActingUser($request);
        $this->auditLog->log('geo_zone.subtract', 'GeoZone', $result->id, [
            'minuend_id' => $request->minuend_id,
            'subtrahend_id' => $request->subtrahend_id,
            'operation' => 'subtract',
        ], $actingUser, $request);

        return response()->json([
            'zone' => [
                'id' => $result->id,
                'name' => $result->name,
                'description' => $result->description,
                'is_active' => $result->is_active,
                'created_at' => $result->created_at?->toISOString(),
                'polygons' => $result->polygons->map(fn ($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'geometry' => $p->geometry,
                    'sort_order' => $p->sort_order,
                ]),
            ],
            'pagination' => [
                'current_page' => 1,
                'per_page' => 50,
                'total' => $result->polygons->count(),
            ],
        ], 201);
    }
}
