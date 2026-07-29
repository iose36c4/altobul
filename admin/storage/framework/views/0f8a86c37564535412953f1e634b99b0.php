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
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-16 w-16 rounded-2xl bg-primary-100 flex items-center justify-center">
                    <i class="fas fa-heart text-3xl text-primary-600"></i>
                </div>
                <h2 class="mt-6 text-center text-3xl font-bold text-gray-900">Altobul Admin</h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Inicia sesión para acceder al panel de administración
                </p>
            </div>
            
            <form class="mt-8 space-y-6" method="POST" action="<?php echo e(route('admin.login.post')); ?>">
                <?php echo csrf_field(); ?>
                
                <?php if($errors->any()): ?>
                    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                        <ul class="list-disc list-inside space-y-1">
                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <li><?php echo e($error); ?></li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <div class="space-y-4">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" id="email" required autocomplete="email" 
                               value="<?php echo e(old('email')); ?>" class="mt-1 input-field w-full" placeholder="admin@altobul.com">
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Contraseña</label>
                        <input type="password" name="password" id="password" required autocomplete="current-password" 
                               class="mt-1 input-field w-full" placeholder="••••••••">
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <label class="flex items-center">
                            <input type="checkbox" name="remember" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                            <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                        </label>
                    </div>
                </div>
                
                <div>
                    <button type="submit" class="btn-primary w-full py-3">
                        <i class="fas fa-sign-in-alt mr-2"></i>Iniciar sesión
                    </button>
                </div>
            </form>
            
            <p class="text-center text-sm text-gray-500">
                ¿Olvidaste la contraseña? Contacta al administrador del sistema.
            </p>
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
<?php endif; ?><?php /**PATH /var/www/html/resources/views/auth/admin-login.blade.php ENDPATH**/ ?>