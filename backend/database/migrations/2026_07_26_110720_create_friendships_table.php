<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friendships', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_a_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('user_b_id')->constrained('users')->cascadeOnDelete();
            $table->timestampTz('created_at')->useCurrent();
            $table->enum('status', ['ACTIVE', 'ENDED'])->default('ACTIVE');
            $table->timestampTz('ended_at')->nullable();
            $table->foreignUuid('ended_by')->nullable()->constrained('users');
        });

        DB::statement('ALTER TABLE friendships ADD CONSTRAINT normalized_pair CHECK (user_a_id < user_b_id)');

        // Partial unique index: only one ACTIVE friendship per pair
        DB::statement('CREATE UNIQUE INDEX friendships_active_unique ON friendships (user_a_id, user_b_id) WHERE status = \'ACTIVE\'');
        DB::statement('CREATE INDEX friendships_user_a ON friendships (user_a_id) WHERE status = \'ACTIVE\'');
        DB::statement('CREATE INDEX friendships_user_b ON friendships (user_b_id) WHERE status = \'ACTIVE\'');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS friendships_user_b');
        DB::statement('DROP INDEX IF EXISTS friendships_user_a');
        DB::statement('DROP INDEX IF EXISTS friendships_active_unique');
        Schema::dropIfExists('friendships');
    }
};