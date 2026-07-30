@php
$scripts = <<<'SCRIPTS'
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
            polygon: { 
                allowIntersection: false, 
                showArea: true,
                shapeOptions: {
                    color: '#3b82f6',
                    fillColor: '#3b82f6',
                    fillOpacity: 0.2,
                    weight: 2
                }
            },
            polyline: false, 
            circle: false, 
            rectangle: false, 
            marker: false, 
            circlemarker: false
        }
    });
    createMap.addControl(createDrawControl);
    
    createMap.on(L.Draw.Event.CREATED, function(e) {
        const layer = e.layer;
        // Validate minimum 3 points (4 coordinates since closed)
        const coords = layer.getLatLngs()[0];
        if (coords.length < 4) {
            alert('Un polígono debe tener al menos 3 puntos. Por favor, dibuja nuevamente.');
            createDrawnItems.removeLayer(layer);
            return;
        }
        createDrawnItems.addLayer(layer);
        updatePolygonsInput();
        document.getElementById('submit-btn').disabled = false;
        document.getElementById('polygon-validation').classList.add('hidden');
    });
    
    createMap.on(L.Draw.Event.EDITED, function(e) {
        // Validate edited polygons have at least 3 points
        let valid = true;
        e.layers.eachLayer(function(layer) {
            const coords = layer.getLatLngs()[0];
            if (coords.length < 4) {
                valid = false;
            }
        });
        if (!valid) {
            alert('Un polígono debe tener al menos 3 puntos.');
        }
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
        const coords = geojson.geometry.coordinates[0];
        // Validate minimum 3 points (4 coordinates = closed ring)
        if (coords.length >= 4) {
            polygons.push({
                name: `Polígono ${polygons.length + 1}`,
                geometry: geojson.geometry,
                sort_order: polygons.length
            });
        }
    });
    document.getElementById('polygons-input').value = JSON.stringify(polygons);
    document.getElementById('polygon-count').textContent = polygons.length;
    
    // Show/hide validation message
    const validationEl = document.getElementById('polygon-validation');
    const submitBtn = document.getElementById('submit-btn');
    if (polygons.length === 0) {
        validationEl.classList.remove('hidden');
        submitBtn.disabled = true;
    } else {
        validationEl.classList.add('hidden');
        submitBtn.disabled = false;
    }
}

function clearAllPolygons() {
    if (confirm('¿Estás seguro de que quieres eliminar todos los polígonos?')) {
        createDrawnItems.clearLayers();
        updatePolygonsInput();
        document.getElementById('submit-btn').disabled = true;
    }
}
</script>
SCRIPTS;
@endphp

<x-admin.layouts.app :scripts="$scripts">
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
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mb-3">
                <p class="text-sm text-blue-800 mb-1"><i class="fas fa-info-circle mr-1"></i> Instrucciones:</p>
                <ul class="text-sm text-blue-700 list-disc list-inside space-y-1">
                    <li>Haz clic en <strong>"Dibujar un polígono"</strong> en la barra de herramientas del mapa</li>
                    <li>Haz clic en el mapa para colocar <strong>mínimo 3 puntos</strong></li>
                    <li>El polígono se <strong>cierra automáticamente</strong> al hacer clic en el primer punto</li>
                    <li>Puedes <strong>mover</strong> y <strong>eliminar</strong> puntos con la herramienta de edición</li>
                    <li>Puedes agregar <strong>múltiples polígonos</strong></li>
                </ul>
            </div>
            <div id="create-map" class="h-[500px] w-full rounded-lg border border-gray-300 mb-3"></div>
            <div class="bg-gray-50 rounded-lg p-3">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <span class="text-sm text-gray-600">Polígonos dibujados: <span id="polygon-count" class="font-medium">0</span></span>
                    <button type="button" onclick="clearAllPolygons()" class="text-sm text-red-600 hover:text-red-800">Limpiar todo</button>
                </div>
                <div id="polygon-validation" class="mt-2 text-sm hidden">
                    <span class="text-red-600"><i class="fas fa-exclamation-triangle mr-1"></i> Se requiere al menos 1 polígono con 3+ puntos</span>
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