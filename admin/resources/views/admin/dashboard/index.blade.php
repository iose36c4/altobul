<x-admin.layouts.app :pageTitle="'Dashboard'">
    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Usuarios</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $metrics['users']['total'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-primary-100 flex items-center justify-center">
                    <i class="fas fa-users text-primary-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                Activos: <span class="font-medium text-gray-900">{{ $metrics['users']['active'] ?? 0 }}</span> |
                Nuevos 24h: <span class="font-medium text-gray-900">{{ $metrics['users']['new_24h'] ?? 0 }}</span>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Matches Activos</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $metrics['matches']['active'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                    <i class="fas fa-heart text-green-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                Amistades: <span class="font-medium text-gray-900">{{ $metrics['friendships']['active'] ?? 0 }}</span>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Tokes Activos</p>
                    <p class="text-3xl font-bold text-gray-900 mt-1">{{ $metrics['tokes']['active'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                    <i class="fas fa-bolt text-amber-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                Posts: <span class="font-medium text-gray-900">{{ $metrics['posts']['active'] ?? 0 }}</span> |
                Fotos: <span class="font-medium text-gray-900">{{ $metrics['photos']['active'] ?? 0 }}</span>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Verificaciones</p>
                    <p class="text-3xl font-bold {{ ($metrics['verifications']['pending'] ?? 0) > 0 ? 'text-amber-600' : 'text-gray-900' }} mt-1">{{ $metrics['verifications']['pending'] ?? 0 }}</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-100 flex items-center justify-center">
                    <i class="fas fa-user-check text-purple-600 text-2xl"></i>
                </div>
            </div>
            <div class="mt-4 text-sm text-gray-500">
                Aprobadas hoy: <span class="font-medium text-gray-900">{{ $metrics['verifications']['approved_today'] ?? 0 }}</span> |
                Rechazadas: <span class="font-medium text-gray-900">{{ $metrics['verifications']['rejected_today'] ?? 0 }}</span>
            </div>
        </div>
    </div>
    
    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Registros de Usuarios (Últimos 30 días)</h3>
            <div class="h-80">
                <canvas id="usersChart"></canvas>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Actividad Diaria</h3>
            <div class="h-80">
                <canvas id="activityChart"></canvas>
            </div>
        </div>
    </div>
    
    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('admin.users.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-primary-100 flex items-center justify-center">
                <i class="fas fa-users text-primary-600 text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-900">Gestionar Usuarios</p>
                <p class="text-sm text-gray-500">Ver, suspender, activar usuarios</p>
            </div>
        </a>
        
        <a href="{{ route('admin.geo-zones.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center">
                <i class="fas fa-map-marked-alt text-green-600 text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-900">Área de Servicio</p>
                <p class="text-sm text-gray-500">Definir zonas geográficas</p>
            </div>
        </a>
        
        <a href="{{ route('admin.profile-fields.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center">
                <i class="fas fa-list-ol text-purple-600 text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-900">Campos de Perfil</p>
                <p class="text-sm text-gray-500">Configurar metadatos</p>
            </div>
        </a>
        
        <a href="{{ route('admin.verifications.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition flex items-center gap-4">
            <div class="w-12 h-12 rounded-lg bg-amber-100 flex items-center justify-center">
                <i class="fas fa-user-check text-amber-600 text-xl"></i>
            </div>
            <div>
                <p class="font-semibold text-gray-900">Verificaciones</p>
                <p class="text-sm text-gray-500">{{ ($metrics['verifications']['pending'] ?? 0) > 0 ? 'Hay pendientes' : 'Sin pendientes' }}</p>
            </div>
        </a>
    </div>
</x-admin.layouts.app>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Users Chart
    const usersCtx = document.getElementById('usersChart');
    if (usersCtx) {
        new Chart(usersCtx, {
            type: 'line',
            data: {
                labels: {{ json_encode($charts['users']['labels'] ?? []) }},
                datasets: [{
                    label: 'Nuevos usuarios',
                    data: {{ json_encode($charts['users']['data'] ?? []) }},
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
    
    // Activity Chart
    const activityCtx = document.getElementById('activityChart');
    if (activityCtx) {
        new Chart(activityCtx, {
            type: 'bar',
            data: {
                labels: {{ json_encode($charts['activity']['labels'] ?? []) }},
                datasets: [
                    { label: 'Matches', data: {{ json_encode($charts['activity']['matches'] ?? []) }}, backgroundColor: '#22c55e' },
                    { label: 'Tokes', data: {{ json_encode($charts['activity']['tokes'] ?? []) }}, backgroundColor: '#f59e0b' },
                    { label: 'Posts', data: {{ json_encode($charts['activity']['posts'] ?? []) }}, backgroundColor: '#3b82f6' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }
});
</script>
@endpush