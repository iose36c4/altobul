@extends('installer.layout')

@section('title', 'Instalación Exitosa - Altobul')

@section('content')
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">¡Instalación completada!</h2>
        <p class="text-gray-600 text-center mb-8">Tu backend Altobul está listo para usar</p>

        <div class="space-y-6">
            <div class="p-4 bg-gray-50 rounded-lg">
                <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Administrador
                </h3>
                <dl class="grid grid-cols-2 gap-2 text-sm">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-gray-900">{{ $admin['email'] }}</dd>
                    <dt class="text-gray-500">Rol</dt>
                    <dd class="font-medium text-gray-900">{{ $admin['role'] }}</dd>
                </dl>
            </div>

            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="font-medium text-blue-900 mb-2">Próximos pasos</h3>
                <ol class="text-sm text-blue-800 space-y-2 list-decimal list-inside">
                    <li>Crea claves API desde el <strong>panel de administración</strong> o vía <code>POST /admin/api-keys</code></li>
                    <li>Verifica salud: <code>GET /api/install/status</code></li>
                    <li>Inicia Reverb: <code>php artisan reverb:start</code></li>
                    <li>Inicia workers: <code>php artisan queue:work</code></li>
                </ol>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="/" class="text-blue-600 hover:text-blue-700 font-medium">Volver al inicio</a>
        </div>
    </div>
@endsection
