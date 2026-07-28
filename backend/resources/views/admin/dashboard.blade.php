@extends('admin.layout')

@section('title', 'API Keys - Altobul Admin')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Claves API</h2>
        <a href="{{ route('admin.keys.create') }}"
           class="bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition text-sm">
            + Nueva clave
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    @if ($keys->isEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No hay claves API</h3>
            <p class="text-gray-500 mb-4">Creá tu primera clave para conectar una aplicación.</p>
            <a href="{{ route('admin.keys.create') }}"
               class="inline-block bg-blue-600 text-white py-2 px-4 rounded-lg font-medium hover:bg-blue-700 transition text-sm">
                Crear primera clave
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Prefijo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Creada</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expira</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($keys as $key)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $key->name }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded
                                    {{ $key->type === 'CLIENT' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $key->type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-600">{{ $key->key_prefix }}****</td>
                            <td class="px-6 py-4">
                                @if ($key->revoked_at)
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-red-100 text-red-800">Revocada</span>
                                @elseif ($key->expires_at && $key->expires_at->isPast())
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-yellow-100 text-yellow-800">Expirada</span>
                                @else
                                    <span class="px-2 py-1 text-xs font-medium rounded bg-green-100 text-green-800">Activa</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $key->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $key->expires_at ? $key->expires_at->format('d/m/Y') : 'Sin expirar' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                @if (! $key->revoked_at)
                                    <form method="POST" action="{{ route('admin.keys.destroy', $key) }}"
                                          onsubmit="return confirm('¿Revocar esta clave? Se desconectarán todas las aplicaciones que la use.')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="text-sm text-red-600 hover:text-red-800 font-medium">
                                            Revocar
                                        </button>
                                    </form>
                                @else
                                    <span class="text-sm text-gray-400">Revocada</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection
