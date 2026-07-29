<x-admin.layouts.app :pageTitle="'Campos de Perfil'">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Campos de Perfil</h1>
            <a href="{{ route('admin.profile-fields.create') }}" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>Nuevo campo
            </a>
        </div>
        
        @if (empty($fields))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <i class="fas fa-list-ol text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 mb-4">No hay campos de perfil definidos.</p>
                <a href="{{ route('admin.profile-fields.create') }}" class="btn-primary">Crear primer campo</a>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full" x-data="sortableTable()">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="w-12 px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Orden</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Slug</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Etiqueta</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Visibilidad</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Req. Verif.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Activo</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody x-ref="tbody" class="divide-y divide-gray-200 sortable">
                        @foreach ($fields as $field)
                            <tr data-id="{{ $field['id'] }}" class="hover:bg-gray-50">
                                <td class="px-4 py-3 drag-handle text-gray-400 cursor-move">⋮⋮</td>
                                <td class="px-4 py-3 font-mono text-sm text-gray-900">{{ $field['slug'] }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $field['label'] }}</td>
                                <td class="px-4 py-3">
                                    <span class="badge badge-blue">{{ $field['type'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="badge badge-gray">{{ $field['default_visibility'] }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if ($field['default_requires_verified'])
                                        <span class="badge badge-amber">Sí</span>
                                    @else
                                        <span class="badge badge-gray">No</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <button @click="toggleActive('{{ $field['id'] }}', {{ !($field['is_active'] ?? false) ? 'true' : 'false' }})" 
                                            class="inline-flex items-center justify-center w-10 h-6 rounded-full {{ ($field['is_active'] ?? false) ? 'bg-green-500' : 'bg-gray-300' }} transition"
                                            aria-label="{{ ($field['is_active'] ?? false) ? 'Desactivar' : 'Activar' }}">
                                        <span class="sr-only">{{ ($field['is_active'] ?? false) ? 'Activo' : 'Inactivo' }}</span>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.profile-fields.edit', $field['id']) }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium">Editar</a>
                                        <form action="{{ route('admin.profile-fields.destroy', $field['id']) }}" method="POST" class="inline" onsubmit="return confirm('¿Eliminar este campo?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <div class="mt-4 flex justify-end">
                <button onclick="saveOrder()" class="btn-primary" id="save-order-btn" disabled>
                    <i class="fas fa-save mr-2"></i>Guardar orden
                </button>
            </div>
        @endif
    </div>
</x-admin.layouts.app>

@push('scripts')
<script>
function sortableTable() {
    return {
        init() {
            Sortable.create(this.$refs.tbody, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: () => this.onOrderChange()
            });
        },
        
        onOrderChange() {
            document.getElementById('save-order-btn').disabled = false;
        },
        
        async toggleActive(id, active) {
            try {
                const response = await fetch(`{{ route('admin.profile-fields.update', ':id') }}`.replace(':id', id), {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ is_active: active })
                });
                if (response.ok) {
                    location.reload();
                }
            } catch (e) {
                console.error(e);
            }
        }
    }
}

async function saveOrder() {
    const ids = Array.from(document.querySelectorAll('[x-ref="tbody"] tr')).map(tr => tr.dataset.id);
    try {
        const response = await fetch('{{ route("admin.profile-fields.reorder") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ ids })
        });
        if (response.ok) {
            document.getElementById('save-order-btn').disabled = true;
            alert('Orden guardado correctamente');
        }
    } catch (e) {
        console.error(e);
    }
}
</script>
@endpush