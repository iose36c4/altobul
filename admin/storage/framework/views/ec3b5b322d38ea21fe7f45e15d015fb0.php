<?php if (isset($component)) { $__componentOriginal3ea99e3f680c8be2c74e6874e47248b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.layouts.app','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg w-full space-y-8">
            <div>
                <div class="mx-auto h-16 w-16 rounded-2xl bg-primary-100 flex items-center justify-center">
                    <i class="fas fa-heart text-3xl text-primary-600"></i>
                </div>
                <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Altobul Admin</h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Configuración inicial del panel de administración
                </p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <span class="text-blue-600 font-bold text-sm">1</span>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900">Conectar al backend</h2>
                </div>

                <?php if($errors->any()): ?>
                    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">
                        <ul class="list-disc list-inside">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <div id="error-box" class="hidden mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700"></div>

                <form method="POST" action="<?php echo e(route('install.save')); ?>" class="space-y-5">
                    <?php echo csrf_field(); ?>

                    <div>
                        <label for="backend_url" class="block text-sm font-medium text-gray-700 mb-1">URL del Backend</label>
                        <input type="url" name="backend_url" id="backend_url" required
                               value="<?php echo e(old('backend_url', $defaultUrl)); ?>"
                               class="input-field w-full"
                               placeholder="http://localhost:8000">
                        <p class="text-xs text-gray-500 mt-1">URL base del backend Laravel (sin trailing slash)</p>
                    </div>

                    <div>
                        <label for="api_key" class="block text-sm font-medium text-gray-700 mb-1">Clave API de Administración</label>
                        <input type="text" name="api_key" id="api_key" required
                               value="<?php echo e(old('api_key')); ?>"
                               class="input-field w-full font-mono"
                               placeholder="ab_adm_xxxxxxxxxxxxxxxx">
                        <p class="text-xs text-gray-500 mt-1">Clave API con permisos de administrador (tipo ADMIN)</p>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button type="button" id="test-btn"
                                class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition">
                            Probar conexión
                        </button>
                        <button type="submit" id="save-btn"
                                class="flex-1 bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition">
                            Guardar y continuar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('test-btn').addEventListener('click', async function() {
            const btn = this;
            const errorBox = document.getElementById('error-box');
            btn.disabled = true;
            btn.textContent = 'Probando...';
            errorBox.classList.add('hidden');

            const formData = new FormData();
            formData.append('backend_url', document.getElementById('backend_url').value);
            formData.append('api_key', document.getElementById('api_key').value);
            formData.append('_token', '<?php echo e(csrf_token()); ?>');

            try {
                const res = await fetch('<?php echo e(route('install.test')); ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                    body: formData,
                });
                const data = await res.json();
                if (data.success) {
                    btn.textContent = '✓ Conectado';
                    btn.className = 'flex-1 bg-green-100 text-green-700 py-3 px-4 rounded-lg font-medium';
                } else {
                    errorBox.textContent = data.message;
                    errorBox.classList.remove('hidden');
                    btn.textContent = 'Error';
                    btn.className = 'flex-1 bg-red-100 text-red-700 py-3 px-4 rounded-lg font-medium';
                }
            } catch (e) {
                errorBox.textContent = 'Error de red: ' + e.message;
                errorBox.classList.remove('hidden');
            }

            setTimeout(() => {
                btn.disabled = false;
                btn.textContent = 'Probar conexión';
                btn.className = 'flex-1 bg-gray-100 text-gray-700 py-3 px-4 rounded-lg font-medium hover:bg-gray-200 transition';
            }, 3000);
        });
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9)): ?>
<?php $attributes = $__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9; ?>
<?php unset($__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3ea99e3f680c8be2c74e6874e47248b9)): ?>
<?php $component = $__componentOriginal3ea99e3f680c8be2c74e6874e47248b9; ?>
<?php unset($__componentOriginal3ea99e3f680c8be2c74e6874e47248b9); ?>
<?php endif; ?>
<?php /**PATH /var/www/html/resources/views/install/index.blade.php ENDPATH**/ ?>