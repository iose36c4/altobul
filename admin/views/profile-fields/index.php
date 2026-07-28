<?php $pageTitle = 'Campos de Perfil - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Campos de Perfil</h1>
    <button onclick="document.getElementById('create-modal').classList.remove('hidden')" class="btn-primary">
        + Nuevo campo
    </button>
</div>

<?php if (empty($fields)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <p class="text-gray-500 mb-4">No hay campos de perfil definidos.</p>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="table-header">Nombre</th>
                    <th class="table-header">Slug</th>
                    <th class="table-header">Tipo</th>
                    <th class="table-header">Privacidad</th>
                    <th class="table-header">Obligatorio</th>
                    <th class="table-header text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($fields as $field): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="table-cell font-medium"><?= htmlspecialchars($field['name'] ?? '') ?></td>
                        <td class="table-cell font-mono text-sm"><?= htmlspecialchars($field['slug'] ?? '') ?></td>
                        <td class="table-cell">
                            <span class="badge badge-blue"><?= htmlspecialchars($field['type'] ?? 'text') ?></span>
                        </td>
                        <td class="table-cell">
                            <span class="badge badge-gray"><?= htmlspecialchars($field['privacy_default'] ?? 'PUBLIC') ?></span>
                        </td>
                        <td class="table-cell">
                            <?= ! empty($field['is_required']) ? '✓' : '—' ?>
                        </td>
                        <td class="table-cell text-right">
                            <a href="/profile-fields/<?= $field['id'] ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium mr-3">
                                Ver
                            </a>
                            <form method="POST" action="/profile-fields/<?= $field['id'] ?>/delete" class="inline"
                                  onsubmit="return confirm('¿Eliminar este campo?')">
                                <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                    Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<div id="create-modal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
    <div class="bg-white rounded-xl shadow-lg p-8 w-full max-w-md mx-4">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Nuevo campo de perfil</h2>
        <form method="POST" action="/profile-fields" class="space-y-4">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="name" id="name" required class="input-field" placeholder="Ej: Biografía">
            </div>
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                <input type="text" name="slug" id="slug" required class="input-field font-mono" placeholder="Ej: bio">
            </div>
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="type" id="type" class="input-field">
                    <option value="text">Texto</option>
                    <option value="textarea">Texto largo</option>
                    <option value="select">Selección</option>
                    <option value="number">Número</option>
                    <option value="date">Fecha</option>
                </select>
            </div>
            <div>
                <label for="privacy_default" class="block text-sm font-medium text-gray-700 mb-1">Privacidad por defecto</label>
                <select name="privacy_default" id="privacy_default" class="input-field">
                    <option value="PUBLIC">Público</option>
                    <option value="MATCH">Match</option>
                    <option value="FRIENDS">Amigos</option>
                    <option value="PRIVATE">Privado</option>
                </select>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')"
                        class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition text-sm">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary text-sm">Crear campo</button>
            </div>
        </form>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
