<x-admin.layouts.app :pageTitle="'GeoZona: ' . ($zone['name'] ?? '')">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $zone['name'] ?? '' }}</h1>
                <p class="text-gray-600 mt-1">{{ $zone['description'] ?? 'Sin descripción' }}</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('admin.geo-zones.edit', $zone['id']) }}" class="bg-primary-100 text-primary-700 py-2 px-4 rounded-lg font-medium hover:bg-primary-200 transition">Editar</a>
                <a href="{{ route('admin.geo-zones.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition">← Volver</a>
            </div>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 lg:col-span-2">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Mapa</h3>
                    <span class="badge {{ ($zone['is_active'] ?? false) ? 'badge-green' : 'badge-gray' }}">
                        {{ ($zone['is_active'] ?? false) ? 'Activa' : 'Inactiva' }}
                    </span>
                </div>
                <div id="show-map" class="h-[500px] w-full rounded-lg border border-gray-300"></div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Información</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm text-gray-500">ID</dt>
                        <dd class="text-sm font-mono text-gray-900 break-all">{{ $zone['id'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Polígonos</dt>
                        <dd class="text-sm font-medium text-gray-900">{{ count($zone['polygons'] ?? []) }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Creado por</dt>
                        <dd class="text-sm text-gray-900">{{ $zone['created_by'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Creado el</dt>
                        <dd class="text-sm text-gray-900">{{ $zone['created_at'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Actualizado el</dt>
                        <dd class="text-sm text-gray-900">{{ $zone['updated_at'] ?? 'N/A' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
        
        @if (!empty($zone['polygons']))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Polígonos ({{ count($zone['polygons']) }})</h3>
            <div class="space-y-3">
                @foreach ($zone['polygons'] as $index => $polygon)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div>
                            <p class="font-medium text-gray-900">{{ $polygon['name'] ?? "Polígono " . ($index + 1) }}</p>
                            <p class="text-sm text-gray-500">Orden: {{ $polygon['sort_order'] ?? $index }} | Tipo: {{ $polygon['geometry']['type'] ?? 'N/A' }}</p>
                        </div>
                        <div class="text-sm text-gray-500">
                            @php
                                $coords = $polygon['geometry']['coordinates'] ?? [];
                                $points = count($coords[0] ?? []);
                            @endphp
                            {{ $points }} puntos
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-admin.layouts.app>

@push('scripts')
<script>
let showMap = null;

document.addEventListener('DOMContentLoaded', function() {
    initShowMap();
});

function initShowMap() {
    showMap = L.map('show-map').setView([-34.6037, -58.3816], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(showMap);
    
    const existingPolygons = @json($zone['polygons'] ?? []);
    if (existingPolygons.length > 0) {
        const bounds = [];
        existingPolygons.forEach((p) => {
            if (p.geometry) {
                const layer = L.geoJSON(p.geometry, {
                    style: {
                        color: '#3b82f6',
                        weight: 2,
                        opacity: 0.8,
                        fillColor: '#3b82f6',
                        fillOpacity: 0.15
                    }
                }).addTo(showMap);
                
                if (p.name) {
                    layer.bindTooltip(p.name, { permanent: false, direction: 'center' });
                }
                
                if (layer.getBounds) {
                    bounds.push(layer.getBounds());
                }
            }
        });
        
        if (bounds.length > 0) {
            const allBounds = L.latLngBounds(bounds);
            showMap.fitBounds(allBounds, { padding: [20, 20] });
        }
    }
}
</script>
@endpush