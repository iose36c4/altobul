<?php if (isset($component)) { $__componentOriginal3ea99e3f680c8be2c74e6874e47248b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.layouts.app','data' => ['pageTitle' => 'Usuarios']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pageTitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Usuarios')]); ?>
    <div class="max-w-full">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Usuarios</h1>
            <div class="flex gap-3">
                <a href="<?php echo e(route('admin.users.export')); ?>" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition flex items-center gap-2">
                    <i class="fas fa-download"></i> Exportar CSV
                </a>
            </div>
        </div>
        
        <!-- Filters -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                    <input type="text" name="search" value="<?php echo e($filters['search'] ?? ''); ?>" placeholder="Email o ID..." class="input-field">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="role" class="input-field">
                        <option value="">Todos</option>
                        <option value="user" <?php echo e(($filters['role'] ?? '') === 'user' ? 'selected' : ''); ?>>Usuario</option>
                        <option value="admin" <?php echo e(($filters['role'] ?? '') === 'admin' ? 'selected' : ''); ?>>Admin</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                    <select name="status" class="input-field">
                        <option value="">Todos</option>
                        <option value="active" <?php echo e(($filters['status'] ?? '') === 'active' ? 'selected' : ''); ?>>Activo</option>
                        <option value="suspended" <?php echo e(($filters['status'] ?? '') === 'suspended' ? 'selected' : ''); ?>>Suspendido</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Verificación</label>
                    <select name="verification_status" class="input-field">
                        <option value="">Todos</option>
                        <option value="unverified" <?php echo e(($filters['verification_status'] ?? '') === 'unverified' ? 'selected' : ''); ?>>No verificado</option>
                        <option value="pending" <?php echo e(($filters['verification_status'] ?? '') === 'pending' ? 'selected' : ''); ?>>Pendiente</option>
                        <option value="verified" <?php echo e(($filters['verification_status'] ?? '') === 'verified' ? 'selected' : ''); ?>>Verificado</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="btn-primary w-full sm:w-auto">Filtrar</button>
                </div>
            </form>
        </div>
        
        <?php if(empty($users)): ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <i class="fas fa-users text-4xl text-gray-300 mb-4"></i>
                <p class="text-gray-500">No hay usuarios que coincidan con los filtros.</p>
            </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rol</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Verificación</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Último acceso</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-4">
                                    <a href="<?php echo e(route('admin.users.show', $user['id'])); ?>" class="font-medium text-gray-900 hover:text-primary-600">
                                        <?php echo e($user['email']); ?>

                                    </a>
                                    <p class="text-xs text-gray-500 font-mono"><?php echo e($user['id']); ?></p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge <?php echo e(($user['role'] ?? '') === 'admin' ? 'badge-purple' : 'badge-blue'); ?>">
                                        <?php echo e(ucfirst($user['role'] ?? '')); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge <?php echo e(($user['status'] ?? '') === 'active' ? 'badge-green' : 'badge-red'); ?>">
                                        <?php echo e(ucfirst($user['status'] ?? '')); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="badge <?php echo e(($user['verification_status'] ?? '') === 'verified' ? 'badge-green' : 
                                        (($user['verification_status'] ?? '') === 'pending' ? 'badge-amber' : 'badge-gray')); ?>">
                                        <?php echo e(ucfirst($user['verification_status'] ?? '')); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500">
                                    <?php echo e($user['last_seen_at'] ? \Carbon\Carbon::parse($user['last_seen_at'])->diffForHumans() : 'Nunca'); ?>

                                </td>
                                <td class="px-4 py-4 text-sm text-gray-500">
                                    <?php echo e(\Carbon\Carbon::parse($user['created_at'])->format('d/m/Y')); ?>

                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="<?php echo e(route('admin.users.show', $user['id'])); ?>" class="text-primary-600 hover:text-primary-800 text-sm font-medium">Ver</a>
                                        <?php if(auth()->id() !== $user['id']): ?>
                                            <?php if(($user['status'] ?? '') === 'active'): ?>
                                                <form action="<?php echo e(route('admin.users.suspend', $user['id'])); ?>" method="POST" class="inline" onsubmit="return confirm('¿Suspender a este usuario?')">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="text-amber-600 hover:text-amber-800 text-sm font-medium">Suspender</button>
                                                </form>
                                            <?php else: ?>
                                                <form action="<?php echo e(route('admin.users.activate', $user['id'])); ?>" method="POST" class="inline">
                                                    <?php echo csrf_field(); ?>
                                                    <button type="submit" class="text-green-600 hover:text-green-800 text-sm font-medium">Activar</button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form action="<?php echo e(route('admin.users.change-role', $user['id'])); ?>" method="POST" class="inline" onsubmit="return confirm('¿Cambiar rol a <?php echo e(($user['role'] ?? '') === 'admin' ? 'usuario' : 'admin'); ?>?')">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="role" value="<?php echo e(($user['role'] ?? '') === 'admin' ? 'user' : 'admin'); ?>">
                                                <button type="submit" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                                    <?php echo e(($user['role'] ?? '') === 'admin' ? 'Quitar admin' : 'Hacer admin'); ?>

                                                </button>
                                            </form>
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
<?php endif; ?><?php /**PATH /var/www/html/resources/views/admin/users/index.blade.php ENDPATH**/ ?>