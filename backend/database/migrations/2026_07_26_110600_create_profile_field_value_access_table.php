<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_field_value_access', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('field_value_id')->constrained('profile_field_values')->cascadeOnDelete();
            $table->foreignUuid('grantee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('granted_by')->constrained('users');
            $table->timestampTz('granted_at')->useCurrent();
            $table->unique(['field_value_id', 'grantee_id']);
        });

        DB::statement('CREATE INDEX profile_field_value_access_grantee ON profile_field_value_access (grantee_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS profile_field_value_access_grantee');
        Schema::dropIfExists('profile_field_value_access');
    }
};