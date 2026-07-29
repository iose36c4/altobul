<x-admin.layouts.app :pageTitle="'Editar Campo: ' . ($field['label'] ?? '')">
    <div class="max-w-3xl mx-auto">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Editar Campo de Perfil</h1>
            <a href="{{ route('admin.profile-fields.index') }}" class="text-primary-600 hover:text-primary-800">← Volver</a>
        </div>
        
        <form method="POST" action="{{ route('admin.profile-fields.update', $field['id']) }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-6" x-data="profileFieldForm()">
            @csrf
            @method('PUT')
            
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">Slug <span class="text-red-500">*</span></label>
                <input type="text" name="slug" id="slug" required value="{{ old('slug', $field['slug'] ?? '') }}" class="input-field" placeholder="ej: altura, color_favorito" pattern="[a-z0-9_-]+" title="Solo minúsculas, números, guiones y guiones bajos">
                <p class="mt-1 text-xs text-gray-500">Identificador único (solo minúsculas, números, guiones y guiones bajos)</p>
                @error('slug')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="label" class="block text-sm font-medium text-gray-700 mb-1">Etiqueta <span class="text-red-500">*</span></label>
                <input type="text" name="label" id="label" required value="{{ old('label', $field['label'] ?? '') }}" class="input-field" placeholder="Ej: Altura, Color favorito">
                @error('label')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descripción</label>
                <textarea name="description" id="description" rows="3" class="input-field" placeholder="Descripción del campo...">{{ old('description', $field['description'] ?? '') }}</textarea>
            </div>
            
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                <select name="type" id="type" required x-model="selectedType" @change="onTypeChange()" class="input-field">
                    <option value="text" {{ old('type', $field['type'] ?? '') === 'text' ? 'selected' : '' }}>Texto corto</option>
                    <option value="textarea" {{ old('type', $field['type'] ?? '') === 'textarea' ? 'selected' : '' }}>Texto largo</option>
                    <option value="number" {{ old('type', $field['type'] ?? '') === 'number' ? 'selected' : '' }}>Número</option>
                    <option value="select" {{ old('type', $field['type'] ?? '') === 'select' ? 'selected' : '' }}>Selección única (dropdown)</option>
                    <option value="multiselect" {{ old('type', $field['type'] ?? '') === 'multiselect' ? 'selected' : '' }}>Selección múltiple</option>
                    <option value="radio" {{ old('type', $field['type'] ?? '') === 'radio' ? 'selected' : '' }}>Radio buttons</option>
                    <option value="checkbox" {{ old('type', $field['type'] ?? '') === 'checkbox' ? 'selected' : '' }}>Checkbox (Sí/No)</option>
                    <option value="date" {{ old('type', $field['type'] ?? '') === 'date' ? 'selected' : '' }}>Fecha</option>
                    <option value="boolean" {{ old('type', $field['type'] ?? '') === 'boolean' ? 'selected' : '' }}>Booleano (Sí/No)</option>
                </select>
                @error('type')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <!-- Options section -->
            <template x-if="needsOptions">
                <div class="border-t border-gray-200 pt-6" x-transition>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">Opciones</h3>
                        <button type="button" @click="addOption()" class="text-sm text-primary-600 hover:text-primary-800 font-medium">+ Añadir opción</button>
                    </div>
                    
                    <div class="space-y-3" x-ref="optionsContainer">
                        <template x-for="(option, index) in options" :key="option.id">
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm text-gray-500">{{ index + 1 }}.</span>
                                <input type="hidden" :name="`options[${index}][id]`" :value="option.id">
                                <input type="text" :name="`options[${index}][label]`" :value="option.label" placeholder="Etiqueta (ej: Alto)" required class="input-field flex-1" @input="updateOption(index, 'label', $event.target.value)">
                                <input type="text" :name="`options[${index}][value]`" :value="option.value" placeholder="Valor (ej: alto)" required class="input-field w-32" @input="updateOption(index, 'value', $event.target.value)">
                                <input type="number" :name="`options[${index}][sort_order]`" :value="option.sort_order" min="0" class="input-field w-16" @input="updateOption(index, 'sort_order', parseInt($event.target.value) || 0)">
                                <label class="flex items-center gap-1">
                                    <input type="checkbox" :name="`options[${index}][is_active]`" :checked="option.is_active" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" @change="updateOption(index, 'is_active', $event.target.checked)">
                                    <span class="text-xs text-gray-600">Activo</span>
                                </label>
                                <button type="button" @click="removeOption(index)" class="text-red-500 hover:text-red-700" aria-label="Eliminar"><i class="fas fa-trash"></i></button>
                            </div>
                        </template>
                    </div>
                    
                    @error('options')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </template>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Requerido</label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_required" value="1" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ old('is_required', $field['is_required'] ?? false) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Este campo es obligatorio</span>
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Filtrbable en descubrimiento</label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_filterable" value="1" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ old('is_filterable', $field['is_filterable'] ?? false) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Aparece en filtros de búsqueda</span>
                    </label>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Activo</label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ old('is_active', $field['is_active'] ?? true) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Disponible para usuarios</span>
                    </label>
                </div>
            </div>
            
            <div>
                <label for="default_visibility" class="block text-sm font-medium text-gray-700 mb-1">Visibilidad por defecto <span class="text-red-500">*</span></label>
                <select name="default_visibility" id="default_visibility" required class="input-field">
                    <option value="PUBLIC" {{ old('default_visibility', $field['default_visibility'] ?? 'PUBLIC') === 'PUBLIC' ? 'selected' : '' }}>Público (todos)</option>
                    <option value="MATCH" {{ old('default_visibility', $field['default_visibility'] ?? '') === 'MATCH' ? 'selected' : '' }}>Solo Match</option>
                    <option value="FRIENDS" {{ old('default_visibility', $field['default_visibility'] ?? '') === 'FRIENDS' ? 'selected' : '' }}>Solo Amigos</option>
                    <option value="PRIVATE" {{ old('default_visibility', $field['default_visibility'] ?? '') === 'PRIVATE' ? 'selected' : '' }}>Privado (solo con permiso)</option>
                </select>
                @error('default_visibility')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Requiere verificación</label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" name="default_requires_verified" value="1" class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500" {{ old('default_requires_verified', $field['default_requires_verified'] ?? false) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700">Solo usuarios verificados pueden ver</span>
                    </label>
                </div>
                
                <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700 mb-1">Orden</label>
                    <input type="number" name="sort_order" id="sort_order" min="0" value="{{ old('sort_order', $field['sort_order'] ?? 0) }}" class="input-field">
                </div>
            </div>
            
            <div>
                <label for="validation_rules" class="block text-sm font-medium text-gray-700 mb-1">Reglas de validación (JSON)</label>
                <textarea name="validation_rules" id="validation_rules" rows="4" class="input-field font-mono text-sm" placeholder='{"max": 100, "min": 2, "regex": "^[a-zA-Z ]+$"}'>{{ old('validation_rules', $field['validation_rules'] ?? '') }}</textarea>
                <p class="mt-1 text-xs text-gray-500">Ejemplos: text/textarea: {"max": 500, "min": 10}, number: {"min": 18, "max": 100, "integer": true}, date: {"before": "today", "after": "1900-01-01"}</p>
                @error('validation_rules')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            
            <div class="flex gap-3 justify-end pt-4 border-t border-gray-200">
                <a href="{{ route('admin.profile-fields.index') }}" class="bg-gray-100 text-gray-700 py-2 px-4 rounded-lg font-medium hover:bg-gray-200 transition">Cancelar</a>
                <button type="submit" class="btn-primary">Guardar cambios</button>
            </div>
        </form>
    </div>
</x-admin.layouts.app>

@push('scripts')
<script>
function profileFieldForm() {
    return {
        selectedType: '{{ old("type", $field["type"] ?? "") }}',
        options: [],
        optionCounter: 0,
        
        init() {
            // Parse existing options from field data
            const existingOptions = @json($field['options'] ?? []);
            if (existingOptions.length > 0) {
                this.options = existingOptions.map(opt => ({
                    id: opt.id,
                    label: opt.label,
                    value: opt.value,
                    sort_order: opt.sort_order ?? 0,
                    is_active: opt.is_active ?? true
                }));
                this.optionCounter = Math.max(...this.options.map(o => o.id), 0);
            }
        },
        
        get needsOptions() {
            return ['select', 'multiselect', 'radio'].includes(this.selectedType);
        },
        
        onTypeChange() {
            if (!this.needsOptions) {
                this.options = [];
            } else if (this.options.length === 0) {
                this.addOption();
            }
        },
        
        addOption() {
            this.options.push({
                id: ++this.optionCounter,
                label: '',
                value: '',
                sort_order: this.options.length,
                is_active: true
            });
        },
        
        removeOption(index) {
            this.options.splice(index, 1);
            this.options.forEach((opt, i) => opt.sort_order = i);
        },
        
        updateOption(index, field, value) {
            this.options[index][field] = value;
        }
    }
}
</script>
@endpush