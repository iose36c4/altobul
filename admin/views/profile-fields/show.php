<?php $pageTitle = 'Campo de Perfil - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="max-w-2xl">
    <div class="mb-6">
        <a href="/profile-fields" class="text-sm text-blue-600 hover:text-blue-700">← Volver a Campos de Perfil</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900"><?= htmlspecialchars($field['name'] ?? '') ?></h1>
            <form method="POST" action="/profile-fields/<?= $field['id'] ?>/delete"
                  onsubmit="return confirm('¿Eliminar este campo? Los datos de usuario asociados se perderán.')">
                <button type="submit" class="bg-red-100 text-red-700 py-2 px-4 rounded-lg font-medium hover:bg-red-200 transition text-sm">
                    Eliminar
                </button>
            </form>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Slug</p>
                <p class="font-medium font-mono"><?= htmlspecialchars($field['slug'] ?? '') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tipo</p>
                <p class="font-medium"><?= htmlspecialchars($field['type'] ?? 'text') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Privacidad por defecto</p>
                <p class="font-medium"><?= htmlspecialchars($field['privacy_default'] ?? 'PUBLIC') ?></p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Obligatorio</p>
                <p class="font-medium"><?= ! empty($field['is_required']) ? 'Sí' : 'No' ?></p>
            </div>
        </div>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
