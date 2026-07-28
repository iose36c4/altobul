<?php $pageTitle = 'Usuario - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/users" class="text-sm text-blue-600 hover:text-blue-700">← Volver a Usuarios</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 mb-6">
        <div class="flex items-center gap-4 mb-6">
            <div class="w-16 h-16 bg-gray-200 rounded-full flex items-center justify-center">
                <span class="text-2xl font-bold text-gray-500">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                </span>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($user['name'] ?? '') ?></h1>
                <p class="text-gray-600"><?= htmlspecialchars($user['email'] ?? '') ?></p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <p class="text-sm text-gray-500">Rol</p>
                <p class="font-medium"><?= htmlspecialchars($user['role'] ?? 'user') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Estado</p>
                <p class="font-medium"><?= htmlspecialchars($user['status'] ?? 'active') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Verificación</p>
                <p class="font-medium"><?= htmlspecialchars($user['verification_status'] ?? 'unverified') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Registro</p>
                <p class="font-medium"><?= date('d/m/Y H:i', strtotime($user['created_at'] ?? '')) ?></p>
            </div>
        </div>

        <div class="flex gap-3 border-t border-gray-200 pt-6">
            <?php if (($user['status'] ?? '') === 'active'): ?>
                <form method="POST" action="/users/<?= $user['id'] ?>/suspend"
                      onsubmit="return confirm('¿Suspender este usuario?')">
                    <button type="submit" class="bg-red-100 text-red-700 py-2 px-4 rounded-lg font-medium hover:bg-red-200 transition text-sm">
                        Suspender
                    </button>
                </form>
            <?php else: ?>
                <form method="POST" action="/users/<?= $user['id'] ?>/activate">
                    <button type="submit" class="bg-green-100 text-green-700 py-2 px-4 rounded-lg font-medium hover:bg-green-200 transition text-sm">
                        Activar
                    </button>
                </form>
            <?php endif; ?>

            <form method="POST" action="/users/<?= $user['id'] ?>/role" class="flex gap-2">
                <select name="role" class="input-field text-sm py-2">
                    <option value="user" <?= ($user['role'] ?? '') === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
                <button type="submit" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition text-sm">
                    Cambiar rol
                </button>
            </form>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
