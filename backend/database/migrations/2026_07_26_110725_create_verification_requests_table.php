<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->timestampTz('submitted_at')->useCurrent();
            $table->timestampTz('reviewed_at')->nullable();
            $table->foreignUuid('reviewed_by')->nullable()->constrained('users');
            $table->text('rejection_reason')->nullable();
            $table->string('verification_method', 50)->nullable();
            $table->string('external_reference', 200)->nullable();
        });

        // Partial unique index: one active PENDING or APPROVED request per user
        DB::statement('CREATE UNIQUE INDEX verification_request_active_unique ON verification_requests (user_id) WHERE status IN (\'PENDING\', \'APPROVED\')');
        DB::statement('CREATE INDEX verification_requests_user ON verification_requests (user_id)');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS verification_requests_user');
        DB::statement('DROP INDEX IF EXISTS verification_request_active_unique');
        Schema::dropIfExists('verification_requests');
    }
};
