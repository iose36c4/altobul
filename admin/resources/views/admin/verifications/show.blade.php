<x-admin.layouts.app :pageTitle="'Verificación: ' . ($verification['user']['email'] ?? '')">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detalle de Verificación</h1>
                <p class="text-gray-600 mt-1">{{ $verification['user']['email'] ?? 'N/A' }}</p>
            </div>
            <a href="{{ route('admin.verifications.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200">← Volver</a>
        </div>
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Usuario</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900">{{ $verification['user']['email'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Estado actual</dt>
                            <dd class="text-sm">
                                <span class="badge {{ 
                                    ($verification['user']['verification_status'] ?? '') === 'verified' ? 'badge-green' : 
                                    (($verification['user']['verification_status'] ?? '') === 'pending' ? 'badge-amber' : 'badge-gray') }}">
                                    {{ ucfirst($verification['user']['verification_status'] ?? 'unverified') }}
                                </span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Perfil</dt>
                            <dd class="text-sm text-gray-900">{{ $verification['user']['profile']['title'] ?? 'Sin título' }}</dd>
                        </div>
                    </dl>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Solicitud</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">Método</dt>
                            <dd class="text-sm text-gray-900">{{ $verification['verification_method'] }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Referencia externa</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $verification['external_reference'] ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Enviado</dt>
                            <dd class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($verification['submitted_at'])->format('d/m/Y H:i') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Estado</dt>
                            <dd class="text-sm">
                                <span class="badge {{ 
                                    $verification['status'] === 'PENDING' ? 'badge-amber' : 
                                    ($verification['status'] === 'APPROVED' ? 'badge-green' : 'badge-red') }}">
                                    {{ $verification['status'] }}
                                </span>
                            </dd>
                        </div>
                        @if ($verification['reviewed_at'])
                            <div>
                                <dt class="text-sm text-gray-500">Revisado</dt>
                                <dd class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($verification['reviewed_at'])->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                        @if ($verification['reviewed_by'])
                            <div>
                                <dt class="text-sm text-gray-500">Revisado por</dt>
                                <dd class="text-sm text-gray-900">{{ $verification['reviewed_by']['email'] ?? 'N/A' }}</dd>
                            </div>
                        @endif
                        @if ($verification['rejection_reason'])
                            <div>
                                <dt class="text-sm text-gray-500">Motivo rechazo</dt>
                                <dd class="text-sm text-red-600">{{ $verification['rejection_reason'] }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>
            
            @if ($verification['status'] === 'PENDING')
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <form action="{{ route('admin.verifications.approve', $verification['id']) }}" method="POST" onsubmit="return confirm('¿Aprobar esta verificación? El usuario quedará verificado.')">
                        @csrf
                        <button type="submit" class="btn-primary flex-1">
                            <i class="fas fa-check mr-2"></i>Aprobar
                        </button>
                    </form>
                    
                    <button type="button" onclick="showRejectModal()" class="bg-red-100 text-red-700 py-2 px-4 rounded-lg font-medium hover:bg-red-200 transition flex-1" x-data="{ open: false }" x-ref="modal">
                        <i class="fas fa-times mr-2"></i>Rechazar
                    </button>
                </div>
                
                <!-- Reject Modal -->
                <div x-show="open" x-transition class="fixed inset-0 bg-black/50 flex items-center justify-center z-50" @click.outside="open = false">
                    <div class="bg-white rounded-xl shadow-lg p-6 w-full max-w-md mx-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rechazar verificación</h3>
                        <p class="text-gray-600 mb-4">Escribe el motivo del rechazo (máx. 500 caracteres). El usuario será notificado.</p>
                        <form action="{{ route('admin.verifications.reject', $verification['id']) }}" method="POST">
                            @csrf
                            <textarea name="rejection_reason" required maxlength="500" rows="4" class="input-field mb-4" placeholder="Motivo del rechazo..."></textarea>
                            <div class="flex gap-3 justify-end">
                                <button type="button" @click="open = false" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200">Cancelar</button>
                                <button type="submit" class="bg-red-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-red-700">Rechazar</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-admin.layouts.app>