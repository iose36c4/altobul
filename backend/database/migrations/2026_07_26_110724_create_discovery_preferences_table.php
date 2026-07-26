<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discovery_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('field_id')->nullable()->constrained('profile_fields')->nullOnDelete();
            $table->enum('operator', ['eq', 'neq', 'gt', 'gte', 'lt', 'lte', 'in', 'nin', 'between', 'is_null', 'is_not_null']);
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 20, 4)->nullable();
            $table->decimal('value_number_2', 20, 4)->nullable();
            $table->date('value_date')->nullable();
            $table->date('value_date_2')->nullable();
            $table->json('value_option_ids')->nullable(); // JSON array of option UUIDs
            $table->boolean('value_boolean')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
        });

        DB::statement('CREATE INDEX discovery_prefs_user_active ON discovery_preferences (user_id) WHERE is_active = true');
        DB::statement('CREATE INDEX discovery_prefs_field_op ON discovery_preferences (field_id, operator) WHERE is_active = true');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS discovery_prefs_field_op');
        DB::statement('DROP INDEX IF EXISTS discovery_prefs_user_active');
        Schema::dropIfExists('discovery_preferences');
    }
};