<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppConfigSeeder extends Seeder
{
    public function run(): void
    {
        $configs = [
            'max_photos_per_user' => ['value' => 32, 'description' => 'Maximum number of active photos per user'],
            'toke_ttl_hours' => ['value' => 48, 'description' => 'Time-to-live for tokes in hours'],
            'match_ttl_days' => ['value' => 7, 'description' => 'Time-to-live for matches in days'],
            'post_ttl_hours' => ['value' => 24, 'description' => 'Time-to-live for posts in hours'],
            'online_threshold_minutes' => ['value' => 2, 'description' => 'Minutes after last_seen to consider user online'],
            'location_default_precision_meters' => ['value' => 1000, 'description' => 'Default location precision in meters'],
        ];

        foreach ($configs as $key => $config) {
            DB::table('app_configs')->updateOrInsert(
                ['key' => $key],
                array_merge($config, ['updated_at' => now()])
            );
        }
    }
}
