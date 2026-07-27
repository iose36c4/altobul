<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProfileFieldSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            [
                'slug' => 'height',
                'label' => 'Altura',
                'description' => 'Altura en centímetros',
                'type' => 'NUMBER',
                'validation_rules' => ['min' => 100, 'max' => 250],
                'is_active' => true,
                'is_required' => false,
                'is_filterable' => true,
                'default_visibility' => 'PUBLIC',
                'default_requires_verified' => false,
                'sort_order' => 1,
            ],
            [
                'slug' => 'eye_color',
                'label' => 'Color de ojos',
                'description' => 'Color de ojos',
                'type' => 'SELECT',
                'validation_rules' => [],
                'is_active' => true,
                'is_required' => false,
                'is_filterable' => true,
                'default_visibility' => 'PUBLIC',
                'default_requires_verified' => false,
                'sort_order' => 2,
            ],
            [
                'slug' => 'body_type',
                'label' => 'Tipo corporal',
                'description' => 'Tipo de complexión física',
                'type' => 'SELECT',
                'validation_rules' => [],
                'is_active' => true,
                'is_required' => false,
                'is_filterable' => true,
                'default_visibility' => 'PUBLIC',
                'default_requires_verified' => false,
                'sort_order' => 3,
            ],
            [
                'slug' => 'interests',
                'label' => 'Intereses',
                'description' => 'Intereses y aficiones',
                'type' => 'MULTISELECT',
                'validation_rules' => [],
                'is_active' => true,
                'is_required' => false,
                'is_filterable' => true,
                'default_visibility' => 'PUBLIC',
                'default_requires_verified' => false,
                'sort_order' => 4,
            ],
            [
                'slug' => 'preferences',
                'label' => 'Preferencias',
                'description' => 'Preferencias de búsqueda',
                'type' => 'MULTISELECT',
                'validation_rules' => [],
                'is_active' => true,
                'is_required' => false,
                'is_filterable' => true,
                'default_visibility' => 'PRIVATE',
                'default_requires_verified' => false,
                'sort_order' => 5,
            ],
            [
                'slug' => 'bio',
                'label' => 'Bio',
                'description' => 'Descripción personal',
                'type' => 'TEXTAREA',
                'validation_rules' => ['max' => 500],
                'is_active' => true,
                'is_required' => false,
                'is_filterable' => false,
                'default_visibility' => 'PUBLIC',
                'default_requires_verified' => false,
                'sort_order' => 6,
            ],
        ];

        // Upsert fields and collect their IDs
        $fieldIds = [];
        foreach ($fields as $field) {
            DB::table('profile_fields')->upsert(
                array_merge($field, [
                    'id' => Str::uuid(),
                    'validation_rules' => json_encode($field['validation_rules']),
                    'updated_at' => now(),
                ]),
                ['slug'],
                ['label', 'description', 'type', 'validation_rules', 'is_active', 'is_required', 'is_filterable', 'default_visibility', 'default_requires_verified', 'sort_order', 'updated_at']
            );
        }

        // Get actual field IDs from database
        $fieldRecords = DB::table('profile_fields')->whereIn('slug', array_column($fields, 'slug'))->get(['id', 'slug']);
        foreach ($fieldRecords as $record) {
            $fieldIds[$record->slug] = $record->id;
        }

        // Eye color options
        $eyeColors = [
            ['field' => 'eye_color', 'label' => 'Marrón', 'value' => 'brown', 'sort_order' => 1],
            ['field' => 'eye_color', 'label' => 'Verde', 'value' => 'green', 'sort_order' => 2],
            ['field' => 'eye_color', 'label' => 'Azul', 'value' => 'blue', 'sort_order' => 3],
            ['field' => 'eye_color', 'label' => 'Avellana', 'value' => 'hazel', 'sort_order' => 4],
            ['field' => 'eye_color', 'label' => 'Gris', 'value' => 'gray', 'sort_order' => 5],
            ['field' => 'eye_color', 'label' => 'Otro', 'value' => 'other', 'sort_order' => 6],
        ];
        $this->insertOptions($fieldIds, $eyeColors);

        // Body type options
        $bodyTypes = [
            ['field' => 'body_type', 'label' => 'Atlético', 'value' => 'athletic', 'sort_order' => 1],
            ['field' => 'body_type', 'label' => 'Promedio', 'value' => 'average', 'sort_order' => 2],
            ['field' => 'body_type', 'label' => 'Delgado', 'value' => 'slim', 'sort_order' => 3],
            ['field' => 'body_type', 'label' => 'Musculoso', 'value' => 'muscular', 'sort_order' => 4],
            ['field' => 'body_type', 'label' => 'Voluptuoso', 'value' => 'curvy', 'sort_order' => 5],
            ['field' => 'body_type', 'label' => 'Otro', 'value' => 'other', 'sort_order' => 6],
        ];
        $this->insertOptions($fieldIds, $bodyTypes);

        // Interests options
        $interests = [
            ['field' => 'interests', 'label' => 'Música', 'value' => 'music', 'sort_order' => 1],
            ['field' => 'interests', 'label' => 'Cine', 'value' => 'movies', 'sort_order' => 2],
            ['field' => 'interests', 'label' => 'Viajes', 'value' => 'travel', 'sort_order' => 3],
            ['field' => 'interests', 'label' => 'Deportes', 'value' => 'sports', 'sort_order' => 4],
            ['field' => 'interests', 'label' => 'Lectura', 'value' => 'reading', 'sort_order' => 5],
            ['field' => 'interests', 'label' => 'Cocina', 'value' => 'cooking', 'sort_order' => 6],
            ['field' => 'interests', 'label' => 'Videojuegos', 'value' => 'gaming', 'sort_order' => 7],
            ['field' => 'interests', 'label' => 'Arte', 'value' => 'art', 'sort_order' => 8],
        ];
        $this->insertOptions($fieldIds, $interests);

        // Preferences options
        $preferences = [
            ['field' => 'preferences', 'label' => 'Música', 'value' => 'music', 'sort_order' => 1],
            ['field' => 'preferences', 'label' => 'Cine', 'value' => 'movies', 'sort_order' => 2],
            ['field' => 'preferences', 'label' => 'Viajes', 'value' => 'travel', 'sort_order' => 3],
            ['field' => 'preferences', 'label' => 'Deportes', 'value' => 'sports', 'sort_order' => 4],
            ['field' => 'preferences', 'label' => 'Lectura', 'value' => 'reading', 'sort_order' => 5],
        ];
        $this->insertOptions($fieldIds, $preferences);
    }

    private function insertOptions(array $fieldIds, array $options): void
    {
        $rows = [];
        foreach ($options as $opt) {
            $fieldId = $fieldIds[$opt['field']];
            if (! $fieldId) {
                continue;
            }
            $rows[] = [
                'id' => Str::uuid(),
                'field_id' => $fieldId,
                'label' => $opt['label'],
                'value' => $opt['value'],
                'sort_order' => $opt['sort_order'],
                'is_active' => true,
            ];
        }

        if (! empty($rows)) {
            DB::table('profile_field_options')->upsert(
                $rows,
                ['field_id', 'value'],
                ['label', 'sort_order', 'is_active']
            );
        }
    }
}
