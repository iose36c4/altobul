<?php $pageTitle = 'Verificación - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/verifications" class="text-sm text-blue-600 hover:text-blue-700">← Volver a Verificaciones</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Solicitud de verificación</h1>
            <?php
            $status = $request['status'] ?? 'pending';
            $badgeClass = match($status) {
                'pending' => 'badge-yellow',
                'approved' => 'badge-green',
                'rejected' => 'badge-red',
                default => 'badge-gray',
            };
            ?>
            <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($status) ?></span>
        </div>

        <div class="space-y-4 mb-6">
            <div>
                <p class="text-sm text-gray-500">Usuario</p>
                <p class="font-medium">
                    <?= htmlspecialchars($request['user']['name'] ?? $request['user_name'] ?? 'Desconocido') ?>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p class="font-medium">
                    <?= htmlspecialchars($request['user']['email'] ?? $request['user_email'] ?? '') ?>
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Fecha de solicitud</p>
                <p class="font-medium"><?= date('d/m/Y H:i', strtotime($request['created_at'] ?? '')) ?></p>
            </div>
            <?php if (! empty($request['document_url'])): ?>
                <div>
                    <p class="text-sm text-gray-500 mb-1">Documento</p>
                    <a href="<?= htmlspecialchars($request['document_url']) ?>" target="_blank"
                       class="text-blue-600 hover:text-blue-700 underline text-sm">
                        Ver documento adjunto
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($status === 'pending'): ?>
            <div class="flex gap-3 border-t border-gray-200 pt-6">
                <form method="POST" action="/verifications/<?= $request['id'] ?>/approve"
                      onsubmit="return confirm('¿Aprobar esta verificación?')">
                    <button type="submit" class="bg-green-600 text-white py-2 px-6 rounded-lg font-medium hover:bg-green-700 transition text-sm">
                        Aprobar
                    </button>
                </form>
                <form method="POST" action="/verifications/<?= $request['id'] ?>/reject"
                      onsubmit="return confirm('¿Rechazar esta verificación?')">
                    <button type="submit" class="bg-red-100 text-red-700 py-2 px-6 rounded-lg font-medium hover:bg-red-200 transition text-sm">
                        Rechazar
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
