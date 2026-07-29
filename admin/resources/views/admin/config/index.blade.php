<x-admin.layouts.app :pageTitle="'Configuración'">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Configuración del Sistema</h1>
        </div>
        
        <form method="POST" action="{{ route('admin.config.update') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            @method('PUT')
            
            @foreach ($configs as $key => $value)
                <div class="border-t border-gray-200 pt-6 first:border-0">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">{{ $key }}</label>
                            <p class="text-xs text-gray-500 mt-1 font-mono">{{ gettype($value) === 'array' ? 'array' : (gettype($value) === 'object' ? 'object' : 'string') }}</p>
                        </div>
                    </div>
                    
                    @php
                        $isJson = is_array($value) || is_object($value);
                        $displayValue = $isJson ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string)$value;
                    @endphp
                    
                    <div>
                        <input type="hidden" name="{{ $key }}_type" value="{{ $isJson ? 'json' : 'string' }}">
                        
                        @if ($isJson)
                            <textarea name="{{ $key }}" rows="6" class="input-field font-mono text-sm" placeholder="JSON válido">{{ $displayValue }}</textarea>
                            <p class="mt-1 text-xs text-gray-500">Edita como JSON. Ejemplo: {"key": "value", "number": 123, "bool": true}</p>
                        @else
                            <input type="text" name="{{ $key }}" value="{{ $displayValue }}" class="input-field" placeholder="Valor">
                        @endif
                        
                        @error($key)
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            @endforeach
            
            <div class="pt-4 border-t border-gray-200">
                <div class="flex gap-3 justify-end">
                    <a href="{{ route('admin.config.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i>Guardar configuración
                    </button>
                </div>
            </div>
        </form>
        
        <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <h3 class="font-medium text-amber-800 mb-2">⚠️ Importante</h3>
            <ul class="text-sm text-amber-700 space-y-1">
                <li>• Los cambios surten efecto inmediatamente en la API backend</li>
                <li>• Los valores JSON deben ser sintácticamente válidos</li>
                <li>• Algunas configuraciones requieren reinicio de workers de cola</li>
                <li>• Cada cambio queda registrado en los logs de auditoría</li>
            </ul>
        </div>
    </div>
</x-admin.layouts.app>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validate JSON fields on blur
    document.querySelectorAll('textarea[name]').forEach(textarea => {
        textarea.addEventListener('blur', function() {
            if (this.value.trim()) {
                try {
                    JSON.parse(this.value);
                    this.classList.remove('border-red-500');
                    this.classList.add('border-green-500');
                    setTimeout(() => this.classList.remove('border-green-500'), 2000);
                } catch (e) {
                    this.classList.add('border-red-500');
                    this.classList.remove('border-green-500');
                }
            }
        });
    });
});
</script>
@endpush