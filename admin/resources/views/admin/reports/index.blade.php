<x-admin.layouts.app :pageTitle="'Reportes y Denuncias'">
    <div class="max-w-full">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Reportes y Denuncias</h1>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="input-field">
                        <option value="">Todos</option>
                        <option value="PENDING" {{ ($filters['status'] ?? '') === 'PENDING' ? 'selected' : '' }}>Pendiente</option>
                        <option value="REVIEWED" {{ ($filters['status'] ?? '') === 'REVIEWED' ? 'selected' : '' }}>Revisado</option>
                        <option value="DISMISSED" {{ ($filters['status'] ?? '') === 'DISMISSED' ? 'selected' : '' }}>Descartado</option>
                        <option value="ACTIONED" {{ ($filters['status'] ?? '') === 'ACTIONED' ? 'selected' : '' }}>Accionado</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Motivo</label>
                    <select name="reason" class="input-field">
                        <option value="">Todos</option>
                        <option value="SPAM" {{ ($filters['reason'] ?? '') === 'SPAM' ? 'selected' : '' }}>Spam</option>
                        <option value="ABUSE" {{ ($filters['reason'] ?? '') === 'ABUSE' ? 'selected' : '' }}>Abuso</option>
                        <option value="HARASSMENT" {{ ($filters['reason'] ?? '') === 'HARASSMENT' ? 'selected' : '' }}>Acoso</option>
                        <option value="INAPPROPRIATE" {{ ($filters['reason'] ?? '') === 'INAPPROPRIATE' ? 'selected' : '' }}>Inapropiado</option>
                        <option value="FAKE" {{ ($filters['reason'] ?? '') === 'FAKE' ? 'selected' : '' }}>Falso</option>
                        <option value="UNDERAGE" {{ ($filters['reason'] ?? '') === 'UNDERAGE' ? 'selected' : '' }}>Menor de edad</option>
                        <option value="OTHER" {{ ($filters['reason'] ?? '') === 'OTHER' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="reportable_type" class="input-field">
                        <option value="">Todos</option>
                        <option value="user" {{ ($filters['reportable_type'] ?? '') === 'user' ? 'selected' : '' }}>Usuario</option>
                        <option value="post" {{ ($filters['reportable_type'] ?? '') === 'post' ? 'selected' : '' }}>Post</option>
                        <option value="photo" {{ ($filters['reportable_type'] ?? '') === 'photo' ? 'selected' : '' }}>Foto</option>
                        <option value="message" {{ ($filters['reportable_type'] ?? '') === 'message' ? 'selected' : '' }}>Mensaje</option>
                        <option value="conversation" {{ ($filters['reportable_type'] ?? '') === 'conversation' ? 'selected' : '' }}>Conversación</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full sm:w-auto">Filtrar</button>
                </div>
            </form>
        </div>

        @if (empty($reports))
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <i class="fas fa-flag text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No hay reportes que coincidan con los filtros.</p>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reportante</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motivo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($reports as $report)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4">
                                    <span class="font-medium text-gray-900">{{ $report['reporter']['email'] ?? 'N/A' }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge badge-blue">{{ $report['reportable_type'] }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge badge-amber">{{ $report['reason'] }}</span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge {{ $report['status'] === 'PENDING' ? 'badge-amber' : ($report['status'] === 'ACTIONED' ? 'badge-green' : 'badge-gray') }}">
                                        {{ $report['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500">
                                    {{ $report['created_at'] ? \Carbon\Carbon::parse($report['created_at'])->format('d/m/Y H:i') : 'N/A' }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <a href="{{ route('admin.reports.show', $report['id']) }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

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
