<x-admin.layouts.app>
    <div x-data="sidebar()" class="flex min-h-screen">
        <!-- Sidebar -->
        <aside 
            :class="['fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-gray-200 transform transition-transform duration-300 lg:translate-x-0', open ? 'translate-x-0' : '-translate-x-full']" 
            @keydown.escape="open = false"
            @click.outside="open = false"
        >
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="flex items-center justify-between h-16 px-6 border-b border-gray-200">
                    <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold text-primary-600">
                        Altobul Admin
                    </a>
                    <button 
                        @click="open = false" 
                        class="lg:hidden text-gray-500 hover:text-gray-700"
                        aria-label="Cerrar menú"
                    >
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                
                <!-- Navigation -->
                <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
                    <div>
                        <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Principal</h3>
                        <a href="{{ route('admin.dashboard') }}" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fas fa-tachometer-alt w-5 text-center mr-3"></i>
                            Dashboard
                        </a>
                    </div>
                    
                    <div>
                        <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4 mb-2">Gestión</h3>
                        <a href="{{ route('admin.users.index') }}" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.users*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fas fa-users w-5 text-center mr-3"></i>
                            Usuarios
                        </a>
                        <a href="{{ route('admin.geo-zones.index') }}" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.geo-zones*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fas fa-map-marked-alt w-5 text-center mr-3"></i>
                            GeoZonas
                        </a>
                        <a href="{{ route('admin.profile-fields.index') }}" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.profile-fields*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fas fa-list-alt w-5 text-center mr-3"></i>
                            Campos de Perfil
                        </a>
                        <a href="{{ route('admin.verifications.index') }}" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.verifications*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fas fa-user-check w-5 text-center mr-3"></i>
                            Verificaciones
                        </a>
                    </div>
                    
                    <div>
                        <h3 class="px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider mt-4 mb-2">Configuración</h3>
                        <a href="{{ route('admin.config.index') }}" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.config*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fas fa-cog w-5 text-center mr-3"></i>
                            Configuración
                        </a>
                        <a href="{{ route('admin.api-keys.index') }}" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.api-keys*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fas fa-key w-5 text-center mr-3"></i>
                            API Keys
                        </a>
                        <a href="{{ route('admin.audit-logs.index') }}" class="nav-item flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('admin.audit-logs*') ? 'bg-primary-50 text-primary-700' : 'text-gray-600 hover:bg-gray-100' }}">
                            <i class="fas fa-history w-5 text-center mr-3"></i>
                            Auditoría
                        </a>
                    </div>
                </nav>
                
                <!-- Footer -->
                <div class="p-4 border-t border-gray-200">
                    <div class="flex items-center px-3 py-2">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center">
                                <i class="fas fa-user-shield text-primary-600"></i>
                            </div>
                        </div>
                        <div class="ml-3 flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->email }}</p>
                            <p class="text-xs text-gray-500 truncate">Administrador</p>
                        </div>
                    </div>
                    <form action="{{ route('admin.logout') }}" method="POST" class="mt-3">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center px-3 py-2 text-sm font-medium text-gray-600 rounded-lg hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2"></i>
                            Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </aside>
        
        <!-- Mobile overlay -->
        <div 
            x-show="open" 
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-30 bg-black/50 lg:hidden"
            @click="open = false"
            aria-hidden="true"
        ></div>
        
        <!-- Main content -->
        <div class="flex-1 lg:ml-64 min-h-screen">
            <!-- Topbar -->
            <header class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center justify-between h-16 px-4 sm:px-6">
                    <button 
                        @click="open = true" 
                        class="lg:hidden p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100"
                        aria-label="Abrir menú"
                    >
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    
                    <div class="flex-1 lg:flex-none">
                        <h1 class="text-lg font-semibold text-gray-900">{{ $pageTitle ?? 'Altobul Admin' }}</h1>
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <!-- Notifications -->
                        <div class="relative">
                            <button class="p-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 relative">
                                <i class="fas fa-bell text-xl"></i>
                                <span class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">3</span>
                            </button>
                        </div>
                        
                        <!-- User menu -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" @click.outside="open = false" class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-100">
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
                    </div>
                </div>
            </header>
            
            <!-- Page content -->
            <main class="p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-admin.layouts.app>