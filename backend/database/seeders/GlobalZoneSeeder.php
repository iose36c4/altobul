<?php

namespace Database\Seeders;

use App\Models\GeoPolygon;
use App\Models\GeoZone;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GlobalZoneSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user for zone (idempotent)
        $admin = User::firstOrCreate(
            ['email' => 'geo-admin@example.com'],
            [
                'id' => (string) Str::uuid(),
                'password_hash' => Hash::make('password'),
                'email_verified_at' => now(),
                'verification_status' => 'not_verified',
                'status' => 'active',
                'role' => 'admin',
            ]
        );

        // Create global zone (idempotent)
        $zone = GeoZone::firstOrCreate(
            ['name' => 'Global Test Zone'],
            [
                'id' => (string) Str::uuid(),
                'description' => 'Global zone covering most of the world for testing',
                'is_active' => true,
                'created_by' => $admin->id,
            ]
        );

        // Polygon covering most of the world (avoiding antipodal edges)
        GeoPolygon::firstOrCreate(
            ['zone_id' => $zone->id, 'name' => 'World Polygon'],
            [
                'id' => (string) Str::uuid(),
                'geom' => DB::raw("ST_MakePolygon(ST_GeomFromText('LINESTRING(-179 -89, 179 -89, 179 89, -179 89, -179 -89)', 4326))::geography"),
            ]
        );
    }
}
