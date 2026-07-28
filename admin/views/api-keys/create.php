<?php $pageTitle = 'Crear API Key - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="max-w-lg">
    <div class="mb-6">
        <a href="/api-keys" class="text-sm text-blue-600 hover:text-blue-700">← Volver a API Keys</a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Nueva clave API</h1>

        <form method="POST" action="/api-keys" class="space-y-5">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                <input type="text" name="name" id="name" required
                       class="input-field"
                       placeholder="Ej: App iOS, App Android, Web">
            </div>

            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="type" id="type" class="input-field">
                    <option value="CLIENT">CLIENT — App de usuario</option>
                    <option value="ADMIN">ADMIN — App de administración</option>
                </select>
            </div>

            <div>
                <label for="expires_in_days" class="block text-sm font-medium text-gray-700 mb-1">Expira en (días, opcional)</label>
                <input type="number" name="expires_in_days" id="expires_in_days"
                       min="1" max="3650"
                       class="input-field"
                       placeholder="Sin expiración">
                <p class="text-xs text-gray-500 mt-1">Dejar vacío para que no expire</p>
            </div>

            <button type="submit" class="w-full btn-primary">
                Crear clave
            </button>
        </form>
    </div>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
