<x-admin.layouts.app :pageTitle="'Usuarios'">
    <div class="max-w-full">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Usuarios</h1>
            <div class="flex gap-3">
                <a href="{{ route('admin.users.export') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition flex items-center gap-2">
                    <i class="fas fa-download"></i> Exportar CSV
                </a>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Email o ID..." class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="role" class="input-field">
                        <option value="">Todos</option>
                        <option value="user" {{ ($filters['role'] ?? '') === 'user' ? 'selected' : '' }}>Usuario</option>
                        <option value="admin" {{ ($filters['role'] ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="input-field">
                        <option value="">Todos</option>
                        <option value="active" {{ ($filters['status'] ?? '') === 'active' ? 'selected' : '' }}>Activo</option>
                        <option value="suspended" {{ ($filters['status'] ?? '') === 'suspended' ? 'selected' : '' }}>Suspendido</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Verificación</label>
                    <select name="verification_status" class="input-field">
                        <option value="">Todos</option>
                        <option value="unverified" {{ ($filters['verification_status'] ?? '') === 'unverified' ? 'selected' : '' }}>No verificado</option>
                        <option value="pending" {{ ($filters['verification_status'] ?? '') === 'pending' ? 'selected' : '' }}>Pendiente</option>
                        <option value="verified" {{ ($filters['verification_status'] ?? '') === 'verified' ? 'selected' : '' }}>Verificado</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full sm:w-auto">Filtrar</button>
                </div>
            </form>
        </div>
        
        @if (empty($users))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No hay usuarios que coincidan con los filtros.</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verificación</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último acceso</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4">
                                    <a href="{{ route('admin.users.show', $user['id']) }}" class="font-medium text-gray-900 hover:text-primary-600">
                                        {{ $user['email'] }}
                                    </a>
                                    <p class="text-xs text-gray-500 font-mono">{{ $user['id'] }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge {{ ($user['role'] ?? '') === 'admin' ? 'badge-purple' : 'badge-blue' }}">
                                        {{ ucfirst($user['role'] ?? '') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge {{ ($user['status'] ?? '') === 'active' ? 'badge-green' : 'badge-red' }}">
                                        {{ ucfirst($user['status'] ?? '') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge {{ 
                                        ($user['verification_status'] ?? '') === 'verified' ? 'badge-green' : 
                                        (($user['verification_status'] ?? '') === 'pending' ? 'badge-amber' : 'badge-gray') }}">
                                        {{ ucfirst($user['verification_status'] ?? '') }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500">
                                    {{ $user['last_seen_at'] ? \Carbon\Carbon::parse($user['last_seen_at'])->diffForHumans() : 'Nunca' }}
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($user['created_at'])->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.users.show', $user['id']) }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium">Ver</a>
                                        @if (auth()->id() !== $user['id'])
                                            @if (($user['status'] ?? '') === 'active')
                                                <form action="{{ route('admin.users.suspend', $user['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Suspender a este usuario?')">
                                                    @csrf
                                                    <button type="submit" class="text-amber-600 hover:text-amber-800 text-sm font-medium">Suspender</button>
                                                </form>
                                            @else
                                                <form action="{{ route('admin.users.activate', $user['id']) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">Activar</button>
                                                </form>
                                            @endif
                                            
                                            <form action="{{ route('admin.users.change-role', $user['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Cambiar rol a {{ ($user['role'] ?? '') === 'admin' ? 'usuario' : 'admin' }}?')">
                                                @csrf
                                                <input type="hidden" name="role" value="{{ ($user['role'] ?? '') === 'admin' ? 'user' : 'admin' }}">
                                                <button type="submit" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                                    {{ ($user['role'] ?? '') === 'admin' ? 'Quitar admin' : 'Hacer admin' }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if (!empty($pagination['last_page']) && $pagination['last_page'] > 1)
                <div class="mt-4 flex justify-center">
                    <nav class="flex items-center gap-2">
                        @for ($i = 1; $i <= $pagination['last_page']; $i++)
                            <a href="?page={{ $i }}{{ !empty($filters) ? '&' . http_build_query($filters) : '' }}" 
                               class="px-3 py-2 rounded-lg text-sm font-medium {{ $i == ($pagination['current_page'] ?? 1) ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                                {{ $i }}
                            </a>
                        @endfor
                    </nav>
                </div>
            @endif
        @endif
    </div>
</x-admin.layouts.app>