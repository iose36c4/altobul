<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geo_zones', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });

        Schema::create('geo_polygons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('zone_id')->constrained('geo_zones')->cascadeOnDelete();
            $table->timestampTz('created_at')->useCurrent();
        });

        // Add PostGIS polygon column via raw SQL
        DB::statement('ALTER TABLE geo_polygons ADD COLUMN geom GEOGRAPHY(POLYGON, 4326) NOT NULL');
        DB::statement('ALTER TABLE geo_polygons ADD CONSTRAINT valid_polygon CHECK (ST_IsValid(geom::geometry))');

        // Spatial index for PostGIS queries
        DB::statement('CREATE INDEX geo_polygons_geom_gist ON geo_polygons USING GIST (geom)');
        DB::statement('CREATE INDEX geo_polygons_zone_id ON geo_polygons (zone_id)');
        DB::statement('CREATE INDEX geo_zones_active ON geo_zones (is_active) WHERE is_active');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS geo_polygons_geom_gist');
        DB::statement('DROP INDEX IF EXISTS geo_polygons_zone_id');
        DB::statement('DROP INDEX IF EXISTS geo_zones_active');
        Schema::dropIfExists('geo_polygons');
        Schema::dropIfExists('geo_zones');
    }
};
