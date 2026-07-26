<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('geo_polygons', function (Blueprint $table) {
            $table->string('name', 100)->after('zone_id');
            $table->json('geometry')->nullable()->after('name');
            $table->unsignedInteger('sort_order')->default(0)->after('geometry');
        });
    }

    public function down(): void
    {
        Schema::table('geo_polygons', function (Blueprint $table) {
            $table->dropColumn(['name', 'geometry', 'sort_order']);
        });
    }
};
