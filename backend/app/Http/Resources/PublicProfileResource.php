<?php

namespace App\Http\Resources;

use App\Models\Profile;
use App\Models\ProfileFieldValue;
use App\Models\User;
use App\Services\Authorization\AuthorizationService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PublicProfileResource extends JsonResource
{
    private AuthorizationService $authorization;

    private ?User $viewer;

    public function __construct(
        Profile $resource,
        AuthorizationService $authorization,
        ?User $viewer = null
    ) {
        parent::__construct($resource);
        $this->authorization = $authorization;
        $this->viewer = $viewer;
    }

    public function toArray(Request $request): array
    {
        /** @var Profile $profile */
        $profile = $this->resource;
        $viewer = $this->viewer ?? $request->user();
        $owner = $profile->user;

        $result = [
            'user_id' => $profile->user_id,
        ];

        $result['title'] = $this->getFixedFieldValue($viewer, $owner, 'title', $profile->title, $profile->title_visibility, $profile->title_requires_verified);
        $result['description'] = $this->getFixedFieldValue($viewer, $owner, 'description', $profile->description, $profile->description_visibility, $profile->description_requires_verified);
        $result['birth_date'] = $this->getFixedFieldValue($viewer, $owner, 'birth_date', $profile->birth_date?->format('Y-m-d'), $profile->birth_date_visibility, $profile->birth_date_requires_verified);

        $result['fields'] = [];
        foreach ($profile->fieldValues as $fieldValue) {
            $fieldValueData = $this->getDynamicFieldValue($viewer, $owner, $fieldValue);
            if ($fieldValueData !== null) {
                $result['fields'][$fieldValueData['slug']] = $fieldValueData['value'];
            }
        }

        return $result;
    }

    private function getFixedFieldValue(
        ?User $viewer,
        User $owner,
        string $fieldName,
        mixed $value,
        string $visibility,
        bool $requiresVerified
    ): mixed {
        if (! $viewer || $viewer->id !== $owner->id) {
            $result = $this->authorization->canViewProfileFixedField(
                $viewer ?? $owner,
                $owner,
                $fieldName,
                $visibility,
                $requiresVerified
            );

            if (! $result->allowed) {
                return null;
            }
        }

        return $value;
    }

    private function getDynamicFieldValue(
        ?User $viewer,
        User $owner,
        ProfileFieldValue $fieldValue
    ): ?array {
        if (! $viewer || $viewer->id !== $owner->id) {
            $result = $this->authorization->canViewProfileField(
                $viewer ?? $owner,
                $owner,
                $fieldValue->field->slug
            );

            if (! $result->allowed) {
                return null;
            }
        }

        return [
            'slug' => $fieldValue->field->slug,
            'value' => $fieldValue->getDisplayValue(),
        ];
    }
}
