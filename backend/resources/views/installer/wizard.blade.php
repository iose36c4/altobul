@extends('installer.layout')

@section('title', 'Instalación - Altobul')

@section('content')
    @if ($installed ?? false)
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
        @php
            $currentStep = $step ?? 1;
            $steps = [
                1 => 'DB',
                2 => 'Administrador',
                3 => 'Medios',
                4 => 'Confirmar',
            ];
        @endphp

        {{-- Step Indicator --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                @foreach($steps as $num => $label)
                    <div class="flex flex-col items-center flex-1">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-bold
                            {{ $num < $currentStep ? 'bg-green-500 text-white' : ($num === $currentStep ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-500') }}">
                            @if ($num < $currentStep)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            @else
                                {{ $num }}
                            @endif
                        </div>
                        <span class="text-xs mt-2 whitespace-nowrap {{ $num <= $currentStep ? 'text-gray-900 font-medium' : 'text-gray-400' }}">
                            {{ $label }}
                        </span>
                    </div>
                    @if ($num < 4)
                        <div class="flex-1 h-0.5 mx-2 {{ $num < $currentStep ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                    @endif
                @endforeach
            </div>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">Errores</h3>
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

        {{-- STEP 1: Database --}}
        @if ($currentStep === 1)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Conexión a la Base de Datos</h2>
                <p class="text-gray-600 text-center mb-8">Configurá la conexión a PostgreSQL</p>

                <form method="POST" action="{{ route('install.save-db') }}" id="dbForm" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="db_host" class="block text-sm font-medium text-gray-700 mb-1">Host</label>
                            <input type="text" name="db_host" id="db_host" required
                                   value="{{ old('db_host', $dbConfig['db_host']) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                   placeholder="127.0.0.1">
                        </div>
                        <div>
                            <label for="db_port" class="block text-sm font-medium text-gray-700 mb-1">Puerto</label>
                            <input type="number" name="db_port" id="db_port" required
                                   value="{{ old('db_port', $dbConfig['db_port']) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                   placeholder="5432">
                        </div>
                    </div>

                    <div>
                        <label for="db_database" class="block text-sm font-medium text-gray-700 mb-1">Nombre de la base de datos</label>
                        <input type="text" name="db_database" id="db_database" required
                               value="{{ old('db_database', $dbConfig['db_database']) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="altobul">
                    </div>

                    <div>
                        <label for="db_username" class="block text-sm font-medium text-gray-700 mb-1">Usuario</label>
                        <input type="text" name="db_username" id="db_username" required
                               value="{{ old('db_username', $dbConfig['db_username']) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="postgres">
                    </div>

                    <div>
                        <label for="db_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input type="password" name="db_password" id="db_password" required
                               value="{{ old('db_password', $dbConfig['db_password']) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="••••••••">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" id="testDbBtn"
                                class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition">
                            Probar conexión
                        </button>
                        <button type="submit" id="saveDbBtn"
                                class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition disabled:opacity-50">
                            Siguiente
                        </button>
                    </div>

                    <div id="dbTestResult" class="hidden mt-4 p-4 rounded-lg text-sm"></div>
                </form>
            </div>
        @endif

        {{-- STEP 2: Admin User --}}
        @if ($currentStep === 2)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Primer Administrador</h2>
                <p class="text-gray-600 text-center mb-8">Creá el usuario administrador principal de la app</p>

                <form method="POST" action="{{ route('install.save-admin') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="admin_name" class="block text-sm font-medium text-gray-700 mb-1">Nombre (opcional)</label>
                        <input type="text" name="admin_name" id="admin_name"
                               value="{{ old('admin_name') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="Nombre del admin">
                    </div>

                    <div>
                        <label for="admin_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" name="admin_email" id="admin_email" required
                               value="{{ old('admin_email') }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="admin@altobul.com">
                    </div>

                    <div>
                        <label for="admin_password" class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                        <input type="password" name="admin_password" id="admin_password" required minlength="8"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="Mínimo 8 caracteres">
                    </div>

                    <div>
                        <label for="admin_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                        <input type="password" name="admin_password_confirmation" id="admin_password_confirmation" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="Repite la contraseña">
                    </div>

                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('install.show', ['step' => 1]) }}"
                           class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition text-center">
                            Atrás
                        </a>
                        <button type="submit"
                                class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition">
                            Siguiente
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- STEP 3: Media Server --}}
        @if ($currentStep === 3)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Servidor de Medios</h2>
                <p class="text-gray-600 text-center mb-8">Configurá el almacenamiento de archivos (MinIO o S3 compatible)</p>

                <form method="POST" action="{{ route('install.save-media') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="media_url" class="block text-sm font-medium text-gray-700 mb-1">URL del servidor</label>
                        <input type="url" name="media_url" id="media_url" required
                               value="{{ old('media_url', session('install_media.media_url', 'http://altobul-media:9000')) }}"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                               placeholder="http://altobul-media:9000">
                        <p class="text-xs text-gray-400 mt-1">Ej: http://altobul-media:9000 (Docker) o http://localhost:9000 (local)</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="media_access_key" class="block text-sm font-medium text-gray-700 mb-1">Access Key</label>
                            <input type="text" name="media_access_key" id="media_access_key" required
                                   value="{{ old('media_access_key', session('install_media.media_access_key', 'altobul')) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                   placeholder="altobul">
                        </div>
                        <div>
                            <label for="media_secret_key" class="block text-sm font-medium text-gray-700 mb-1">Secret Key</label>
                            <input type="text" name="media_secret_key" id="media_secret_key" required
                                   value="{{ old('media_secret_key', session('install_media.media_secret_key', 'altobul_secret')) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                   placeholder="••••••••">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="media_bucket" class="block text-sm font-medium text-gray-700 mb-1">Bucket</label>
                            <input type="text" name="media_bucket" id="media_bucket"
                                   value="{{ old('media_bucket', session('install_media.media_bucket', 'altobul')) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                   placeholder="altobul">
                        </div>
                        <div>
                            <label for="media_region" class="block text-sm font-medium text-gray-700 mb-1">Región</label>
                            <input type="text" name="media_region" id="media_region"
                                   value="{{ old('media_region', session('install_media.media_region', 'us-east-1')) }}"
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                                   placeholder="us-east-1">
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <a href="{{ route('install.show', ['step' => 2]) }}"
                           class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition text-center">
                            Atrás
                        </a>
                        <button type="submit"
                                class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition">
                            Siguiente
                        </button>
                    </div>
                </form>
            </div>
        @endif

        {{-- STEP 4: Confirm & Install --}}
        @if ($currentStep === 4)
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Confirmar instalación</h2>
                <p class="text-gray-600 text-center mb-8">Revisá todo antes de instalar</p>

                <div class="space-y-4 mb-8">
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-2">Base de datos</h3>
                        <dl class="grid grid-cols-2 gap-1 text-sm">
                            <dt class="text-gray-500">Host</dt>
                            <dd class="font-mono text-gray-900">{{ env('DB_HOST') }}:{{ env('DB_PORT') }}</dd>
                            <dt class="text-gray-500">Database</dt>
                            <dd class="font-mono text-gray-900">{{ env('DB_DATABASE') }}</dd>
                            <dt class="text-gray-500">Usuario</dt>
                            <dd class="font-mono text-gray-900">{{ env('DB_USERNAME') }}</dd>
                        </dl>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-2">Administrador</h3>
                        <dl class="grid grid-cols-2 gap-1 text-sm">
                            <dt class="text-gray-500">Email</dt>
                            <dd class="text-gray-900">{{ session('install_admin.email', '—') }}</dd>
                        </dl>
                    </div>

                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-2">Servidor de medios</h3>
                        <dl class="grid grid-cols-2 gap-1 text-sm">
                            <dt class="text-gray-500">URL</dt>
                            <dd class="font-mono text-gray-900">{{ session('install_media.media_url', env('MEDIA_URL', '—')) }}</dd>
                            <dt class="text-gray-500">Bucket</dt>
                            <dd class="font-mono text-gray-900">{{ session('install_media.media_bucket', env('MEDIA_BUCKET', 'altobul')) }}</dd>
                            <dt class="text-gray-500">Access Key</dt>
                            <dd class="font-mono text-gray-900">{{ session('install_media.media_access_key', env('MEDIA_ACCESS_KEY', '—')) }}</dd>
                            <dt class="text-gray-500">Región</dt>
                            <dd class="font-mono text-gray-900">{{ session('install_media.media_region', env('MEDIA_REGION', 'us-east-1')) }}</dd>
                        </dl>
                    </div>
                </div>

                <form method="POST" action="{{ route('install.execute') }}" id="installForm">
                    @csrf

                    <div class="flex gap-3">
                        <a href="{{ route('install.show', ['step' => 3]) }}"
                           class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition text-center">
                            Atrás
                        </a>
                        <button type="submit" id="installBtn"
                                class="flex-1 bg-green-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition disabled:opacity-50">
                            <span id="btnText">Instalar Backend</span>
                            <span id="btnLoading" class="hidden items-center justify-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Instalando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        @endif
    @endif
@endsection

@section('scripts')
    @if (!($installed ?? false))
    <script>
        document.getElementById('testDbBtn')?.addEventListener('click', async function() {
            const btn = this;
            const result = document.getElementById('dbTestResult');
            btn.disabled = true;
            btn.textContent = 'Probando...';

            const formData = new FormData();
            formData.append('db_host', document.getElementById('db_host').value);
            formData.append('db_port', document.getElementById('db_port').value);
            formData.append('db_database', document.getElementById('db_database').value);
            formData.append('db_username', document.getElementById('db_username').value);
            formData.append('db_password', document.getElementById('db_password').value);
            formData.append('_token', '{{ csrf_token() }}');

            try {
                const response = await fetch('{{ route("install.test-db") }}', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await response.json();
                result.classList.remove('hidden');
                if (data.success) {
                    result.className = 'mt-4 p-4 rounded-lg text-sm bg-green-50 border border-green-200 text-green-800';
                    result.textContent = '✓ ' + data.message;
                } else {
                    result.className = 'mt-4 p-4 rounded-lg text-sm bg-red-50 border border-red-200 text-red-800';
                    result.textContent = '✗ ' + data.message;
                }
            } catch (e) {
                result.classList.remove('hidden');
                result.className = 'mt-4 p-4 rounded-lg text-sm bg-red-50 border border-red-200 text-red-800';
                result.textContent = '✗ Error de red: ' + e.message;
            }
            btn.disabled = false;
            btn.textContent = 'Probar conexión';
        });

        document.getElementById('installForm')?.addEventListener('submit', function(e) {
            const btnText = document.getElementById('btnText');
            const btnLoading = document.getElementById('btnLoading');
            document.getElementById('installBtn').disabled = true;
            btnText.classList.add('hidden');
            btnLoading.classList.remove('hidden');
            btnLoading.classList.add('flex');
        });
    </script>
    @endif
@endsection
