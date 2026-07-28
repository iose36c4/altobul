<?php $pageTitle = 'Usuarios - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Usuarios</h1>
</div>

<?php if (empty($users)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <p class="text-gray-500">No hay usuarios registrados.</p>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="table-header">Nombre</th>
                    <th class="table-header">Email</th>
                    <th class="table-header">Rol</th>
                    <th class="table-header">Estado</th>
                    <th class="table-header">Registro</th>
                    <th class="table-header text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($users as $user): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="table-cell font-medium"><?= htmlspecialchars($user['name'] ?? '') ?></td>
                        <td class="table-cell text-sm text-gray-600"><?= htmlspecialchars($user['email'] ?? '') ?></td>
                        <td class="table-cell">
                            <span class="badge <?= ($user['role'] ?? '') === 'admin' ? 'badge-purple' : 'badge-blue' ?>">
                                <?= htmlspecialchars($user['role'] ?? 'user') ?>
                            </span>
                        </td>
                        <td class="table-cell">
                            <?php
                            $status = $user['status'] ?? 'active';
                            $badgeClass = match($status) {
                                'active' => 'badge-green',
                                'suspended' => 'badge-red',
                                'pending' => 'badge-yellow',
                                default => 'badge-gray',
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                        </td>
                        <td class="table-cell text-sm text-gray-500">
                            <?= date('d/m/Y', strtotime($user['created_at'] ?? '')) ?>
                        </td>
                        <td class="table-cell text-right">
                            <a href="/users/<?= $user['id'] ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Ver
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
