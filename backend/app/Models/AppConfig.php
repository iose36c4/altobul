<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends Model
{
    protected $table = 'app_configs';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'description',
        'updated_by',
    ];

    protected $casts = [
        'value' => 'array',
        'updated_at' => 'datetime',
    ];

    public static function getConfig(): self
    {
        return self::firstOrCreate(
            ['key' => 'system'],
            [
                'value' => [
                    'installed' => false,
                    'installed_at' => null,
                    'first_admin_id' => null,
                ],
                'description' => 'System configuration',
            ]
        );
    }

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->value;

        if (! is_array($value)) {
            return $default;
        }

        return $value[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $currentValue = $this->value ?? [];
        $currentValue[$key] = $value;
        $this->value = $currentValue;
        $this->save();
    }
}
