<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('profile_field_values', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('profile_id')->constrained('profiles', 'user_id')->cascadeOnDelete();
            $table->foreignUuid('field_id')->constrained('profile_fields')->cascadeOnDelete();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 10, 2)->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->date('value_date')->nullable();
            $table->enum('visibility_override', ['PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE'])->nullable();
            $table->boolean('requires_verified_override')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->unique(['profile_id', 'field_id']);
        });

        Schema::create('profile_field_value_options', function (Blueprint $table) {
            $table->foreignUuid('field_value_id')->constrained('profile_field_values', 'id')->cascadeOnDelete();
            $table->foreignUuid('option_id')->constrained('profile_field_options')->cascadeOnDelete();
            $table->primary(['field_value_id', 'option_id']);
        });

        // Indexes for filtering
        DB::statement('CREATE INDEX profile_field_values_profile ON profile_field_values (profile_id)');
        DB::statement('CREATE INDEX profile_field_values_field ON profile_field_values (field_id)');
        DB::statement('CREATE INDEX profile_field_values_number ON profile_field_values (value_number) WHERE value_number IS NOT NULL');
        DB::statement('CREATE INDEX profile_field_values_date ON profile_field_values (value_date) WHERE value_date IS NOT NULL');
        DB::statement('CREATE INDEX profile_field_values_boolean ON profile_field_values (value_boolean) WHERE value_boolean IS NOT NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS profile_field_values_number');
        DB::statement('DROP INDEX IF EXISTS profile_field_values_date');
        DB::statement('DROP INDEX IF EXISTS profile_field_values_boolean');
        DB::statement('DROP INDEX IF EXISTS profile_field_values_field');
        DB::statement('DROP INDEX IF EXISTS profile_field_values_profile');
        Schema::dropIfExists('profile_field_value_options');
        Schema::dropIfExists('profile_field_values');
    }
};
