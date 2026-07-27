<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->json('response')->nullable();
            $table->unsignedSmallInteger('status_code')->nullable();
            $table->timestampTz('created_at')->useCurrent();
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
