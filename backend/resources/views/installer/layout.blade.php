<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Altobul Installer')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-gray-50 flex flex-col">
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-3xl mx-auto px-4 py-4 flex items-center justify-between">
            <h1 class="text-xl font-bold text-gray-900">Altobul</h1>
            <span class="text-xs text-gray-500">Backend Installer</span>
        </div>
    </header>
    
    <main class="flex-1 flex items-center justify-center p-4">
        <div class="w-full max-w-md">
            @yield('content')
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-4">
        <div class="max-w-3xl mx-auto px-4 text-center text-sm text-gray-500">
            Altobul v1.0 &mdash; Geosocial Backend
        </div>
    </footer>

    @stack('scripts')
</body>
</html>