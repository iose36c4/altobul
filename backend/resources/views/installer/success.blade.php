@extends('installer.layout')

@section('title', 'Instalación Exitosa - Altobul')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        
        <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">¡Instalación completada!</h2>
        <p class="text-gray-600 text-center mb-8">Tu backend Altobul está listo para usar</p>

        <div class="space-y-6">
            <!-- Admin Info -->
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Administrador creado
                </h3>
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-900">{{ $admin['email'] }}</dd>
                    <dt class="text-gray-500">Rol</dt>
                    <dd class="font-medium text-gray-900">{{ $admin['role'] }}</dd>
                </dl>
            </div>

            <!-- API Keys Warning -->
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h3 class="font-medium text-yellow-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <strong>Guarda estas claves AHORA</strong>
                </h3>
                <p class="text-yellow-700 text-sm mb-4">Las claves API <strong>no se volverán a mostrar</strong>. Cópialas y guárdalas en un lugar seguro (gestor de contraseñas, archivo encriptado, etc.).</p>
            </div>

            <!-- Client API Key -->
            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="font-medium text-blue-900 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    CLIENT API Key (para la App Cliente)
                </h3>
                <div class="relative">
                    <input type="text" value="{{ $api_keys['client']['raw_key'] }}" readonly
                           class="w-full px-3 py-2 bg-white border border-blue-300 rounded text-sm font-mono text-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           id="clientKey">
                    <button type="button" onclick="copyToClipboard('clientKey')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Copiar
                    </button>
                </div>
                <p class="text-xs text-blue-700 mt-2">Header: <code>X-API-Key: {{ $api_keys['client']['raw_key'] }}</code></p>
            </div>

            <!-- Admin API Key -->
            <div class="p-4 bg-purple-50 border border-purple-200 rounded-lg">
                <h3 class="font-medium text-purple-900 mb-2 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    ADMIN API Key (para el Panel Admin)
                </h3>
                <div class="relative">
                    <input type="text" value="{{ $api_keys['admin']['raw_key'] }}" readonly
                           class="w-full px-3 py-2 bg-white border border-purple-300 rounded text-sm font-mono text-purple-900 focus:outline-none focus:ring-2 focus:ring-purple-500"
                           id="adminKey">
                    <button type="button" onclick="copyToClipboard('adminKey')"
                            class="absolute right-2 top-1/2 -translate-y-1/2 text-purple-600 hover:text-purple-800 text-sm font-medium">
                        Copiar
                    </button>
                </div>
                <p class="text-xs text-purple-700 mt-2">Header: <code>X-API-Key: {{ $api_keys['admin']['raw_key'] }}</code></p>
            </div>

            <!-- Next Steps -->
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-900 mb-3">Próximos pasos</h3>
                <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                    <li>Configura tu <strong>App Cliente</strong> con la URL del backend + <code>CLIENT API Key</code></li>
                    <li>Configura tu <strong>Panel Admin</strong> con la URL del backend + <code>ADMIN API Key</code></li>
                    <li>Verifica salud: <code>GET /api/system/health</code></li>
                    <li>Inicia Reverb: <code>php artisan reverb:start</code></li>
                    <li>Inicia workers: <code>php artisan queue:work</code></li>
                </ol>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="/" class="text-blue-600 hover:text-blue-700 font-medium">
                Volver al inicio
            </a>
        </div>
    </div>
@endsection

@section('scripts')
<script>
    function copyToClipboard(elementId) {
        const input = document.getElementById(elementId);
        input.select();
        input.setSelectionRange(0, 99999);
        navigator.clipboard.writeText(input.value).then(() => {
            const btn = input.nextElementSibling;
            const originalText = btn.textContent;
            btn.textContent = '¡Copiado!';
            btn.classList.add('text-green-600');
            setTimeout(() => {
                btn.textContent = originalText;
                btn.classList.remove('text-green-600');
            }, 2000);
        });
    }
</script>
@endsection