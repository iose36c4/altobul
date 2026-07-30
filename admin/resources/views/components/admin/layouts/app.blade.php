{{-- resources/views/admin/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Altobul Admin' }}</title>
    
    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd',
                            400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8',
                            800: '#1e40af', 900: '#1e3a8a'
                        }
                    }
                }
            }
        }
    </script>
    
    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    
    {{-- Leaflet CSS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.css" />
    
    {{-- Custom CSS --}}
    <style>
        .drag-handle { cursor: grab; }
        .drag-handle:active { cursor: grabbing; }
        .sortable-ghost { opacity: 0.4; background: #dbeafe; }
        .leaflet-draw-toolbar { z-index: 1000 !important; }
        [x-cloak] { display: none !important; }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-gray-50">
    <div class="flex h-full" x-data="{ sidebarOpen: false }">
        {{-- Sidebar --}}
        <aside class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 lg:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" @keydown.escape="sidebarOpen = false">
            <div class="flex flex-col h-full">
                {{-- Logo --}}
                <div class="p-4 border-b border-gray-200">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                        <div class="w-10 h-10 rounded-lg bg-primary-600 flex items-center justify-center">
                            <i class="fas fa-heart text-white text-xl"></i>
                        </div>
                        <span class="text-xl font-bold text-gray-900">Altobul Admin</span>
                    </a>
                </div>
                
                {{-- Navigation --}}
                <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
                    <div>
                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <i class="fas fa-chart-line w-5 text-center"></i>
                            <span>Dashboard</span>
                        </a>
                    </div>
                    
                    <hr class="my-3 border-gray-200">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Gestión</p>
                    
                    <div>
                        <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <i class="fas fa-users w-5 text-center"></i>
                            <span>Usuarios</span>
                        </a>
                    </div>
                    
                    <div>
                        <a href="{{ route('admin.geo-zones.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <i class="fas fa-map-marked-alt w-5 text-center"></i>
                            <span>GeoZonas</span>
                        </a>
                    </div>
                    
                    <div>
                        <a href="{{ route('admin.profile-fields.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <i class="fas fa-list-ol w-5 text-center"></i>
                            <span>Campos de Perfil</span>
                        </a>
                    </div>
                    
                    <div>
                        <a href="{{ route('admin.verifications.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <i class="fas fa-user-check w-5 text-center"></i>
                            <span>Verificaciones</span>
                        </a>
                    </div>
                    
                    <div>
                        <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <i class="fas fa-flag w-5 text-center"></i>
                            <span>Reportes</span>
                        </a>
                    </div>
                    
                    <hr class="my-3 border-gray-200">
                    <p class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Configuración</p>
                    
                    <div>
                        <a href="{{ route('admin.config.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <i class="fas fa-cogs w-5 text-center"></i>
                            <span>Configuración</span>
                        </a>
                    </div>
                    
                    <div>
                        <a href="{{ route('admin.api-keys.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <i class="fas fa-key w-5 text-center"></i>
                            <span>API Keys</span>
                        </a>
                    </div>
                    
                    <div>
                        <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <i class="fas fa-history w-5 text-center"></i>
                            <span>Auditoría</span>
                        </a>
                    </div>
                </nav>
                
                {{-- Footer --}}
                <div class="p-4 border-t border-gray-200">
                    @auth
                    <div class="flex items-center gap-3 px-3 py-2">
                        <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                            <i class="fas fa-user-shield text-primary-600"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->email }}</p>
                            <p class="text-xs text-gray-500">Administrador</p>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                            <i class="fas fa-sign-out-alt"></i>
                            Cerrar sesión
                        </button>
                    </form>
                    @else
                    <div class="text-center text-gray-500 text-sm py-2">
                        Panel de administración Altobul
                    </div>
                    @endauth
                </div>
            </div>
        </aside>
        
        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-30 bg-black/50 lg:hidden" @click="sidebarOpen = false"></div>
        
        {{-- Main content --}}
        <div class="flex-1 flex flex-col lg:pl-64">
            {{-- Top bar --}}
            <header class="sticky top-0 z-20 bg-white border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h1 class="text-lg font-semibold text-gray-900">{{ $pageTitle ?? 'Altobul Admin' }}</h1>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        {{-- Notifications --}}
                        <div class="relative">
                            <button class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 relative">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center">3</span>
                            </button>
                        </div>
                        
                        {{-- User menu --}}
                        @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100">
                                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                    <i class="fas fa-user-shield text-primary-600"></i>
                                </div>
                                <span class="hidden sm:block text-sm font-medium text-gray-700">{{ auth()->user()->email }}</span>
                                <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                            </button>
                            
                            <div x-show="open" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-50">
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Mi perfil</a>
                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Configuración</a>
                                <hr class="my-1 border-gray-200">
                                <form action="{{ route('admin.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">Cerrar sesión</button>
                                </form>
                            </div>
                        </div>
                        @else
                        <a href="{{ route('admin.login') }}" class="text-primary-600 hover:text-primary-800 text-sm font-medium">Iniciar sesión</a>
                        @endauth
                    </div>
                </div>
            </header>
            
            {{-- Flash messages --}}
            <div class="p-4 sm:p-6 lg:p-8">
                @if (session('success'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between">
                        <span>{{ session('success') }}</span>
                        <button @click="show = false" class="text-green-600 hover:text-green-800"><i class="fas fa-times"></i></button>
                    </div>
                @endif
                
                @if (session('error'))
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg flex items-center justify-between">
                        <span>{{ session('error') }}</span>
                        <button @click="show = false" class="text-red-600 hover:text-red-800"><i class="fas fa-times"></i></button>
                    </div>
                @endif
                
                @if ($errors->any())
                    <div x-data="{ show: true }" x-show="show" x-transition class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                {{-- Page content --}}
                {{ $slot }}
            </div>
        </div>
    </div>
    
    {{-- Leaflet JS --}}
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-draw@1.0.4/dist/leaflet.draw.js"></script>
    
    {{-- SortableJS --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.1/Sortable.min.js"></script>
    
    {{-- Custom JS --}}
    @vite('resources/js/app.js')
    {{-- Allow child views to push scripts --}}
    @if (isset($scripts))
        {{ $scripts }}
    @endif
</body>
</html>