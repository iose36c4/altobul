<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DO \$\$ BEGIN CREATE TYPE api_key_type AS ENUM ('CLIENT', 'ADMIN', 'MOBILE', 'INTEGRATION'); EXCEPTION WHEN duplicate_object THEN NULL; END \$\$;");

        Schema::create('api_keys', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->string('name');
            $table->enum('type', ['CLIENT', 'ADMIN', 'MOBILE', 'INTEGRATION']);
            $table->string('key_hash')->unique();
            $table->string('key_prefix', 8);
            $table->timestampTz('last_used_at')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('revoked_at')->nullable();
            $table->foreignUuid('created_by')->constrained('users');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->index(['type', 'revoked_at'], 'api_keys_type_revoked_idx');
            $table->index(['created_by'], 'api_keys_created_by_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_keys');
        DB::statement('DROP TYPE IF EXISTS api_key_type CASCADE');
    }
};
