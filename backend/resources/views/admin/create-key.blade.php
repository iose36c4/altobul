@extends('admin.layout')

@section('title', 'Nueva API Key - Altobul Admin')

@section('content')
    <div class="w-full max-w-lg mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Nueva Clave API</h2>
            <p class="text-gray-600 text-center mb-8">Generá una nueva clave para una aplicación</p>

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.keys.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nombre</label>
                    <input type="text" name="name" id="name" required
                           value="{{ old('name') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Ej: App Cliente iOS, CRM, Moderación">
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select name="type" id="type" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                        <option value="CLIENT">CLIENT — App cliente</option>
                        <option value="ADMIN">ADMIN — Panel admin / CRM / Moderación</option>
                    </select>
                </div>

                <div>
                    <label for="expires_in_days" class="block text-sm font-medium text-gray-700 mb-1">Expiración (opcional)</label>
                    <input type="number" name="expires_in_days" id="expires_in_days"
                           value="{{ old('expires_in_days') }}"
                           min="1" max="3650"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="Sin expirar (dejar vacío)">
                    <p class="mt-1 text-xs text-gray-500">Días hasta expirar. Dejar vacío para sin expiración.</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <a href="{{ route('admin.dashboard') }}"
                       class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition text-center">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition">
                        Crear clave
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
