<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Altobul Admin')</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 flex flex-col">
    @auth
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-4xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-gray-900">Altobul</a>
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600">{{ Auth::user()->email }}</span>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">Salir</button>
                </form>
            </div>
        </div>
    </header>
    @endauth

    <main class="flex-1 flex items-start justify-center p-4 pt-8">
        <div class="w-full max-w-4xl">
            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-4">
        <div class="max-w-4xl mx-auto px-4 text-center text-sm text-gray-500">
            Altobul v1.0 &mdash; Panel de Administración
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
