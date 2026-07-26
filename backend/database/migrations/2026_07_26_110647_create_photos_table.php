<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('storage_key', 500);
            $table->enum('mime_type', ['image/jpeg', 'image/png', 'image/webp', 'image/heic']);
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            $table->enum('visibility', ['PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE'])->default('PUBLIC');
            $table->boolean('requires_verified')->default(false);
            $table->enum('status', ['ACTIVE', 'PROCESSING', 'DELETED'])->default('ACTIVE');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('updated_at')->useCurrent();
            $table->timestampTz('deleted_at')->nullable();
        });

        DB::statement('CREATE INDEX photos_user_active ON photos (user_id, sort_order) WHERE status = \'ACTIVE\' AND deleted_at IS NULL');
        DB::statement('CREATE INDEX photos_user_primary ON photos (user_id) WHERE is_primary AND status = \'ACTIVE\' AND deleted_at IS NULL');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS photos_user_primary');
        DB::statement('DROP INDEX IF EXISTS photos_user_active');
        Schema::dropIfExists('photos');
    }
};