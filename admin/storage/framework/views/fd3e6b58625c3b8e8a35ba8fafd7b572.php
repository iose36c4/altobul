<?php if (isset($component)) { $__componentOriginal3ea99e3f680c8be2c74e6874e47248b9 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal3ea99e3f680c8be2c74e6874e47248b9 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.admin.layouts.app','data' => ['pageTitle' => 'Configuración']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('admin.layouts.app'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['pageTitle' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute('Configuración')]); ?>
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Configuración del Sistema</h1>
        </div>
        
        <form method="POST" action="<?php echo e(route('admin.config.update')); ?>" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>
            
            <?php $__currentLoopData = $configs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $value): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border-t border-gray-200 pt-6 first:border-0">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-2">
                        <div>
                            <label class="block text-sm font-medium text-gray-700"><?php echo e($key); ?></label>
                            <p class="text-xs text-gray-500 mt-1 font-mono"><?php echo e(gettype($value) === 'array' ? 'array' : (gettype($value) === 'object' ? 'object' : 'string')); ?></p>
                        </div>
                    </div>
                    
                    <?php
                        $isJson = is_array($value) || is_object($value);
                        $displayValue = $isJson ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : (string)$value;
                    ?>
                    
                    <div>
                        <input type="hidden" name="<?php echo e($key); ?>_type" value="<?php echo e($isJson ? 'json' : 'string'); ?>">
                        
                        <?php if($isJson): ?>
                            <textarea name="<?php echo e($key); ?>" rows="6" class="input-field font-mono text-sm" placeholder="JSON válido"><?php echo e($displayValue); ?></textarea>
                            <p class="mt-1 text-xs text-gray-500">Edita como JSON. Ejemplo: {"key": "value", "number": 123, "bool": true}</p>
                        <?php else: ?>
                            <input type="text" name="<?php echo e($key); ?>" value="<?php echo e($displayValue); ?>" class="input-field" placeholder="Valor">
                        <?php endif; ?>
                        
                        <?php $__errorArgs = [$key];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            
            <div class="pt-4 border-t border-gray-200">
                <div class="flex gap-3 justify-end">
                    <a href="<?php echo e(route('admin.config.index')); ?>" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition">Cancelar</a>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save mr-2"></i>Guardar configuración
                    </button>
                </div>
            </div>
        </form>
        
        <div class="mt-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
            <h3 class="font-medium text-amber-800 mb-2">⚠️ Importante</h3>
            <ul class="text-sm text-amber-700 space-y-1">
                <li>• Los cambios surten efecto inmediatamente en la API backend</li>
                <li>• Los valores JSON deben ser sintácticamente válidos</li>
                <li>• Algunas configuraciones requieren reinicio de workers de cola</li>
                <li>• Cada cambio queda registrado en los logs de auditoría</li>
            </ul>
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
    // Validate JSON fields on blur
    document.querySelectorAll('textarea[name]').forEach(textarea => {
        textarea.addEventListener('blur', function() {
            if (this.value.trim()) {
                try {
                    JSON.parse(this.value);
                    this.classList.remove('border-red-500');
                    this.classList.add('border-green-500');
                    setTimeout(() => this.classList.remove('border-green-500'), 2000);
                } catch (e) {
                    this.classList.add('border-red-500');
                    this.classList.remove('border-green-500');
                }
            }
        });
    });
});
</script>
<?php $__env->stopPush(); ?><?php /**PATH /var/www/html/resources/views/admin/config/index.blade.php ENDPATH**/ ?>