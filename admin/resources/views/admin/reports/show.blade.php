<x-admin.layouts.app :pageTitle="'Reporte #' . substr($report['id'] ?? '', 0, 8)">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Detalle del Reporte</h1>
            <a href="{{ route('admin.reports.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200">← Volver</a>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <dt class="text-sm text-gray-500">ID</dt>
                    <dd class="font-mono text-sm text-gray-900 break-all">{{ $report['id'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Estado</dt>
                    <dd>
                        <span class="badge {{ $report['status'] === 'PENDING' ? 'badge-amber' : ($report['status'] === 'ACTIONED' ? 'badge-green' : 'badge-gray') }}">
                            {{ $report['status'] }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Reportante</dt>
                    <dd class="text-sm text-gray-900">{{ $report['reporter']['email'] ?? 'N/A' }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Tipo de contenido</dt>
                    <dd class="text-sm text-gray-900">{{ $report['reportable_type'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">ID del contenido</dt>
                    <dd class="font-mono text-sm text-gray-900 break-all">{{ $report['reportable_id'] }}</dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Motivo</dt>
                    <dd>
                        <span class="badge badge-amber">{{ $report['reason'] }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm text-gray-500">Fecha</dt>
                    <dd class="text-sm text-gray-900">{{ $report['created_at'] ? \Carbon\Carbon::parse($report['created_at'])->format('d/m/Y H:i') : 'N/A' }}</dd>
                </div>
                @if ($report['reviewed_by'])
                    <div>
                        <dt class="text-sm text-gray-500">Revisado por</dt>
                        <dd class="text-sm text-gray-900">{{ $report['reviewed_by']['email'] ?? 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500">Revisado el</dt>
                        <dd class="text-sm text-gray-900">{{ $report['reviewed_at'] ? \Carbon\Carbon::parse($report['reviewed_at'])->format('d/m/Y H:i') : 'N/A' }}</dd>
                    </div>
                @endif
            </dl>

            @if ($report['description'])
                <div class="mt-4">
                    <dt class="text-sm text-gray-500 mb-1">Descripción</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3">{{ $report['description'] }}</dd>
                </div>
            @endif

            @if ($report['admin_notes'])
                <div class="mt-4">
                    <dt class="text-sm text-gray-500 mb-1">Notas del admin</dt>
                    <dd class="text-sm text-gray-900 bg-gray-50 rounded-lg p-3">{{ $report['admin_notes'] }}</dd>
                </div>
            @endif
        </div>

        @if ($report['status'] === 'PENDING')
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Acciones</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <form action="{{ route('admin.reports.dismiss', $report['id']) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas (opcional)</label>
                            <textarea name="admin_notes" rows="2" class="input-field w-full" placeholder="Motivo del descarte..."></textarea>
                        </div>
                        <button type="submit" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 w-full" onclick="return confirm('¿Descartar este reporte?')">
                            Descartar reporte
                        </button>
                    </form>
                    <form action="{{ route('admin.reports.action', $report['id']) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Notas (opcional)</label>
                            <textarea name="admin_notes" rows="2" class="input-field w-full" placeholder="Acción tomada..."></textarea>
                        </div>
                        <button type="submit" class="bg-red-100 text-red-700 py-2 px-4 rounded-lg font-medium hover:bg-red-200 w-full" onclick="return confirm('¿Marcar este reporte como accionado?')">
                            Accionar (medida tomada)
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-admin.layouts.app>
