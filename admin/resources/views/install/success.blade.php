<x-admin.layouts.app>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg w-full space-y-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-check text-3xl text-green-600"></i>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">¡Configuración completa!</h2>
                <p class="text-gray-600 mb-8">La app admin está conectada al backend correctamente.</p>
                <a href="{{ route('admin.login') }}" class="inline-block bg-blue-600 text-white py-3 px-6 rounded-lg font-medium hover:bg-blue-700 transition">
                    Ir al inicio de sesión
                </a>
            </div>
        </div>
    </div>
</x-admin.layouts.app>
