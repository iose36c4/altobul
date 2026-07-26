<?php

namespace App\Http\Resources;

use App\Models\ProfileFieldDefinition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileFieldDefinitionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ProfileFieldDefinition $field */
        $field = $this->resource;
        
        return [
            'id' => $field->id,
            'slug' => $field->slug,
            'label' => $field->label,
            'description' => $field->description,
            'type' => $field->type,
            'is_required' => $field->is_required,
            'is_filterable' => $field->is_filterable,
            'is_active' => $field->is_active,
            'default_visibility' => $field->default_visibility,
            'default_requires_verified' => $field->default_requires_verified,
            'sort_order' => $field->sort_order,
            'options' => ProfileFieldOptionResource::collection($field->whenLoaded('options')),
            'validation_rules' => $field->validation_rules,
        ];
    }
}