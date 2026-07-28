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
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Administrador
                </h3>
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-900">{{ $admin['email'] }}</dd>
                    <dt class="text-gray-500">Rol</dt>
                    <dd class="font-medium text-gray-900">{{ $admin['role'] }}</dd>
                </dl>
            </div>

            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <h3 class="font-medium text-yellow-800 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <strong>Guardá estas claves AHORA</strong>
                </h3>
                <p class="text-yellow-700 text-sm mb-4">Las claves API <strong>no se volverán a mostrar</strong>. Cópialas y guárdalas en un lugar seguro.</p>
            </div>

            @foreach ($api_keys as $i => $key)
                <div class="p-4 rounded-lg border
                    {{ $key['type'] === 'CLIENT' ? 'bg-blue-50 border-blue-200' : 'bg-purple-50 border-purple-200' }}">
                    <h3 class="font-medium mb-2 flex items-center
                        {{ $key['type'] === 'CLIENT' ? 'text-blue-900' : 'text-purple-900' }}">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                        </svg>
                        {{ $key['name'] }}
                        <span class="ml-2 px-2 py-0.5 rounded text-xs font-medium
                            {{ $key['type'] === 'CLIENT' ? 'bg-blue-200 text-blue-800' : 'bg-purple-200 text-purple-800' }}">
                            {{ $key['type'] }}
                        </span>
                    </h3>
                    <div class="relative">
                        <input type="text" value="{{ $key['raw_key'] }}" readonly
                               class="w-full px-3 py-2 bg-white border rounded text-sm font-mono focus:outline-none focus:ring-2
                                      {{ $key['type'] === 'CLIENT' ? 'border-blue-300 text-blue-900 focus:ring-blue-500' : 'border-purple-300 text-purple-900 focus:ring-purple-500' }}"
                               id="key_{{ $i }}">
                        <button type="button" onclick="copyToClipboard('key_{{ $i }}')"
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-sm font-medium
                                       {{ $key['type'] === 'CLIENT' ? 'text-blue-600 hover:text-blue-800' : 'text-purple-600 hover:text-purple-800' }}">
                            Copiar
                        </button>
                    </div>
                    <p class="text-xs mt-2
                        {{ $key['type'] === 'CLIENT' ? 'text-blue-700' : 'text-purple-700' }}">
                        Header: <code>X-API-Key: {{ $key['raw_key'] }}</code>
                    </p>
                </div>
            @endforeach

            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-900 mb-3">Próximos pasos</h3>
                <ol class="text-sm text-gray-600 space-y-2 list-decimal list-inside">
                    <li>Configura tu <strong>App Cliente</strong> con la URL del backend + <code>CLIENT API Key</code></li>
                    <li>Configura tu <strong>Panel Admin</strong> con la URL del backend + <code>ADMIN API Key</code></li>
                    <li>Verifica salud: <code>GET /api/install/status</code></li>
                    <li>Inicia Reverb: <code>php artisan reverb:start</code></li>
                    <li>Inicia workers: <code>php artisan queue:work</code></li>
                </ol>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="/" class="text-blue-600 hover:text-blue-700 font-medium">Volver al inicio</a>
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
