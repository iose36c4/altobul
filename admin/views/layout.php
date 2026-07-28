<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Altobul Admin' ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>
    <div class="flex h-screen">
        <aside class="w-64 bg-gray-900 text-white flex flex-col">
            <div class="p-6 border-b border-gray-800">
                <h1 class="text-xl font-bold">Altobul</h1>
                <p class="text-xs text-gray-400 mt-1">Panel de Administración</p>
            </div>
            <nav class="flex-1 p-4 space-y-1">
                <a href="/" class="nav-link <?= $_SERVER['REQUEST_URI'] === '/' ? 'active' : '' ?>">
                    <span class="icon">📊</span> Dashboard
                </a>
                <a href="/api-keys" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/api-keys') ? 'active' : '' ?>">
                    <span class="icon">🔑</span> API Keys
                </a>
                <a href="/users" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/users') ? 'active' : '' ?>">
                    <span class="icon">👥</span> Usuarios
                </a>
                <a href="/geo-zones" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/geo-zones') ? 'active' : '' ?>">
                    <span class="icon">🗺️</span> GeoZonas
                </a>
                <a href="/profile-fields" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/profile-fields') ? 'active' : '' ?>">
                    <span class="icon">📋</span> Campos de Perfil
                </a>
                <a href="/verifications" class="nav-link <?= str_starts_with($_SERVER['REQUEST_URI'], '/verifications') ? 'active' : '' ?>">
                    <span class="icon">✅</span> Verificaciones
                </a>
            </nav>
            <div class="p-4 border-t border-gray-800">
                <form method="POST" action="/logout">
                    <button type="submit" class="nav-link w-full text-left">
                        <span class="icon">🚪</span> Cerrar sesión
                    </button>
                </form>
            </div>
        </aside>
        <main class="flex-1 overflow-auto bg-gray-50">
            <div class="p-8">
                <?= $content ?>
            </div>
        </main>
    </div>
    <script src="/js/app.js"></script>
</body>
</html>
