<x-admin.layouts.app :pageTitle="'Editar Usuario: ' . ($user['email'] ?? '')">
    <div class="max-w-2xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Editar Usuario</h1>
                <p class="text-sm text-gray-500">{{ $user['email'] ?? '' }}</p>
            </div>
            <a href="{{ route('admin.users.show', $user['id']) }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200">← Volver</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('admin.users.update', $user['id']) }}">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email', $user['email'] ?? '') }}" class="input-field w-full">
                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Contraseña <span class="text-gray-400 font-normal">(dejar vacío para mantener)</span></label>
                    <input type="password" name="password" class="input-field w-full">
                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="role" class="input-field w-full">
                        <option value="user" {{ ($user['role'] ?? '') === 'user' ? 'selected' : '' }}>Usuario</option>
                        <option value="admin" {{ ($user['role'] ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="input-field w-full">
                        <option value="active" {{ ($user['status'] ?? '') === 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="suspended" {{ ($user['status'] ?? '') === 'suspended' ? 'selected' : '' }}>Suspendido</option>
                        <option value="banned" {{ ($user['status'] ?? '') === 'banned' ? 'selected' : '' }}>Baneado</option>
                    </select>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="{{ route('admin.users.show', $user['id']) }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200">Cancelar</a>
                    <button type="submit" class="btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</x-admin.layouts.app>
