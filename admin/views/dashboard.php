<?php $pageTitle = 'Dashboard - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
    <p class="text-gray-600 mt-1">Resumen del sistema</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">API Keys</p>
        <p class="text-3xl font-bold text-gray-900"><?= $stats['total_keys'] ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Usuarios</p>
        <p class="text-3xl font-bold text-gray-900"><?= $stats['total_users'] ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">GeoZonas</p>
        <p class="text-3xl font-bold text-gray-900"><?= $stats['total_zones'] ?></p>
    </div>
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <p class="text-sm text-gray-500 mb-1">Verificaciones pendientes</p>
        <p class="text-3xl font-bold <?= $stats['pending_verifications'] > 0 ? 'text-amber-600' : 'text-gray-900' ?>">
            <?= $stats['pending_verifications'] ?>
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <a href="/api-keys" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
        <h3 class="font-bold text-gray-900 mb-1">🔑 API Keys</h3>
        <p class="text-sm text-gray-600">Gestionar claves de acceso a la API</p>
    </a>
    <a href="/users" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
        <h3 class="font-bold text-gray-900 mb-1">👥 Usuarios</h3>
        <p class="text-sm text-gray-600">Administrar usuarios registrados</p>
    </a>
    <a href="/geo-zones" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
        <h3 class="font-bold text-gray-900 mb-1">🗺️ GeoZonas</h3>
        <p class="text-sm text-gray-600">Gestionar zonas geográficas y polígonos</p>
    </a>
    <a href="/profile-fields" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
        <h3 class="font-bold text-gray-900 mb-1">📋 Campos de Perfil</h3>
        <p class="text-sm text-gray-600">Definir campos disponibles para perfiles</p>
    </a>
    <a href="/verifications" class="block bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition">
        <h3 class="font-bold text-gray-900 mb-1">✅ Verificaciones</h3>
        <p class="text-sm text-gray-600">Revisar solicitudes de verificación de identidad</p>
    </a>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
