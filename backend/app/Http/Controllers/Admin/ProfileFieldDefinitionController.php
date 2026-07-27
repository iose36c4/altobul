<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProfileFieldDefinitionRequest;
use App\Http\Requests\Admin\UpdateProfileFieldDefinitionRequest;
use App\Models\ProfileFieldDefinition;
use App\Models\ProfileFieldOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileFieldDefinitionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $fields = ProfileFieldDefinition::with('options')
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'fields' => $fields->map(fn ($f) => [
                'id' => $f->id,
                'slug' => $f->slug,
                'label' => $f->label,
                'description' => $f->description,
                'type' => $f->type,
                'default_visibility' => $f->default_visibility,
                'default_requires_verified' => $f->default_requires_verified,
                'validation_rules' => $f->validation_rules,
                'sort_order' => $f->sort_order,
                'is_active' => $f->is_active,
                'options' => $f->options->map(fn ($o) => [
                    'id' => $o->id,
                    'label' => $o->label,
                    'value' => $o->value,
                    'sort_order' => $o->sort_order,
                    'is_active' => $o->is_active,
                ]),
            ]),
        ]);
    }

    public function store(StoreProfileFieldDefinitionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $options = $data['options'] ?? [];
        unset($data['options']);

        $field = ProfileFieldDefinition::create($data);

        foreach ($options as $index => $option) {
            ProfileFieldOption::create([
                'field_id' => $field->id,
                'label' => $option['label'],
                'value' => $option['value'],
                'sort_order' => $option['sort_order'] ?? $index,
                'is_active' => $option['is_active'] ?? true,
            ]);
        }

        $field->load('options');

        return response()->json([
            'field' => [
                'id' => $field->id,
                'slug' => $field->slug,
                'label' => $field->label,
                'description' => $field->description,
                'type' => $field->type,
                'default_visibility' => $field->default_visibility,
                'default_requires_verified' => $field->default_requires_verified,
                'validation_rules' => $field->validation_rules,
                'sort_order' => $field->sort_order,
                'is_active' => $field->is_active,
                'options' => $field->options->map(fn ($o) => [
                    'id' => $o->id,
                    'label' => $o->label,
                    'value' => $o->value,
                    'sort_order' => $o->sort_order,
                    'is_active' => $o->is_active,
                ]),
            ],
        ], 201);
    }

    public function show(ProfileFieldDefinition $field): JsonResponse
    {
        $field->load('options');

        return response()->json([
            'field' => [
                'id' => $field->id,
                'slug' => $field->slug,
                'label' => $field->label,
                'description' => $field->description,
                'type' => $field->type,
                'default_visibility' => $field->default_visibility,
                'default_requires_verified' => $field->default_requires_verified,
                'validation_rules' => $field->validation_rules,
                'sort_order' => $field->sort_order,
                'is_active' => $field->is_active,
                'options' => $field->options->map(fn ($o) => [
                    'id' => $o->id,
                    'label' => $o->label,
                    'value' => $o->value,
                    'sort_order' => $o->sort_order,
                    'is_active' => $o->is_active,
                ]),
            ],
        ]);
    }

    public function update(UpdateProfileFieldDefinitionRequest $request, ProfileFieldDefinition $field): JsonResponse
    {
        $data = $request->validated();
        $options = $data['options'] ?? null;
        unset($data['options']);

        $field->update($data);

        if ($options !== null) {
            $existingIds = $field->options->pluck('id')->toArray();
            $incomingIds = [];

            foreach ($options as $index => $option) {
                $optionId = $option['id'] ?? null;

                if ($optionId && in_array($optionId, $existingIds)) {
                    ProfileFieldOption::where('id', $optionId)->update([
                        'label' => $option['label'],
                        'value' => $option['value'],
                        'sort_order' => $option['sort_order'] ?? $index,
                        'is_active' => $option['is_active'] ?? true,
                    ]);
                    $incomingIds[] = $optionId;
                } else {
                    $newOption = ProfileFieldOption::create([
                        'field_id' => $field->id,
                        'label' => $option['label'],
                        'value' => $option['value'],
                        'sort_order' => $option['sort_order'] ?? $index,
                        'is_active' => $option['is_active'] ?? true,
                    ]);
                    $incomingIds[] = $newOption->id;
                }
            }

            ProfileFieldOption::where('field_id', $field->id)
                ->whereNotIn('id', $incomingIds)
                ->delete();
        }

        $field->load('options');

        return response()->json([
            'field' => [
                'id' => $field->id,
                'slug' => $field->slug,
                'label' => $field->label,
                'description' => $field->description,
                'type' => $field->type,
                'default_visibility' => $field->default_visibility,
                'default_requires_verified' => $field->default_requires_verified,
                'validation_rules' => $field->validation_rules,
                'sort_order' => $field->sort_order,
                'is_active' => $field->is_active,
                'options' => $field->options->map(fn ($o) => [
                    'id' => $o->id,
                    'label' => $o->label,
                    'value' => $o->value,
                    'sort_order' => $o->sort_order,
                    'is_active' => $o->is_active,
                ]),
            ],
        ]);
    }

    public function destroy(ProfileFieldDefinition $field): JsonResponse
    {
        $field->delete();

        return response()->json(['message' => 'Field deleted']);
    }

    public function reorder(Request $request): JsonResponse
    {
        $order = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['string', 'uuid'],
        ]);

        foreach ($order['ids'] as $index => $id) {
            ProfileFieldDefinition::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->json(['message' => 'Order updated']);
    }
}
