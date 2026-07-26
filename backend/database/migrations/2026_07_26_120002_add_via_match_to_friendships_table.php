<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('friendships', function (Blueprint $table) {
            $table->boolean('via_match')->default(false)->after('ended_by');
        });
    }

    public function down(): void
    {
        Schema::table('friendships', function (Blueprint $table) {
            $table->dropColumn('via_match');
        });
    }
};