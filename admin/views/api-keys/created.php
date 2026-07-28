<?php $pageTitle = 'Clave API creada - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="w-full max-w-2xl mx-auto mt-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Clave API creada</h1>
            <p class="text-gray-600 mt-2">Copiá y guardá esta clave ahora. <strong>No volverá a mostrarse.</strong></p>
        </div>

        <div class="bg-amber-50 border border-amber-300 rounded-lg p-4 mb-6">
            <p class="text-sm font-semibold text-amber-800 mb-2">⚠️ Guardá esta clave en un lugar seguro</p>
            <p class="text-sm text-amber-700">Una vez que cierres esta página, no podrás volver a ver la clave completa. Solo se almacena su hash en la base de datos.</p>
        </div>

        <div class="bg-gray-900 rounded-lg p-6 mb-6">
            <p class="text-xs text-gray-400 mb-2 uppercase font-medium">Tu clave API</p>
            <div class="flex items-center gap-3">
                <code id="api-key-display"
                      class="text-green-400 text-sm font-mono break-all flex-1 select-all"><?= htmlspecialchars($rawKey) ?></code>
                <button onclick="copyKey()"
                        id="copy-btn"
                        class="shrink-0 bg-gray-700 hover:bg-gray-600 text-white text-sm font-medium py-2 px-4 rounded-lg transition">
                    Copiar
                </button>
            </div>
            <p class="text-xs text-gray-500 mt-3">Nombre: <span class="text-gray-300"><?= htmlspecialchars($keyName) ?></span></p>
        </div>

        <div class="text-center">
            <a href="/api-keys" class="btn-primary inline-block">
                Volver a API Keys
            </a>
        </div>
    </div>
</div>

<script>
function copyKey() {
    const key = document.getElementById('api-key-display').textContent;
    navigator.clipboard.writeText(key).then(() => {
        const btn = document.getElementById('copy-btn');
        btn.textContent = 'Copiada ✓';
        btn.classList.add('bg-green-600');
        setTimeout(() => {
            btn.textContent = 'Copiar';
            btn.classList.remove('bg-green-600');
        }, 2000);
    });
}
</script>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
