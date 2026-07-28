<?php $pageTitle = 'Verificaciones - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Solicitudes de Verificación</h1>
</div>

<?php if (empty($requests)): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
        <p class="text-gray-500">No hay solicitudes de verificación pendientes.</p>
    </div>
<?php else: ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="table-header">Usuario</th>
                    <th class="table-header">Estado</th>
                    <th class="table-header">Fecha</th>
                    <th class="table-header text-right">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($requests as $req): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="table-cell font-medium">
                            <?= htmlspecialchars($req['user']['name'] ?? $req['user_name'] ?? 'Desconocido') ?>
                        </td>
                        <td class="table-cell">
                            <?php
                            $status = $req['status'] ?? 'pending';
                            $badgeClass = match($status) {
                                'pending' => 'badge-yellow',
                                'approved' => 'badge-green',
                                'rejected' => 'badge-red',
                                default => 'badge-gray',
                            };
                            ?>
                            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
                        </td>
                        <td class="table-cell text-sm text-gray-500">
                            <?= date('d/m/Y H:i', strtotime($req['created_at'] ?? '')) ?>
                        </td>
                        <td class="table-cell text-right">
                            <a href="/verifications/<?= $req['id'] ?>" class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                                Revisar
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
