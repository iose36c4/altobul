<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_access', function (Blueprint $table) {
            $table->timestampTz('revoked_at')->nullable()->after('granted_at');
            $table->timestampTz('expires_at')->nullable()->after('revoked_at');
        });

        Schema::table('post_access', function (Blueprint $table) {
            $table->timestampTz('revoked_at')->nullable()->after('granted_at');
            $table->timestampTz('expires_at')->nullable()->after('revoked_at');
        });

        Schema::table('profile_field_value_access', function (Blueprint $table) {
            $table->timestampTz('revoked_at')->nullable()->after('granted_at');
            $table->timestampTz('expires_at')->nullable()->after('revoked_at');
        });
    }

    public function down(): void
    {
        Schema::table('photo_access', function (Blueprint $table) {
            $table->dropColumn(['revoked_at', 'expires_at']);
        });

        Schema::table('post_access', function (Blueprint $table) {
            $table->dropColumn(['revoked_at', 'expires_at']);
        });

        Schema::table('profile_field_value_access', function (Blueprint $table) {
            $table->dropColumn(['revoked_at', 'expires_at']);
        });
    }
};
