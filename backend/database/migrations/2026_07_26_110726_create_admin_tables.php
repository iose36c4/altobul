<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('admin_id')->constrained('users');
            $table->string('action', 100);
            $table->string('target_type', 50)->nullable();
            $table->uuid('target_id')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestampTz('created_at')->useCurrent();
        });

        DB::statement('CREATE INDEX admin_audit_admin_created ON admin_audit_logs (admin_id, created_at DESC)');
        DB::statement('CREATE INDEX admin_audit_target ON admin_audit_logs (target_type, target_id)');

        Schema::create('app_configs', function (Blueprint $table) {
            $table->string('key', 100)->primary();
            $table->jsonb('value');
            $table->text('description')->nullable();
            $table->foreignUuid('updated_by')->nullable()->constrained('users');
            $table->timestampTz('updated_at')->useCurrent();
        });
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS admin_audit_target');
        DB::statement('DROP INDEX IF EXISTS admin_audit_admin_created');
        Schema::dropIfExists('app_configs');
        Schema::dropIfExists('admin_audit_logs');
    }
};