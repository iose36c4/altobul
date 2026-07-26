<?php

namespace App\Services\Config;

use App\Models\AppConfig;
use Illuminate\Support\Facades\Cache;

class ConfigService
{
    public function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("app_config.{$key}", 300, fn() => 
            AppConfig::where('key', $key)->value('value') ?? $default
        );
    }
    
    public function getInt(string $key, int $default = 0): int
    {
        return (int) $this->get($key, $default);
    }
    
    public function getBool(string $key, bool $default = false): bool
    {
        return (bool) $this->get($key, $default);
    }
    
    public function set(string $key, mixed $value, string $description = null, ?string $updatedBy = null): void
    {
        AppConfig::updateOrInsert(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'updated_by' => $updatedBy,
                'updated_at' => now(),
            ]
        );
        Cache::forget("app_config.{$key}");
    }
}