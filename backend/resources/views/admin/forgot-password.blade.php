@extends('admin.layout')

@section('title', 'Recuperar contraseña - Altobul Admin')

@section('content')
    <div class="w-full max-w-md mx-auto mt-8">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
            <h2 class="text-2xl font-bold text-gray-900 text-center mb-2">Recuperar contraseña</h2>
            <p class="text-gray-600 text-center mb-8">Ingresá tu email y te enviaremos un enlace para restablecerla</p>

            @if (session('status'))
                <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-800">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('dev_reset_url'))
                <div class="mb-6 p-4 bg-yellow-50 border border-yellow-300 rounded-lg">
                    <p class="text-sm font-semibold text-yellow-800 mb-2">Modo desarrollo — enlace de recuperación:</p>
                    <a href="{{ session('dev_reset_url') }}" class="text-sm text-blue-700 underline break-all">
                        {{ session('dev_reset_url') }}
                    </a>
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg">
                    <ul class="text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.forgot-password.post') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" required autofocus
                           value="{{ old('email') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                           placeholder="admin@altobul.com">
                </div>

                <button type="submit"
                        class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition">
                    Enviar enlace de recuperación
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('admin.login') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                    ← Volver al login
                </a>
            </div>
        </div>
    </div>
@endsection
