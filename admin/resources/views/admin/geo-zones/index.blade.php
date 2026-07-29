<x-admin.layouts.app>
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">GeoZonas</h1>
        <button onclick="document.getElementById('create-modal').classList.remove('hidden')" class="btn-primary">
            <i class="fas fa-plus mr-2"></i>Nueva zona
        </button>
    </div>
    
    @if (empty($zones))
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <i class="fas fa-map-marked-alt text-6xl text-gray-300 mb-4"></i>
            <p class="text-gray-500 mb-4">No hay geoZonas definidas.</p>
            <button onclick="document.getElementById('create-modal').classList.remove('hidden')" class="btn-primary">
                Crear primera zona
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($zones as $zone)
                <a href="{{ route('admin.geo-zones.show', $zone['id'] ?? $zone['zone']['id'] ?? '') }}" 
                   class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                    <h3 class="font-bold text-gray-900 mb-1">{{ $zone['name'] ?? $zone['zone']['name'] ?? '' }}</h3>
                    <p class="text-sm text-gray-600 mb-3">{{ $zone['description'] ?? $zone['zone']['description'] ?? 'Sin descripción' }}</p>
                    <div class="flex items-center gap-2">
                        <span class="badge badge-blue">
                            {{ count($zone['polygons'] ?? $zone['zone']['polygons'] ?? []) }} polígono(s)
                        </span>
                        @php
                            $isActive = $zone['is_active'] ?? $zone['zone']['is_active'] ?? false;
                        @endphp
                        @if ($isActive)
                            <span class="badge badge-green">Activa</span>
                        @else
                            <span class="badge badge-gray">Inactiva</span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
    @endif
    
    {{-- Create Modal --}}
    <div id="create-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Nueva GeoZona</h2>
            <form method="POST" action="{{ route('admin.geo-zones.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" required class="input-field" placeholder="Ej: Zona Centro">
                </div>
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                    <textarea name="description" id="description" rows="3" class="input-field" placeholder="Descripción de la zona..."></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Polígonos (dibuja en el mapa)</label>
                    <div id="create-map" class="h-[400px] w-full rounded-lg border border-gray-300 mb-2"></div>
                    <p class="text-xs text-gray-500 mb-2">Dibuja al menos un polígono. Usa las herramientas del mapa.</p>
                    <input type="hidden" name="polygons" id="polygons-input" value="[]">
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition text-sm">Cancelar</button>
                    <button type="submit" class="btn-primary text-sm" disabled id="submit-btn">Crear zona</button>
                </div>
            </form>
        </div>
    </div>
</x-admin.layouts.app>

@push('scripts')
<script>
// Initialize map for create modal
let createMap = null;
let createDrawnItems = null;
let createDrawControl = null;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize map when modal opens
    const modal = document.getElementById('create-modal');
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.target.classList.contains('hidden') === false && createMap === null) {
                initCreateMap();
            }
        });
    });
    observer.observe(modal, { attributes: true, attributeFilter: ['class'] });
});

function initCreateMap() {
    if (createMap !== null) return;
    
    createMap = L.map('create-map').setView([-34.6037, -58.3816], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(createMap);
    
    createDrawnItems = new L.FeatureGroup();
    createMap.addLayer(createDrawnItems);
    
    createDrawControl = new L.Control.Draw({
        edit: { featureGroup: createDrawnItems },
        draw: {
            polygon: { allowIntersection: false, showArea: true },
            polyline: false, circle: false, rectangle: false, marker: false, circlemarker: false
        }
    });
    createMap.addControl(createDrawControl);
    
    createMap.on(L.Draw.Event.CREATED, function(e) {
        const layer = e.layer;
        createDrawnItems.addLayer(layer);
        updatePolygonsInput();
        document.getElementById('submit-btn').disabled = false;
    });
    
    createMap.on(L.Draw.Event.EDITED, function(e) {
        updatePolygonsInput();
    });
    
    createMap.on(L.Draw.Event.DELETED, function(e) {
        updatePolygonsInput();
        if (createDrawnItems.getLayers().length === 0) {
            document.getElementById('submit-btn').disabled = true;
        }
    });
    
    // Re-invalidate size after modal shows
    setTimeout(() => createMap.invalidateSize(), 100);
}

function updatePolygonsInput() {
    const polygons = [];
    createDrawnItems.eachLayer(function(layer) {
        const geojson = layer.toGeoJSON();
        polygons.push({
            name: `Polígono ${polygons.length + 1}`,
            geometry: geojson.geometry,
            sort_order: polygons.length
        });
    });
    document.getElementById('polygons-input').value = JSON.stringify(polygons);
}
</script>
@endpush