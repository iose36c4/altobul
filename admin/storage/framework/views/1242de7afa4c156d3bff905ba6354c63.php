<?php if (isset($component)) { $__componentOriginal3ea99e3f680c8be2c74e6874e47248b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.layouts.app','data' => ['pageTitle' => 'API Keys']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pageTitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('API Keys')]); ?>
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">API Keys</h1>
            <a href="<?php echo e(route('admin.api-keys.create')); ?>" class="btn-primary">
                <i class="fas fa-plus mr-2"></i>Crear API Key
            </a>
        </div>
        
        <?php if(empty($apiKeys)): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <i class="fas fa-key text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500 mb-4">No hay API Keys creadas.</p>
                <a href="<?php echo e(route('admin.api-keys.create')); ?>" class="btn-primary">Crear primera API Key</a>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prefijo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado por</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expira</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último uso</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__currentLoopData = $apiKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4 font-medium text-gray-900"><?php echo e($key['name']); ?></td>
                                <td class="px-4 py-4">
                                    <span class="badge <?php echo e($key['type'] === 'ADMIN' ? 'badge-purple' : 
                                        ($key['type'] === 'CLIENT' ? 'badge-blue' : 
                                        ($key['type'] === 'MOBILE' ? 'badge-green' : 'badge-gray'))); ?>">
                                        <?php echo e($key['type']); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-4 font-mono text-sm text-gray-600"><?php echo e($key['key_prefix'] ?? substr($key['id'], 0, 8)); ?>...</td>
                                <td class="px-4 py-4 text-sm text-gray-900"><?php echo e($key['creator']['email'] ?? 'N/A'); ?></td>
                                <td class="px-4 py-4 text-sm text-gray-500"><?php echo e(\Carbon\Carbon::parse($key['created_at'])->format('d/m/Y H:i')); ?></td>
                                <td class="px-4 py-4 text-sm text-gray-500">
                                    <?php echo e($key['expires_at'] ? \Carbon\Carbon::parse($key['expires_at'])->format('d/m/Y') : 'Nunca'); ?>

                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500">
                                    <?php echo e($key['last_used_at'] ? \Carbon\Carbon::parse($key['last_used_at'])->diffForHumans() : 'Nunca'); ?>

                                </td>
                                <td class="px-4 py-4">
                                    <?php
                                        $isRevoked = !empty($key['revoked_at']);
                                        $isExpired = !empty($key['expires_at']) && \Carbon\Carbon::parse($key['expires_at'])->isPast();
                                    ?>
                                    <?php if($isRevoked): ?>
                                        <span class="badge badge-red">Revocada</span>
                                    <?php elseif($isExpired): ?>
                                        <span class="badge badge-amber">Expirada</span>
                                    <?php else: ?>
                                        <span class="badge badge-green">Activa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <?php if(!$isRevoked): ?>
                                        <form action="<?php echo e(route('admin.api-keys.destroy', $key['id'])); ?>" method="POST" class="inline" onsubmit="return confirm('¿Revocar esta API Key? Esta acción no se puede deshacer.')">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Revocar</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">Revocada</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            
            
            <?php if(!empty($pagination['last_page']) && $pagination['last_page'] > 1): ?>
                <div class="mt-4 flex justify-center">
                    <nav class="flex items-center gap-2">
                        <?php for($i = 1; $i <= $pagination['last_page']; $i++): ?>
                            <a href="?page=<?php echo e($i); ?>" class="px-3 py-2 rounded-lg text-sm font-medium <?php echo e($i == ($pagination['current_page'] ?? 1) ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100'); ?>">
                                <?php echo e($i); ?>

                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9)): ?>
<?php $attributes = $__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9; ?>
<?php unset($__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal3ea99e3f680c8be2c74e6874e47248b9)): ?>
<?php $component = $__componentOriginal3ea99e3f680c8be2c74e6874e47248b9; ?>
<?php unset($__componentOriginal3ea99e3f680c8be2c74e6874e47248b9); ?>
<?php endif; ?><?php /**PATH /var/www/html/resources/views/admin/api-keys/index.blade.php ENDPATH**/ ?>