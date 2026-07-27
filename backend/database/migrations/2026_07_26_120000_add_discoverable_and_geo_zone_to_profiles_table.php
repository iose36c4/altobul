<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->boolean('discoverable')->default(true)->after('location_updated_at');
            $table->foreignUuid('geo_zone_id')->nullable()->after('discoverable')->constrained('geo_zones')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('geo_zone_id');
            $table->dropColumn('discoverable');
        });
    }
};
