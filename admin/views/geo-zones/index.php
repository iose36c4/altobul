<?php $pageTitle = 'GeoZonas - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">GeoZonas</h1>
    <button onclick="document.getElementById('create-modal').classList.remove('hidden')" class="btn-primary">
        + Nueva zona
    </button>
</div>

<?php if (empty($zones)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <p class="text-gray-500 mb-4">No hay geoZonas definidas.</p>
    </div>
<?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($zones as $zone): ?>
            <a href="/geo-zones/<?= $zone['id'] ?>" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
                <h3 class="font-bold text-gray-900 mb-1"><?= htmlspecialchars($zone['name'] ?? '') ?></h3>
                <p class="text-sm text-gray-600 mb-3"><?= htmlspecialchars($zone['description'] ?? 'Sin descripción') ?></p>
                <div class="flex items-center gap-2">
                    <span class="badge badge-blue">
                        <?= count($zone['polygons'] ?? []) ?> polígono(s)
                    </span>
                    <?php if (! empty($zone['is_active'])): ?>
                        <span class="badge badge-green">Activa</span>
                    <?php else: ?>
                        <span class="badge badge-gray">Inactiva</span>
                    <?php endif; ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div id="create-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md mx-4">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Nueva GeoZona</h2>
        <form method="POST" action="/geo-zones" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="name" id="name" required class="input-field" placeholder="Ej: Zona Centro">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="description" id="description" rows="3" class="input-field" placeholder="Descripción de la zona..."></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')"
                        class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition text-sm">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary text-sm">Crear zona</button>
            </div>
        </form>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
