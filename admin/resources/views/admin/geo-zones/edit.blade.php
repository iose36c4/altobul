<x-admin.layouts.app :pageTitle="'Editar GeoZona: ' . ($zone['name'] ?? '')">
    <div class="max-w-4xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Editar GeoZona</h1>
            <a href="{{ route('admin.geo-zones.index') }}" class="text-primary-600 hover:text-primary-800">← Volver</a>
        </div>
        
        <form method="POST" action="{{ route('admin.geo-zones.update', $zone['id']) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            @method('PUT')
            @csrf
            
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required value="{{ old('name', $zone['name'] ?? '') }}" class="input-field" placeholder="Ej: Zona Centro">
            </div>
            
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="description" id="description" rows="3" class="input-field" placeholder="Descripción de la zona...">{{ old('description', $zone['description'] ?? '') }}</textarea>
            </div>
            
            <div class="mb-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ old('is_active', $zone['is_active'] ?? false) ? 'checked' : '' }}>
                    <span class="text-sm text-gray-700">Activa</span>
                </label>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Polígonos actuales</label>
                <div id="edit-map" class="h-[400px] w-full rounded-lg border border-gray-300 mb-3"></div>
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Polígonos: <span id="edit-polygon-count">{{ count($zone['polygons'] ?? []) }}</span></span>
                    </div>
                </div>
                <input type="hidden" name="polygons" id="edit-polygons-input" value="{{ json_encode($zone['polygons'] ?? []) }}">
            </div>
            
            <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
                <a href="{{ route('admin.geo-zones.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</x-admin.layouts.app>

@push('scripts')
<script>
let editMap = null;
let editDrawnItems = null;
let editDrawControl = null;

document.addEventListener('DOMContentLoaded', function() {
    initEditMap();
});

function initEditMap() {
    editMap = L.map('edit-map').setView([-34.6037, -58.3816], 10);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(editMap);
    
    editDrawnItems = new L.FeatureGroup();
    editMap.addLayer(editDrawnItems);
    
    editDrawControl = new L.Control.Draw({
        edit: { featureGroup: editDrawnItems },
        draw: {
            polygon: { allowIntersection: false, showArea: true },
            polyline: false, circle: false, rectangle: false, marker: false, circlemarker: false
        }
    });
    editMap.addControl(editDrawControl);
    
    // Load existing polygons
    const existingPolygons = @json($zone['polygons'] ?? []);
    if (existingPolygons.length > 0) {
        existingPolygons.forEach((p, i) => {
            if (p.geometry) {
                const layer = L.geoJSON(p.geometry).addTo(editDrawnItems);
                // Store original data
                layer._polygonData = {
                    name: p.name || `Polígono ${i + 1}`,
                    sort_order: p.sort_order ?? i
                };
            }
        });
        if (editDrawnItems.getLayers().length > 0) {
            editMap.fitBounds(editDrawnItems.getBounds());
        }
    }
    
    editMap.on(L.Draw.Event.CREATED, function(e) {
        const layer = e.layer;
        editDrawnItems.addLayer(layer);
        layer._polygonData = {
            name: `Polígono ${editDrawnItems.getLayers().length}`,
            sort_order: editDrawnItems.getLayers().length - 1
        };
        updateEditPolygonsInput();
    });
    
    editMap.on(L.Draw.Event.EDITED, function(e) {
        updateEditPolygonsInput();
    });
    
    editMap.on(L.Draw.Event.DELETED, function(e) {
        updateEditPolygonsInput();
    });
}

function updateEditPolygonsInput() {
    const polygons = [];
    editDrawnItems.eachLayer(function(layer) {
        const geojson = layer.toGeoJSON();
        polygons.push({
            name: layer._polygonData?.name || `Polígono ${polygons.length + 1}`,
            geometry: geojson.geometry,
            sort_order: layer._polygonData?.sort_order ?? polygons.length
        });
    });
    document.getElementById('edit-polygons-input').value = JSON.stringify(polygons);
    document.getElementById('edit-polygon-count').textContent = polygons.length;
}
</script>
@endpush