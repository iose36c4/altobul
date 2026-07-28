<?php $pageTitle = 'API Keys - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-gray-900">API Keys</h1>
    <a href="/api-keys/create" class="btn-primary">+ Nueva clave</a>
</div>

<?php if (empty($keys)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <p class="text-gray-500 mb-4">No hay claves API creadas.</p>
        <a href="/api-keys/create" class="btn-primary">Crear primera clave</a>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="table-header">Nombre</th>
                    <th class="table-header">Tipo</th>
                    <th class="table-header">Prefijo</th>
                    <th class="table-header">Estado</th>
                    <th class="table-header">Creada</th>
                    <th class="table-header">Expira</th>
                    <th class="table-header text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($keys as $key): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="table-cell font-medium"><?= htmlspecialchars($key['name'] ?? '') ?></td>
                        <td class="table-cell">
                            <span class="badge <?= ($key['type'] ?? '') === 'ADMIN' ? 'badge-purple' : 'badge-blue' ?>">
                                <?= htmlspecialchars($key['type'] ?? '') ?>
                            </span>
                        </td>
                        <td class="table-cell font-mono text-sm"><?= htmlspecialchars($key['key_prefix'] ?? '') ?>****</td>
                        <td class="table-cell">
                            <?php if (! empty($key['revoked_at'])): ?>
                                <span class="badge badge-red">Revocada</span>
                            <?php elseif (! empty($key['expires_at']) && strtotime($key['expires_at']) < time()): ?>
                                <span class="badge badge-yellow">Expirada</span>
                            <?php else: ?>
                                <span class="badge badge-green">Activa</span>
                            <?php endif; ?>
                        </td>
                        <td class="table-cell text-sm text-gray-500">
                            <?= date('d/m/Y', strtotime($key['created_at'] ?? '')) ?>
                        </td>
                        <td class="table-cell text-sm text-gray-500">
                            <?= $key['expires_at'] ? date('d/m/Y', strtotime($key['expires_at'])) : 'Sin expirar' ?>
                        </td>
                        <td class="table-cell text-right">
                            <?php if (empty($key['revoked_at'])): ?>
                                <form method="POST" action="/api-keys/<?= $key['id'] ?>/revoke"
                                      onsubmit="return confirm('¿Revocar esta clave?')" class="inline">
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-800 font-medium">
                                        Revocar
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-sm text-gray-400">Revocada</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
