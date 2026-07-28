<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalación - Altobul Admin</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-lg">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Altobul Admin</h1>
            <p class="text-gray-600 mt-2">Configuración inicial</p>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-bold text-sm">1</span>
                </div>
                <h2 class="text-xl font-bold text-gray-900">Conectar al backend</h2>
            </div>

            <div id="error-box" class="hidden mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"></div>

            <form id="install-form" method="POST" action="/install/save" class="space-y-5">
                <div>
                    <label for="backend_url" class="block text-sm font-medium text-gray-700 mb-1">URL del Backend</label>
                    <input type="url" name="backend_url" id="backend_url" required
                           value="<?= htmlspecialchars($_POST['backend_url'] ?? 'http://localhost:8000') ?>"
                           class="input-field"
                           placeholder="http://localhost:8000">
                    <p class="text-xs text-gray-500 mt-1">URL base del backend Laravel (sin trailing slash)</p>
                </div>

                <div>
                    <label for="api_key" class="block text-sm font-medium text-gray-700 mb-1">Clave API (Admin)</label>
                    <input type="text" name="api_key" id="api_key" required
                           value="<?= htmlspecialchars($_POST['api_key'] ?? '') ?>"
                           class="input-field font-mono"
                           placeholder="ab_adm_xxxxxxxxxxxxxxxx">
                    <p class="text-xs text-gray-500 mt-1">Clave API con permisos de administrador</p>
                </div>

                <div class="flex gap-3">
                    <button type="button" id="test-btn"
                            class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition">
                        Probar conexión
                    </button>
                    <button type="submit" id="save-btn"
                            class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition">
                        Guardar y continuar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('test-btn').addEventListener('click', async () => {
            const btn = document.getElementById('test-btn');
            const errorBox = document.getElementById('error-box');
            btn.disabled = true;
            btn.textContent = 'Probando...';
            errorBox.classList.add('hidden');

            const formData = new FormData();
            formData.append('backend_url', document.getElementById('backend_url').value);
            formData.append('api_key', document.getElementById('api_key').value);

            try {
                const res = await fetch('/install/test', { method: 'POST', body: formData });
                const data = await res.json();
                if (data.success) {
                    btn.textContent = '✓ Conectado';
                    btn.classList.remove('bg-gray-100', 'text-gray-700');
                    btn.classList.add('bg-green-100', 'text-green-700');
                } else {
                    btn.textContent = 'Error';
                    errorBox.textContent = data.message;
                    errorBox.classList.remove('hidden');
                    btn.classList.remove('bg-gray-100', 'text-gray-700');
                    btn.classList.add('bg-red-100', 'text-red-700');
                }
            } catch (e) {
                errorBox.textContent = 'Error de red';
                errorBox.classList.remove('hidden');
            }

            setTimeout(() => {
                btn.disabled = false;
                btn.textContent = 'Probar conexión';
                btn.className = 'flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition';
            }, 3000);
        });
    </script>
</body>
</html>
