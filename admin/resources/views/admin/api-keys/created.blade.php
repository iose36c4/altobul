<x-admin.layouts.app :pageTitle="'API Key Creada'">
    <div class="max-w-2xl mx-auto text-center">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 space-y-6">
            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto">
                <i class="fas fa-check text-green-600 text-3xl"></i>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-900">API Key creada correctamente</h1>
            <p class="text-gray-600">Guarda esta clave en un lugar seguro. No se volverá a mostrar.</p>
            
            <div class="bg-gray-50 rounded-lg p-4 text-left border border-gray-200">
                <label class="block text-sm font-medium text-gray-700 mb-1">Clave API (Raw)</label>
                <div class="flex items-center gap-2">
                    <code class="flex-1 font-mono text-sm bg-white px-3 py-2 rounded border border-gray-300 break-all" id="api-key">{{ $rawKey }}</code>
                    <button onclick="copyToClipboard()" class="bg-primary-600 text-white px-3 py-2 rounded hover:bg-primary-700 transition text-sm" aria-label="Copiar al portapapeles">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
                <p class="mt-2 text-xs text-gray-500">Copia esta clave ahora. No podrás verla de nuevo.</p>
            </div>
            
            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 text-left">
                <h4 class="font-medium text-amber-800 mb-2">⚠️ Importante</h4>
                <ul class="text-sm text-amber-700 space-y-1 list-disc list-inside">
                    <li>Esta es la única vez que verás la clave completa.</li>
                    <li>Guárdala en un gestor de contraseñas o lugar seguro.</li>
                    <li>Si la pierdes, tendrás que crear una nueva y revocar esta.</li>
                    <li>Trátala como una contraseña: no la compartas ni la commitees a repositorios.</li>
                </ul>
            </div>
            
            <div class="flex gap-3 justify-center">
                <a href="{{ route('admin.api-keys.index') }}" class="btn-primary">Ir a lista de API Keys</a>
                <a href="{{ route('admin.api-keys.create') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition">Crear otra</a>
            </div>
        </div>
    </div>
</x-admin.layouts.app>

@push('scripts')
<script>
function copyToClipboard() {
    const key = document.getElementById('api-key').textContent;
    navigator.clipboard.writeText(key).then(() => {
        const btn = event.target.closest('button');
        const original = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i>';
        btn.classList.remove('bg-primary-600', 'hover:bg-primary-700');
        btn.classList.add('bg-green-600', 'hover:bg-green-700');
        setTimeout(() => {
            btn.innerHTML = original;
            btn.classList.remove('bg-green-600', 'hover:bg-green-700');
            btn.classList.add('bg-primary-600', 'hover:bg-primary-700');
        }, 2000);
    });
}
</script>
@endpush