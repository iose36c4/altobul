<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('friendship_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignUuid('requester_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED', 'EXPIRED'])->default('PENDING');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('responded_at')->nullable();
            $table->unique(['match_id', 'requester_id']);
        });

        DB::statement('CREATE INDEX friendship_requests_match ON friendship_requests (match_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS friendship_requests_match');
        Schema::dropIfExists('friendship_requests');
    }
};