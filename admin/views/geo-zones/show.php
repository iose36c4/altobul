<?php $pageTitle = 'GeoZona - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/geo-zones" class="text-sm text-blue-600 hover:text-blue-700">← Volver a GeoZonas</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($zone['name'] ?? '') ?></h1>
            <div class="flex gap-2">
                <button onclick="document.getElementById('edit-modal').classList.remove('hidden')"
                        class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition text-sm">
                    Editar
                </button>
                <form method="POST" action="/geo-zones/<?= $zone['id'] ?>/delete"
                      onsubmit="return confirm('¿Eliminar esta zona?')">
                    <button type="submit" class="bg-red-100 text-red-700 py-2 px-4 rounded-lg font-medium hover:bg-red-200 transition text-sm">
                        Eliminar
                    </button>
                </form>
            </div>
        </div>

        <p class="text-gray-600 mb-6"><?= htmlspecialchars($zone['description'] ?? 'Sin descripción') ?></p>

        <div class="border-t border-gray-200 pt-6">
            <h3 class="font-bold text-gray-900 mb-3">Polígonos</h3>
            <?php $polygons = $zone['polygons'] ?? []; ?>
            <?php if (empty($polygons)): ?>
                <p class="text-sm text-gray-500">No hay polígonos definidos para esta zona.</p>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($polygons as $polygon): ?>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($polygon['name'] ?? 'Polígono') ?></p>
                            <p class="text-xs text-gray-500 mt-1 font-mono">
                                <?= htmlspecialchars(substr($polygon['geometry'] ?? '', 0, 100)) ?>...
                            </p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="edit-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md mx-4">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Editar GeoZona</h2>
        <form method="POST" action="/geo-zones/<?= $zone['id'] ?>" class="space-y-4">
            <input type="hidden" name="_method" value="PUT">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="name" id="name" required class="input-field"
                       value="<?= htmlspecialchars($zone['name'] ?? '') ?>">
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="description" id="description" rows="3" class="input-field"><?= htmlspecialchars($zone['description'] ?? '') ?></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')"
                        class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition text-sm">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary text-sm">Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
