<?php

namespace App\Services\Profile;

use App\Models\Profile;
use App\Models\ProfileFieldDefinition;
use App\Models\ProfileFieldOption;
use App\Models\ProfileFieldValue;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileFieldService
{
    public function setValue(Profile $profile, ProfileFieldDefinition $field, mixed $input): ProfileFieldValue
    {
        $validated = $this->validateAndNormalize($input, $field);

        return DB::transaction(function () use ($profile, $field, $validated, $input) {
            $value = ProfileFieldValue::updateOrCreate(
                ['profile_id' => $profile->user_id, 'field_id' => $field->id],
                array_merge($validated, ['updated_at' => now()])
            );

            // Handle options for SELECT, MULTISELECT, RADIO
            if (in_array($field->type, ['SELECT', 'MULTISELECT', 'RADIO']) && $input) {
                $optionIds = $this->getOptionIdsForInput($field, $input);

                DB::table('profile_field_value_options')->where('field_value_id', $value->id)->delete();

                foreach ($optionIds as $optionId) {
                    DB::table('profile_field_value_options')->insert([
                        'field_value_id' => $value->id,
                        'option_id' => $optionId,
                    ]);
                }
            }

            return $value->fresh(['field', 'selectedOptions']);
        });
    }

    public function validateAndNormalize(mixed $input, ProfileFieldDefinition $field): array
    {
        $baseRules = $this->getBaseRulesForType($field->type, $field->id);
        $customRules = $field->validation_rules ?? [];

        $validator = Validator::make(['value' => $input], array_merge($baseRules, $customRules));
        $validator->validate();

        return $this->normalizeValue($input, $field);
    }

    private function getBaseRulesForType(string $type, string $fieldId): array
    {
        return match ($type) {
            'TEXT' => ['string', 'max:500'],
            'TEXTAREA' => ['string', 'max:2000'],
            'NUMBER' => ['numeric'],
            'DATE' => ['date', 'before_or_equal:today'],
            'BOOLEAN' => ['boolean'],
            'SELECT', 'RADIO' => [
                'string',
                Rule::exists('profile_field_options', 'value')
                    ->where('field_id', $fieldId),
            ],
            'MULTISELECT' => [
                'array', 'min:1',
                Rule::exists('profile_field_options', 'value')
                    ->where('field_id', $fieldId),
            ],
            default => [],
        };
    }

    private function normalizeValue(mixed $input, ProfileFieldDefinition $field): array
    {
        $data = ['value_json' => null];

        switch ($field->type) {
            case 'TEXT':
            case 'TEXTAREA':
                $data['value_text'] = (string) $input;
                $data['value_json'] = json_encode(['text' => $input]);
                break;
            case 'NUMBER':
                $data['value_number'] = (float) $input;
                $data['value_json'] = json_encode(['number' => $input]);
                break;
            case 'BOOLEAN':
                $data['value_boolean'] = (bool) $input;
                $data['value_json'] = json_encode(['boolean' => $input]);
                break;
            case 'DATE':
                $data['value_date'] = Carbon::parse($input)->format('Y-m-d');
                $data['value_json'] = json_encode(['date' => $input]);
                break;
            case 'SELECT':
            case 'RADIO':
                $option = ProfileFieldOption::where('field_id', $field->id)
                    ->where('value', $input)->first();
                $data['value_json'] = json_encode(['option_id' => $option?->id]);
                break;
            case 'MULTISELECT':
                $options = ProfileFieldOption::where('field_id', $field->id)
                    ->whereIn('value', (array) $input)->get();
                $data['value_json'] = json_encode(['option_ids' => $options->pluck('id')->toArray()]);
                break;
        }

        return $data;
    }

    private function getOptionIdsForInput(ProfileFieldDefinition $field, mixed $input): array
    {
        $values = is_array($input) ? $input : [$input];

        return ProfileFieldOption::where('field_id', $field->id)
            ->whereIn('value', $values)
            ->pluck('id')->toArray();
    }

    public function getValue(Profile $profile, string $fieldSlug): ?ProfileFieldValue
    {
        return ProfileFieldValue::where('profile_id', $profile->user_id)
            ->whereHas('field', fn ($q) => $q->where('slug', $fieldSlug))
            ->first();
    }

    public function deleteValue(Profile $profile, string $fieldSlug): bool
    {
        return ProfileFieldValue::where('profile_id', $profile->user_id)
            ->whereHas('field', fn ($q) => $q->where('slug', $fieldSlug))
            ->delete() > 0;
    }
}
