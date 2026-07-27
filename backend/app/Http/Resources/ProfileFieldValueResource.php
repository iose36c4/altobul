<?php

namespace App\Http\Resources;

use App\Models\ProfileFieldValue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileFieldValueResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ProfileFieldValue $value */
        $value = $this->resource;

        return [
            'id' => $value->id,
            'field_id' => $value->field_id,
            'field_slug' => $value->field?->slug,
            'field_label' => $value->field?->label,
            'field_type' => $value->field?->type,
            'value' => $value->getValue(),
            'visibility' => $value->effective_visibility,
            'requires_verified' => $value->effective_requires_verified,
            'can_grant' => $value->effective_visibility === 'PRIVATE',
        ];
    }
}
