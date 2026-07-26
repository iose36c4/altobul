<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->uuid('user_id')->primary();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Fixed profile fields with individual privacy
            $table->string('title', 120)->nullable();
            $table->enum('title_visibility', ['PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE'])->default('PUBLIC');
            $table->boolean('title_requires_verified')->default(false);

            $table->text('description')->nullable();
            $table->enum('description_visibility', ['PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE'])->default('PUBLIC');
            $table->boolean('description_requires_verified')->default(false);

            $table->date('birth_date')->nullable();
            $table->enum('birth_date_visibility', ['PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE'])->default('PRIVATE');
            $table->boolean('birth_date_requires_verified')->default(false);

            // Profile-level visibility (applies to profile as a whole)
            $table->enum('profile_visibility', ['PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE'])->default('PUBLIC');
            $table->boolean('profile_requires_verified')->default(false);

            // Location (PostGIS) - internal use only, never exposed directly
            // Added via raw SQL below
            $table->unsignedInteger('location_precision_meters')->default(1000);
            $table->timestampTz('location_updated_at')->useCurrent();

            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });

        // Add PostGIS point column
        DB::statement('ALTER TABLE profiles ADD COLUMN location GEOGRAPHY(POINT, 4326) NOT NULL');
        
        // Spatial index for location queries
        DB::statement('CREATE INDEX profiles_location_gist ON profiles USING GIST (location)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS profiles_location_gist');
        Schema::dropIfExists('profiles');
    }
};