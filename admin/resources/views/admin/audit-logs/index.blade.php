<x-admin.layouts.app :pageTitle="'Auditoría'">
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Logs de Auditoría</h1>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Acción</label>
                    <input type="text" name="action" value="{{ $filters['action'] ?? '' }}" placeholder="user.suspend, geo_zone.create..." class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo objetivo</label>
                    <input type="text" name="target_type" value="{{ $filters['target_type'] ?? '' }}" placeholder="User, GeoZone, VerificationRequest..." class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Admin</label>
                    <input type="text" name="admin_id" value="{{ $filters['admin_id'] ?? '' }}" placeholder="ID del admin" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="input-field">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary w-full sm:w-auto">Filtrar</button>
                    <a href="{{ route('admin.audit-logs.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 w-full sm:w-auto text-center">Limpiar</a>
                </div>
            </form>
        </div>
        
        @if (empty($logs))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <i class="fas fa-history text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No hay logs de auditoría.</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Admin</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Acción</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Objetivo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP / UA</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Detalles</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm text-gray-500 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($log['created_at'])->format('d/m/Y H:i:s') }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($log['admin'])
                                        <a href="#" class="font-medium text-gray-900 hover:text-primary-600">{{ $log['admin']['email'] }}</a>
                                        <p class="text-xs text-gray-500 font-mono">{{ $log['admin']['id'] }}</p>
                                    @else
                                        <span class="text-gray-400">Sistema</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $log['action'] }}</td>
                                <td class="px-4 py-3 text-sm text-gray-500">
                                    @if ($log['target_type'])
                                        {{ $log['target_type'] }} <span class="font-mono">({{ $log['target_id'] }})</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate block">
                                    {{ $log['ip_address'] }}<br>
                                    {{ Str::limit($log['user_agent'] ?? '', 30) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" 
                                            onclick="showMetadata({{ json_encode($log['metadata'] ?? []) }})" 
                                            class="text-primary-600 hover:text-primary-800 text-sm font-medium">
                                        Ver JSON
                                    </button>
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

@push('scripts')
<script>
function showMetadata(metadata) {
    alert('Metadata:\n' + JSON.stringify(metadata, null, 2));
}
</script>
@endpush