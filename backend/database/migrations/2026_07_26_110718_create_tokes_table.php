<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tokes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->timestampTz('created_at')->useCurrent();
            $table->timestampTz('expires_at')->nullable(false);
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'CONSUMED', 'CANCELLED'])->default('ACTIVE');
            $table->timestampTz('matched_at')->nullable();
        });

        DB::statement('ALTER TABLE tokes ADD CONSTRAINT no_self_toke CHECK (sender_id != receiver_id)');

        // Partial unique index: prevent duplicate ACTIVE/CONSUMED tokes same direction
        DB::statement('CREATE UNIQUE INDEX tokes_active_unique ON tokes (sender_id, receiver_id) WHERE status IN (\'ACTIVE\', \'CONSUMED\')');
        DB::statement('CREATE INDEX tokes_receiver_active ON tokes (receiver_id, expires_at) WHERE status = \'ACTIVE\'');
        DB::statement('CREATE INDEX tokes_sender_active ON tokes (sender_id, expires_at) WHERE status = \'ACTIVE\'');
        DB::statement('CREATE INDEX tokes_expires ON tokes (expires_at) WHERE status = \'ACTIVE\'');
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS tokes_expires');
        DB::statement('DROP INDEX IF EXISTS tokes_sender_active');
        DB::statement('DROP INDEX IF EXISTS tokes_receiver_active');
        DB::statement('DROP INDEX IF EXISTS tokes_active_unique');
        Schema::dropIfExists('tokes');
    }
};
