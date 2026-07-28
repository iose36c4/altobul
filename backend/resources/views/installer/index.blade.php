@extends('installer.layout')

@section('title', 'Instalación - Altobul')

@section('content')
    @if ($installed)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900 mb-2">Backend ya instalado</h2>
            <p class="text-gray-600 mb-6">La instalación se completó el {{ $installedAt ?? 'fecha desconocida' }}</p>
            
            <div class="space-y-3 mb-6 p-4 bg-gray-50 rounded-lg text-left">
                <h3 class="font-medium text-gray-900 mb-3">Primer administrador:</h3>
                <p class="text-gray-600">{{ $adminEmail ?? 'No disponible' }}</p>
            </div>

            <a href="/api/install/status" class="inline-block text-blue-600 hover:text-blue-700 font-medium">
                Ver estado detallado (JSON)
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Instalar Altobul Backend</h2>
            <p class="text-gray-600 text-center mb-8">Crea el primer administrador y genera las claves API</p>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Errores de validación</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc list-inside space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="/install" id="installForm" class="space-y-6">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email del administrador</label>
                    <input type="email" name="email" id="email" required
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="admin@altobul.com">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" name="password" id="password" required minlength="8"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Mínimo 8 caracteres">
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Repite la contraseña">
                </div>

                <div>
                    <label for="app_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre de la aplicación (opcional)</label>
                    <input type="text" name="app_name" id="app_name"
                           value="{{ old('app_name', 'Altobul') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Altobul">
                </div>

                <button type="submit" id="installBtn" 
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition disabled:opacity-50 disabled:cursor-not-allowed">
                    <span id="btnText">Instalar Backend</span>
                    <span id="btnLoading" class="hidden flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Instalando...
                    </span>
                </button>
            </form>

            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Qué hace este instalador:</h4>
                <ul class="text-sm text-gray-600 space-y-1 list-disc list-inside">
                    <li>Crea el primer usuario administrador</li>
                    <li>Genera <strong>CLIENT API Key</strong> (para la app cliente)</li>
                    <li>Genera <strong>ADMIN API Key</strong> (para el panel admin)</li>
                    <li>Marca el backend como instalado</li>
                </ul>
                <p class="mt-3 text-xs text-gray-500">
                    <strong>Importante:</strong> Guarda las API Keys de forma segura. No se volverán a mostrar.
                </p>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @if (!$installed)
    <script>
        document.getElementById('installForm').addEventListener('submit', function(e) {
            const btn = document.getElementById('installBtn');
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            
            btn.disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
        });
    </script>
    @endif
@endsection