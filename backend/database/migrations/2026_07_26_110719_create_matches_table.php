<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_a_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('user_b_id')->constrained('users')->cascadeOnDelete();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('expires_at')->nullable(false);
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'ENDED_BY_BLOCK'])->default('ACTIVE');
            $table->timestampTz('ended_at')->nullable();
            $table->foreignUuid('ended_by')->nullable()->constrained('users');
        });

        DB::statement('ALTER TABLE matches ADD CONSTRAINT normalized_pair CHECK (user_a_id < user_b_id)');

        // Partial unique index: only one ACTIVE match per pair
        DB::statement('CREATE UNIQUE INDEX matches_active_unique ON matches (user_a_id, user_b_id) WHERE status = \'ACTIVE\'');
        DB::statement('CREATE INDEX matches_user_a ON matches (user_a_id, status) WHERE status IN (\'ACTIVE\', \'EXPIRED\')');
        DB::statement('CREATE INDEX matches_user_b ON matches (user_b_id, status) WHERE status IN (\'ACTIVE\', \'EXPIRED\')');
        DB::statement('CREATE INDEX matches_expires ON matches (expires_at) WHERE status = \'ACTIVE\'');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS matches_expires');
        DB::statement('DROP INDEX IF EXISTS matches_user_b');
        DB::statement('DROP INDEX IF EXISTS matches_user_a');
        DB::statement('DROP INDEX IF EXISTS matches_active_unique');
        Schema::dropIfExists('matches');
    }
};
