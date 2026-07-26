<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_a_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('user_b_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['ACTIVE', 'ENDED'])->default('ACTIVE');
            $table->timestampTz('ended_at')->nullable();
            $table->foreignUuid('ended_by')->nullable()->constrained('users');
            $table->boolean('ended_by_block')->default(false);
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();

            $table->unique(['user_a_id', 'user_b_id']);
        });

        DB::statement('ALTER TABLE conversations ADD CONSTRAINT normalized_pair CHECK (user_a_id < user_b_id)');

        Schema::create('messages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('conversation_id')->constrained('conversations')->cascadeOnDelete();
            $table->foreignUuid('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('content');
            $table->timestampTz('read_at')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        DB::statement('CREATE INDEX messages_conversation_created ON messages (conversation_id, created_at DESC)');
        DB::statement('CREATE INDEX messages_sender ON messages (sender_id)');
        DB::statement('CREATE INDEX messages_unread ON messages (conversation_id, read_at) WHERE read_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS messages_unread');
        DB::statement('DROP INDEX IF EXISTS messages_sender');
        DB::statement('DROP INDEX IF EXISTS messages_conversation_created');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
    }
};