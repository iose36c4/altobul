<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photo_access', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('photo_id')->constrained('photos')->cascadeOnDelete();
            $table->foreignUuid('grantee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('granted_by')->constrained('users');
            $table->timestampTz('granted_at')->useCurrent();
            $table->unique(['photo_id', 'grantee_id']);
        });

        DB::statement('CREATE INDEX photo_access_grantee ON photo_access (grantee_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS photo_access_grantee');
        Schema::dropIfExists('photo_access');
    }
};