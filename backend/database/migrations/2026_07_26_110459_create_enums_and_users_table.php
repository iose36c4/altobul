<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Create ENUM types
        DB::statement("CREATE TYPE user_role AS ENUM ('user', 'admin')");
        DB::statement("CREATE TYPE user_status AS ENUM ('active', 'suspended', 'banned', 'deleted')");
        DB::statement("CREATE TYPE verification_status AS ENUM ('not_verified', 'pending', 'verified', 'rejected')");
        DB::statement("CREATE TYPE visibility_level AS ENUM ('PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE')");
        DB::statement("CREATE TYPE toke_status AS ENUM ('ACTIVE', 'EXPIRED', 'CONSUMED', 'CANCELLED')");
        DB::statement("CREATE TYPE match_status AS ENUM ('ACTIVE', 'EXPIRED', 'ENDED_BY_BLOCK')");
        DB::statement("CREATE TYPE friendship_status AS ENUM ('ACTIVE', 'ENDED')");
        DB::statement("CREATE TYPE friendship_request_status AS ENUM ('PENDING', 'ACCEPTED', 'REJECTED', 'EXPIRED')");
        DB::statement("CREATE TYPE conversation_status AS ENUM ('ACTIVE', 'BLOCKED', 'ENDED')");
        DB::statement("CREATE TYPE post_status AS ENUM ('ACTIVE', 'EXPIRED', 'DELETED')");
        DB::statement("CREATE TYPE photo_status AS ENUM ('ACTIVE', 'PROCESSING', 'DELETED')");
        DB::statement("CREATE TYPE field_type AS ENUM ('TEXT', 'TEXTAREA', 'NUMBER', 'DATE', 'BOOLEAN', 'SELECT', 'MULTISELECT', 'RADIO')");
        DB::statement("CREATE TYPE discovery_operator AS ENUM ('eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'nin', 'between', 'is_null', 'is_not_null')");

        // Enable PostGIS extension
        DB::statement('CREATE EXTENSION IF NOT EXISTS postgis');
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');

        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('email')->unique();
            $table->string('password_hash');
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->enum('status', ['active', 'suspended', 'banned', 'deleted'])->default('active');
            $table->enum('verification_status', ['not_verified', 'pending', 'verified', 'rejected'])->default('not_verified');
            $table->timestampTz('verified_at')->nullable();
            $table->timestampTz('email_verified_at')->nullable();
            $table->timestampTz('last_seen_at')->useCurrent();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();

            $table->index(['status'], 'users_status_idx')->where('status', '!=', 'deleted');
            $table->index(['verification_status'], 'users_verification_idx')->whereIn('verification_status', ['verified', 'pending']);
            $table->index(['last_seen_at'], 'users_last_seen_idx')->where('status', 'active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
        DB::statement('DROP TYPE IF EXISTS user_role');
        DB::statement('DROP TYPE IF EXISTS user_status');
        DB::statement('DROP TYPE IF EXISTS verification_status');
        DB::statement('DROP TYPE IF EXISTS visibility_level');
        DB::statement('DROP TYPE IF EXISTS toke_status');
        DB::statement('DROP TYPE IF EXISTS match_status');
        DB::statement('DROP TYPE IF EXISTS friendship_status');
        DB::statement('DROP TYPE IF EXISTS friendship_request_status');
        DB::statement('DROP TYPE IF EXISTS conversation_status');
        DB::statement('DROP TYPE IF EXISTS post_status');
        DB::statement('DROP TYPE IF EXISTS photo_status');
        DB::statement('DROP TYPE IF EXISTS field_type');
        DB::statement('DROP TYPE IF EXISTS discovery_operator');
    }
};