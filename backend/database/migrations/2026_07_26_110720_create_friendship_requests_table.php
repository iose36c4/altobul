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
            $table->foreignUuid('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('addressee_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['PENDING', 'ACCEPTED', 'REJECTED', 'EXPIRED'])->default('PENDING');
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('responded_at')->nullable();
            $table->timestampTz('expires_at')->nullable(false);

            $table->unique(['requester_id', 'addressee_id'], 'friendship_requests_unique_pending')->where('status', 'PENDING');
        });

        DB::statement('CREATE INDEX friendship_requests_addressee_status ON friendship_requests (addressee_id, status) WHERE status = \'PENDING\'');
        DB::statement('CREATE INDEX friendship_requests_expires ON friendship_requests (expires_at) WHERE status = \'PENDING\'');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS friendship_requests_expires');
        DB::statement('DROP INDEX IF EXISTS friendship_requests_addressee_status');
        Schema::dropIfExists('friendship_requests');
    }
};