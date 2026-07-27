<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('blocked_id')->constrained('users')->cascadeOnDelete();
            $table->timestampTz('created_at')->useCurrent();

            $table->unique(['blocker_id', 'blocked_id']);
        });

        DB::statement('ALTER TABLE blocks ADD CONSTRAINT no_self_block CHECK (blocker_id != blocked_id)');

        DB::statement('CREATE INDEX blocks_blocker ON blocks (blocker_id)');
        DB::statement('CREATE INDEX blocks_blocked ON blocks (blocked_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS blocks_blocked');
        DB::statement('DROP INDEX IF EXISTS blocks_blocker');
        Schema::dropIfExists('blocks');
    }
};
