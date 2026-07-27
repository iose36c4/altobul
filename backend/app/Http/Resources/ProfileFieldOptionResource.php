<?php

namespace App\Http\Resources;

use App\Models\ProfileFieldOption;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileFieldOptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ProfileFieldOption $option */
        $option = $this->resource;

        return [
            'id' => $option->id,
            'label' => $option->label,
            'value' => $option->value,
            'sort_order' => $option->sort_order,
            'is_active' => $option->is_active,
        ];
    }
}
