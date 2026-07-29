<?php if (isset($component)) { $__componentOriginal3ea99e3f680c8be2c74e6874e47248b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.layouts.app','data' => ['pageTitle' => 'Usuario: ' . ($user['email'] ?? '')]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pageTitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Usuario: ' . ($user['email'] ?? ''))]); ?>
    <div class="max-w-6xl mx-auto">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900"><?php echo e($user['email']); ?></h1>
                <div class="flex items-center gap-3 mt-1">
                    <span class="badge <?php echo e(($user['role'] ?? '') === 'admin' ? 'badge-purple' : 'badge-blue'); ?>"><?php echo e(ucfirst($user['role'] ?? '')); ?></span>
                    <span class="badge <?php echo e(($user['status'] ?? '') === 'active' ? 'badge-green' : 'badge-red'); ?>"><?php echo e(ucfirst($user['status'] ?? '')); ?></span>
                    <span class="badge <?php echo e(($user['verification_status'] ?? '') === 'verified' ? 'badge-green' : 
                        (($user['verification_status'] ?? '') === 'pending' ? 'badge-amber' : 'badge-gray')); ?>">
                        <?php echo e(ucfirst($user['verification_status'] ?? '')); ?>

                    </span>
                    <?php if(($user['is_online'] ?? false)): ?>
                        <span class="badge badge-green"><i class="fas fa-circle text-xs mr-1"></i>Online</span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex gap-3">
                <?php if(auth()->id() !== ($user['id'] ?? '')): ?>
                    <?php if(($user['status'] ?? '') === 'active'): ?>
                        <form action="<?php echo e(route('admin.users.suspend', $user['id'])); ?>" method="POST" class="inline" onsubmit="return confirm('¿Suspender a este usuario?')">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="bg-amber-100 text-amber-700 py-2 px-4 rounded-lg font-medium hover:bg-amber-200">Suspender</button>
                        </form>
                    <?php else: ?>
                        <form action="<?php echo e(route('admin.users.activate', $user['id'])); ?>" method="POST" class="inline">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="bg-green-100 text-green-700 py-2 px-4 rounded-lg font-medium hover:bg-green-200">Activar</button>
                        </form>
                    <?php endif; ?>
                    
                    <form action="<?php echo e(route('admin.users.change-role', $user['id'])); ?>" method="POST" class="inline" onsubmit="return confirm('¿Cambiar rol?')">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="role" value="<?php echo e(($user['role'] ?? '') === 'admin' ? 'user' : 'admin'); ?>">
                        <button type="submit" class="bg-purple-100 text-purple-700 py-2 px-4 rounded-lg font-medium hover:bg-purple-200">
                            <?php echo e(($user['role'] ?? '') === 'admin' ? 'Quitar admin' : 'Hacer admin'); ?>

                        </button>
                    </form>
                <?php endif; ?>
                <a href="<?php echo e(route('admin.users.index')); ?>" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200">← Volver</a>
            </div>
        </div>
        
        <!-- Tabs -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="border-b border-gray-200 overflow-x-auto">
                <nav class="flex gap-8 px-6" aria-label="Tabs">
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-primary-600 border-primary-600" data-tab="datos">Datos</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="perfil">Perfil</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="fotos">Fotos</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="posts">Posts</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="tokes">Tokes</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="matches">Matches</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="amigos">Amigos</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="bloques">Bloques</button>
                    <button class="tab-btn py-4 px-1 border-b-2 font-medium text-sm text-gray-500 hover:text-gray-700 border-transparent" data-tab="verificacion">Verificación</button>
                </nav>
            </div>
            
            <div class="p-6">
                <!-- Tab: Datos -->
                <div class="tab-content" data-tab-content="datos">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm text-gray-500">ID</dt>
                            <dd class="font-mono text-sm text-gray-900 break-all"><?php echo e($user['id']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900"><?php echo e($user['email']); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Rol</dt>
                            <dd class="text-sm text-gray-900"><?php echo e(ucfirst($user['role'] ?? '')); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Estado</dt>
                            <dd class="text-sm text-gray-900"><?php echo e(ucfirst($user['status'] ?? '')); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Verificación</dt>
                            <dd class="text-sm text-gray-900"><?php echo e(ucfirst($user['verification_status'] ?? '')); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Verificado el</dt>
                            <dd class="text-sm text-gray-900"><?php echo e($user['verified_at'] ? \Carbon\Carbon::parse($user['verified_at'])->format('d/m/Y H:i') : 'N/A'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Email verificado</dt>
                            <dd class="text-sm text-gray-900"><?php echo e($user['email_verified_at'] ? \Carbon\Carbon::parse($user['email_verified_at'])->format('d/m/Y H:i') : 'No'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Último acceso</dt>
                            <dd class="text-sm text-gray-900"><?php echo e($user['last_seen_at'] ? \Carbon\Carbon::parse($user['last_seen_at'])->diffForHumans() : 'Nunca'); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Creado</dt>
                            <dd class="text-sm text-gray-900"><?php echo e(\Carbon\Carbon::parse($user['created_at'])->format('d/m/Y H:i')); ?></dd>
                        </div>
                    </dl>
                </div>
                
                <!-- Tab: Perfil -->
                <div class="tab-content hidden" data-tab-content="perfil">
                    <?php if(!empty($user['profile'])): ?>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm text-gray-500">Título</dt>
                                <dd class="text-sm text-gray-900"><?php echo e($user['profile']['title'] ?? 'No establecido'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Descripción</dt>
                                <dd class="text-sm text-gray-900"><?php echo e($user['profile']['description'] ?? 'Sin descripción'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Fecha nacimiento</dt>
                                <dd class="text-sm text-gray-900"><?php echo e($user['profile']['birth_date'] ? \Carbon\Carbon::parse($user['profile']['birth_date'])->format('d/m/Y') : 'No establecido'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Visibilidad perfil</dt>
                                <dd class="text-sm text-gray-900"><?php echo e($user['profile']['profile_visibility'] ?? 'PUBLIC'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Requiere verificación</dt>
                                <dd class="text-sm text-gray-900"><?php echo e(($user['profile']['profile_requires_verified'] ?? false) ? 'Sí' : 'No'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Descubrible</dt>
                                <dd class="text-sm text-gray-900"><?php echo e(($user['profile']['discoverable'] ?? false) ? 'Sí' : 'No'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Precisión ubicación</dt>
                                <dd class="text-sm text-gray-900"><?php echo e($user['profile']['location_precision_meters'] ?? 'N/A'); ?> metros</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Ubicación</dt>
                                <dd class="text-sm text-gray-900">
                                    <?php if($user['profile']['latitude'] && $user['profile']['longitude']): ?>
                                        <?php echo e($user['profile']['latitude']); ?>, <?php echo e($user['profile']['longitude']); ?>

                                    <?php else: ?>
                                        No establecida
                                    <?php endif; ?>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">GeoZona</dt>
                                <dd class="text-sm text-gray-900"><?php echo e($user['profile']['geo_zone_id'] ?? 'Ninguna'); ?></dd>
                            </div>
                        </dl>
                        
                        <?php if(!empty($user['profile']['fields'])): ?>
                            <h4 class="text-lg font-semibold text-gray-900 mt-6 mb-3">Campos personalizados</h4>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $user['profile']['fields']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $field): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between">
                                            <div>
                                                <p class="font-medium text-gray-900"><?php echo e($field['field_label'] ?? $field['field_slug']); ?></p>
                                                <p class="text-sm text-gray-500"><?php echo e($field['field_type']); ?></p>
                                            </div>
                                            <div class="text-right">
                                                <span class="badge badge-blue"><?php echo e($field['visibility']); ?></span>
                                                <?php if($field['requires_verified']): ?>
                                                    <span class="badge badge-amber ml-1">Verificado</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="mt-2">
                                            <p class="text-sm text-gray-700"><?php echo e($field['value']); ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">Este usuario no tiene perfil creado.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Fotos -->
                <div class="tab-content hidden" data-tab-content="fotos">
                    <?php if(!empty($user['photos'])): ?>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                            <?php $__currentLoopData = $user['photos']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-gray-50 rounded-lg overflow-hidden border border-gray-200">
                                    <img src="<?php echo e($photo['file_url'] ?? $photo['url']); ?>" alt="Foto" class="w-full h-32 object-cover">
                                    <div class="p-3">
                                        <div class="flex items-center justify-between text-xs mb-1">
                                            <span class="badge badge-blue"><?php echo e($photo['visibility']); ?></span>
                                            <?php if($photo['is_primary']): ?>
                                                <span class="badge badge-amber">Principal</span>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-xs text-gray-500"><?php echo e($photo['width']); ?>x<?php echo e($photo['height']); ?> • <?php echo e(number_format($photo['size_bytes'] / 1024, 1)); ?> KB</p>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No hay fotos.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Posts -->
                <div class="tab-content hidden" data-tab-content="posts">
                    <?php if(!empty($user['posts'])): ?>
                        <div class="space-y-4">
                            <?php $__currentLoopData = $user['posts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex items-center justify-between mb-2">
                                        <span class="badge badge-blue"><?php echo e($post['visibility']); ?></span>
                                        <span class="text-xs text-gray-500">Expira: <?php echo e(\Carbon\Carbon::parse($post['expires_at'])->format('d/m/Y H:i')); ?></span>
                                    </div>
                                    <p class="text-gray-700 text-sm"><?php echo e(Str::limit($post['content_md'] ?? $post['content'] ?? '', 200)); ?></p>
                                    <?php if($post['attachment']): ?>
                                        <div class="mt-2">
                                            <img src="<?php echo e($post['attachment']['url']); ?>" alt="Adjunto" class="max-h-32 rounded">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No hay posts.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Tokes -->
                <div class="tab-content hidden" data-tab-content="tokes">
                    <?php if(!empty($user['sent_tokes']) || !empty($user['received_tokes'])): ?>
                        <div class="space-y-3">
                            <?php if(!empty($user['sent_tokes'])): ?>
                                <h4 class="font-medium text-gray-900">Enviados</h4>
                                <?php $__currentLoopData = $user['sent_tokes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $toke): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                        <div>
                                            <span class="badge <?php echo e($toke['status'] === 'ACTIVE' ? 'badge-green' : 'badge-gray'); ?>"><?php echo e($toke['status']); ?></span>
                                            <span class="text-sm text-gray-600 ml-2">a <?php echo e($toke['receiver']['email'] ?? 'N/A'); ?></span>
                                        </div>
                                        <span class="text-xs text-gray-500">Expira: <?php echo e(\Carbon\Carbon::parse($toke['expires_at'])->format('d/m/Y H:i')); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                            <?php if(!empty($user['received_tokes'])): ?>
                                <h4 class="font-medium text-gray-900 mt-4">Recibidos</h4>
                                <?php $__currentLoopData = $user['received_tokes']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $toke): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                        <div>
                                            <span class="badge <?php echo e($toke['status'] === 'ACTIVE' ? 'badge-green' : 'badge-gray'); ?>"><?php echo e($toke['status']); ?></span>
                                            <span class="text-sm text-gray-600 ml-2">de <?php echo e($toke['sender']['email'] ?? 'N/A'); ?></span>
                                        </div>
                                        <span class="text-xs text-gray-500">Expira: <?php echo e(\Carbon\Carbon::parse($toke['expires_at'])->format('d/m/Y H:i')); ?></span>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No hay tokes.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Matches -->
                <div class="tab-content hidden" data-tab-content="matches">
                    <?php if(!empty($user['matches'])): ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $user['matches']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $match): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <span class="badge badge-green">Match activo</span>
                                        <span class="text-sm text-gray-600 ml-2">con <?php echo e($match['user_a_id'] === $user['id'] ? ($match['user_b']['email'] ?? 'N/A') : ($match['user_a']['email'] ?? 'N/A')); ?></span>
                                    </div>
                                    <span class="text-xs text-gray-500">Expira: <?php echo e(\Carbon\Carbon::parse($match['expires_at'])->format('d/m/Y H:i')); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No hay matches.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Amigos -->
                <div class="tab-content hidden" data-tab-content="amigos">
                    <?php if(!empty($user['friendships'])): ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $user['friendships']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $friendship): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <span class="badge badge-green">Amigos</span>
                                        <span class="text-sm text-gray-600 ml-2"><?php echo e($friendship['user_a_id'] === $user['id'] ? ($friendship['user_b']['email'] ?? 'N/A') : ($friendship['user_a']['email'] ?? 'N/A')); ?></span>
                                    </div>
                                    <span class="text-xs text-gray-500">Desde: <?php echo e(\Carbon\Carbon::parse($friendship['created_at'])->format('d/m/Y')); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No hay amistades.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Bloques -->
                <div class="tab-content hidden" data-tab-content="bloques">
                    <?php if(!empty($user['blocks'])): ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $user['blocks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $block): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-gray-50 rounded-lg p-3 flex items-center justify-between">
                                    <div>
                                        <span class="badge badge-red">Bloqueado</span>
                                        <span class="text-sm text-gray-600 ml-2">
                                            <?php echo e($block['blocker_id'] === $user['id'] ? 'Bloqueó a ' . ($block['blocked']['email'] ?? 'N/A') : 'Bloqueado por ' . ($block['blocker']['email'] ?? 'N/A')); ?>

                                        </span>
                                    </div>
                                    <span class="text-xs text-gray-500"><?php echo e(\Carbon\Carbon::parse($block['created_at'])->format('d/m/Y')); ?></span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No hay bloqueos.</p>
                    <?php endif; ?>
                </div>
                
                <!-- Tab: Verificación -->
                <div class="tab-content hidden" data-tab-content="verificacion">
                    <?php if(!empty($user['verification_requests'])): ?>
                        <div class="space-y-3">
                            <?php $__currentLoopData = $user['verification_requests']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="badge <?php echo e($req['status'] === 'APPROVED' ? 'badge-green' : 
                                                ($req['status'] === 'REJECTED' ? 'badge-red' : 'badge-amber')); ?>">
                                                <?php echo e($req['status']); ?>

                                            </span>
                                            <span class="text-sm text-gray-600 ml-2"><?php echo e($req['verification_method']); ?></span>
                                        </div>
                                        <span class="text-xs text-gray-500"><?php echo e(\Carbon\Carbon::parse($req['submitted_at'])->format('d/m/Y H:i')); ?></span>
                                    </div>
                                    <?php if($req['rejection_reason']): ?>
                                        <p class="text-sm text-red-600 mt-2">Motivo: <?php echo e($req['rejection_reason']); ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-center py-8">No hay solicitudes de verificación.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
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
<?php endif; ?>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('[data-tab-content]');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tab = this.dataset.tab;
            
            tabBtns.forEach(b => {
                b.classList.remove('text-primary-600', 'border-primary-600');
                b.classList.add('text-gray-500', 'border-transparent');
            });
            this.classList.remove('text-gray-500', 'border-transparent');
            this.classList.add('text-primary-600', 'border-primary-600');
            
            tabContents.forEach(content => {
                if (content.dataset.tabContent === tab) {
                    content.classList.remove('hidden');
                } else {
                    content.classList.add('hidden');
                }
            });
        });
    });
});
</script>
<?php $__env->stopPush(); ?><?php /**PATH /var/www/html/resources/views/admin/users/show.blade.php ENDPATH**/ ?>