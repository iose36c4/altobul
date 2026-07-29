<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
            $zone = DB::transaction(function () use ($data, $polygons) {
                $data['created_by'] = request()->user()->id;
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

        $this->auditLog->log('geo_zone.create', 'GeoZone', $zone->id, [
            'zone_id' => $zone->id,
            'polygons_count' => $zone->polygons->count(),
        ], request()->user(), request());

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

        $this->auditLog->log('geo_zone.update', 'GeoZone', $zone->id, [
            'changes' => $data,
        ], request()->user(), request());

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

        $this->auditLog->log('geo_zone.delete', 'GeoZone', $zoneId, [], request()->user(), request());

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
        ], request()->user(), request());

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
        ], request()->user(), request());

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
        ], request()->user(), request());

        return response()->json(['message' => 'Polygon deleted']);
    }
}
