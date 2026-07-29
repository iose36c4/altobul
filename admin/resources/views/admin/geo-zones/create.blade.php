<x-admin.layouts.app>
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nueva GeoZona</h1>
            <p class="text-gray-600 mt-1">Define una zona geográfica dibujando polígonos en el mapa</p>
        </div>
        <a href="{{ route('admin.geo-zones.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition">
            <i class="fas fa-arrow-left mr-2"></i>Volver
        </a>
    </div>
    
    <form method="POST" action="{{ route('admin.geo-zones.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 max-w-3xl">
        @csrf
        
        <div class="mb-6">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
            <input type="text" name="name" id="name" required class="input-field" placeholder="Ej: Zona Centro" value="{{ old('name') }}">
            @error('name')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
        
        <div class="mb-6">
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
            <textarea name="description" id="description" rows="3" class="input-field" placeholder="Descripción de la zona...">{{ old('description') }}</textarea>
        </div>
        
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Polígonos <span class="text-red-500">*</span></label>
            <p class="text-xs text-gray-500 mb-2">Dibuja al menos un polígono en el mapa. Puedes agregar múltiples polígonos.</p>
            <div id="create-map" class="h-[500px] w-full rounded-lg border border-gray-300 mb-3"></div>
            <div class="bg-gray-50 rounded-lg p-3">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Polígonos dibujados: <span id="polygon-count">0</span></span>
                    <button type="button" onclick="clearAllPolygons()" class="text-sm text-red-600 hover:text-red-800">Limpiar todo</button>
                </div>
            </div>
            <input type="hidden" name="polygons" id="polygons-input" value="[]">
        </div>
        
        <div class="mb-6">
            <label class="flex items-center gap-2">
                <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ old('is_active', true) ? 'checked' : '' }}>
                <span class="text-sm text-gray-700">Activa (los usuarios deben estar en esta zona para descubrir)</span>
            </label>
        </div>
        
        <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
            <a href="{{ route('admin.geo-zones.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition">Cancelar</a>
            <button type="submit" class="btn-primary" disabled id="submit-btn">Crear zona</button>
        </div>
    </form>
</x-admin.layouts.app>

@push('scripts')
<script>
// Map initialization for create page
let createMap = null;
let createDrawnItems = null;
let createDrawControl = null;

document.addEventListener('DOMContentLoaded', function() {
    initCreateMap();
});

function initCreateMap() {
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
    document.getElementById('polygon-count').textContent = polygons.length;
}

function clearAllPolygons() {
    createDrawnItems.clearLayers();
    updatePolygonsInput();
    document.getElementById('submit-btn').disabled = true;
}
</script>
@endpush