<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("DO \$\$ BEGIN CREATE TYPE report_status AS ENUM ('PENDING', 'REVIEWED', 'DISMISSED', 'ACTIONED'); EXCEPTION WHEN duplicate_object THEN NULL; END \$\$;");
        DB::statement("DO \$\$ BEGIN CREATE TYPE report_reason AS ENUM ('SPAM', 'ABUSE', 'HARASSMENT', 'INAPPROPRIATE', 'FAKE', 'UNDERAGE', 'OTHER'); EXCEPTION WHEN duplicate_object THEN NULL; END \$\$;");
        DB::statement("DO \$\$ BEGIN CREATE TYPE reportable_type AS ENUM ('user', 'post', 'photo', 'message', 'conversation'); EXCEPTION WHEN duplicate_object THEN NULL; END \$\$;");

        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('reporter_id');
            $table->string('reportable_type', 50);
            $table->uuid('reportable_id');
            $table->string('reason', 30);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('PENDING');
            $table->uuid('reviewed_by')->nullable();
            $table->timestampTz('reviewed_at')->nullable();
            $table->text('admin_notes')->nullable();
            $table->timestampTz('created_at')->useCurrent();

            $table->foreign('reporter_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['reportable_type', 'reportable_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        DB::statement('DROP TYPE IF EXISTS report_status');
        DB::statement('DROP TYPE IF EXISTS report_reason');
        DB::statement('DROP TYPE IF EXISTS reportable_type');
    }
};
