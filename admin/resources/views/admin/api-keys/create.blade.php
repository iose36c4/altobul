<x-admin.layouts.app :pageTitle="'Crear API Key'">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Crear API Key</h1>
            <a href="{{ route('admin.api-keys.index') }}" class="text-primary-600 hover:text-primary-800">← Volver</a>
        </div>
        
        <form method="POST" action="{{ route('admin.api-keys.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            @csrf
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="name" id="name" required value="{{ old('name') }}" class="input-field" placeholder="Ej: App Móvil, Integración Stripe, Panel Admin">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                <select name="type" id="type" required class="input-field">
                    <option value="">Seleccionar tipo</option>
                    <option value="CLIENT" {{ old('type') === 'CLIENT' ? 'selected' : '' }}>Cliente (App cliente - /api/client/*)</option>
                    <option value="ADMIN" {{ old('type') === 'ADMIN' ? 'selected' : '' }}>Admin (Panel admin - /api/admin/*)</option>
                    <option value="MOBILE" {{ old('type') === 'MOBILE' ? 'selected' : '' }}>Móvil (App nativa)</option>
                    <option value="INTEGRATION" {{ old('type') === 'INTEGRATION' ? 'selected' : '' }}>Integración (Webhooks, 3rd party)</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">El tipo determina qué endpoints de la API puede acceder esta clave.</p>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="expires_in_days" class="block text-sm font-medium text-gray-700 mb-1">Expiración (días)</label>
                <input type="number" name="expires_in_days" id="expires_in_days" min="1" max="3650" value="{{ old('expires_in_days') }}" class="input-field" placeholder="Dejar vacío para que nunca expire (máx. 10 años)">
                <p class="mt-1 text-xs text-gray-500">Opcional. Máximo 3650 días (10 años).</p>
            </div>
            
            <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
                <a href="{{ route('admin.api-keys.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition">Cancelar</a>
                <button type="submit" class="btn-primary">Crear API Key</button>
            </div>
        </form>
    </div>
</x-admin.layouts.app>