<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_fields', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 60)->unique();
            $table->string('label', 100);
            $table->text('description')->nullable();
            $table->enum('type', ['TEXT', 'TEXTAREA', 'NUMBER', 'DATE', 'BOOLEAN', 'SELECT', 'MULTISELECT', 'RADIO']);
            $table->json('validation_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->enum('default_visibility', ['PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE'])->default('PUBLIC');
            $table->boolean('default_requires_verified')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::create('profile_field_options', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('field_id')->constrained('profile_fields')->cascadeOnDelete();
            $table->string('label', 200);
            $table->string('value', 100); // canonical value for API
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unique(['field_id', 'value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('profile_field_options');
        Schema::dropIfExists('profile_fields');
    }
};
