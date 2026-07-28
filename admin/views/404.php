<?php $pageTitle = '404 - Altobul Admin'; ?>
<?php ob_start(); ?>

<div class="text-center mt-20">
    <h1 class="text-6xl font-bold text-gray-300 mb-4">404</h1>
    <p class="text-gray-600 mb-6">La página que buscás no existe.</p>
    <a href="/" class="btn-primary inline-block">Volver al dashboard</a>
</div>

<?php $content = ob_get_clean(); ?>
<?php require __DIR__ . '/layout.php'; ?>
