<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('content_md');
            $table->enum('visibility', ['PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE'])->default('PUBLIC');
            $table->boolean('requires_verified')->default(false);
            $table->timestampTz('expires_at')->nullable(false);
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'DELETED'])->default('ACTIVE');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        Schema::create('post_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->unique()->constrained('posts')->cascadeOnDelete();
            $table->string('storage_key', 500);
            $table->enum('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/heic']);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedBigInteger('size_bytes');
            $table->timestampTz('created_at')->useCurrent();
        });

        Schema::create('post_access', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignUuid('grantee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('granted_by')->constrained('users');
            $table->timestampTz('granted_at')->useCurrent();
            $table->unique(['post_id', 'grantee_id']);
        });

        DB::statement('CREATE INDEX posts_user_active ON posts (user_id, expires_at DESC) WHERE status = \'ACTIVE\' AND deleted_at IS NULL');
        DB::statement('CREATE INDEX posts_expires ON posts (expires_at) WHERE status = \'ACTIVE\'');
        DB::statement('CREATE INDEX posts_feed ON posts (expires_at DESC) WHERE status = \'ACTIVE\' AND visibility = \'PUBLIC\' AND requires_verified = false');
        DB::statement('CREATE INDEX post_access_grantee ON post_access (grantee_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS post_access_grantee');
        DB::statement('DROP INDEX IF EXISTS posts_feed');
        DB::statement('DROP INDEX IF EXISTS posts_expires');
        DB::statement('DROP INDEX IF EXISTS posts_user_active');
        Schema::dropIfExists('post_access');
        Schema::dropIfExists('post_attachments');
        Schema::dropIfExists('posts');
    }
};