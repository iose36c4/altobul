<?php

namespace Database\Seeders;

use App\Models\GeoPolygon;
use App\Models\GeoZone;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GlobalZoneSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user for zone
        $adminId = (string) Str::uuid();
        DB::table('users')->insert([
            'id' => $adminId,
            'email' => 'geo-admin@example.com',
            'password_hash' => bcrypt('password'),
            'email_verified_at' => now(),
            'verification_status' => 'not_verified',
            'status' => 'active',
            'role' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create global zone
        $zoneId = (string) Str::uuid();
        GeoZone::create([
            'id' => $zoneId,
            'name' => 'Global Test Zone',
            'description' => 'Global zone covering most of the world for testing',
            'is_active' => true,
            'created_by' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Polygon covering most of the world (avoiding antipodal edges)
        GeoPolygon::create([
            'id' => (string) Str::uuid(),
            'zone_id' => $zoneId,
            'name' => 'World Polygon',
            'geom' => DB::raw("ST_MakePolygon(ST_GeomFromText('LINESTRING(-179 -89, 179 -89, 179 89, -179 89, -179 -89)', 4326))::geography"),
            'created_at' => now(),
        ]);
    }
}