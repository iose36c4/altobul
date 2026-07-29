<?php if (isset($component)) { $__componentOriginal3ea99e3f680c8be2c74e6874e47248b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.layouts.app','data' => ['pageTitle' => 'Verificaciones']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pageTitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Verificaciones')]); ?>
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Solicitudes de Verificación</h1>
            <div class="flex gap-3">
                <span class="badge badge-amber self-center">Pendientes: <?php echo e(collect($requests)->where('status', 'PENDING')->count()); ?></span>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="input-field">
                        <option value="">Todos</option>
                        <option value="PENDING" <?php echo e(($filters['status'] ?? '') === 'PENDING' ? 'selected' : ''); ?>>Pendientes</option>
                        <option value="APPROVED" <?php echo e(($filters['status'] ?? '') === 'APPROVED' ? 'selected' : ''); ?>>Aprobadas</option>
                        <option value="REJECTED" <?php echo e(($filters['status'] ?? '') === 'REJECTED' ? 'selected' : ''); ?>>Rechazadas</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Método</label>
                    <select name="method" class="input-field">
                        <option value="">Todos</option>
                        <option value="email" <?php echo e(($filters['method'] ?? '') === 'email' ? 'selected' : ''); ?>>Email</option>
                        <option value="phone" <?php echo e(($filters['method'] ?? '') === 'phone' ? 'selected' : ''); ?>>Teléfono</option>
                        <option value="id_document" <?php echo e(($filters['method'] ?? '') === 'id_document' ? 'selected' : ''); ?>>Documento ID</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                    <input type="date" name="date_from" value="<?php echo e($filters['date_from'] ?? ''); ?>" class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                    <input type="date" name="date_to" value="<?php echo e($filters['date_to'] ?? ''); ?>" class="input-field">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="btn-primary w-full sm:w-auto">Filtrar</button>
                    <a href="<?php echo e(route('admin.verifications.index')); ?>" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 w-full sm:w-auto text-center">Limpiar</a>
                </div>
            </form>
        </div>
        
        <?php if(empty($requests)): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <i class="fas fa-user-check text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No hay solicitudes de verificación.</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Método</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Referencia</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enviado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4">
                                    <a href="<?php echo e(route('admin.verifications.show', $req['id'])); ?>" class="font-medium text-gray-900 hover:text-primary-600">
                                        <?php echo e($req['user']['email'] ?? 'N/A'); ?>

                                    </a>
                                    <p class="text-xs text-gray-500 font-mono"><?php echo e($req['user']['id'] ?? ''); ?></p>
                                    <?php if(!empty($req['user']['profile']['title'])): ?>
                                        <p class="text-xs text-gray-400"><?php echo e($req['user']['profile']['title']); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-700"><?php echo e($req['verification_method']); ?></td>
                                <td class="px-4 py-4 text-sm text-gray-500 font-mono"><?php echo e($req['external_reference'] ?? 'N/A'); ?></td>
                                <td class="px-4 py-4 text-sm text-gray-500">
                                    <?php echo e(\Carbon\Carbon::parse($req['submitted_at'])->format('d/m/Y H:i')); ?>

                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge <?php echo e($req['status'] === 'PENDING' ? 'badge-amber' : 
                                        ($req['status'] === 'APPROVED' ? 'badge-green' : 'badge-red')); ?>">
                                        <?php echo e($req['status']); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?php echo e(route('admin.verifications.show', $req['id'])); ?>" class="text-primary-600 hover:text-primary-800 text-sm font-medium">Ver</a>
                                        <?php if($req['status'] === 'PENDING'): ?>
                                            <form action="<?php echo e(route('admin.verifications.approve', $req['id'])); ?>" method="POST" class="inline" onsubmit="return confirm('¿Aprobar esta verificación?')">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">Aprobar</button>
                                            </form>
                                            <a href="<?php echo e(route('admin.verifications.show', $req['id'])); ?>#reject" class="text-red-600 hover:text-red-800 text-sm font-medium">Rechazar</a>
                                        <?php endif; ?>
                                    </div>
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
                            <a href="?page=<?php echo e($i); ?><?php echo e(!empty($filters) ? '&' . http_build_query($filters) : ''); ?>" 
                               class="px-3 py-2 rounded-lg text-sm font-medium <?php echo e($i == ($pagination['current_page'] ?? 1) ? 'bg-primary-600 text-white' : 'text-gray-600 hover:bg-gray-100'); ?>">
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
<?php endif; ?><?php /**PATH /var/www/html/resources/views/admin/verifications/index.blade.php ENDPATH**/ ?>