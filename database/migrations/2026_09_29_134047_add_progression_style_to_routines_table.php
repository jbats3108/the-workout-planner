<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->string('progression_style', 32)->default('straight_sets')->after('deload_every_n');
            $table->string('progressive_mid_block', 32)->default('ask')->after('progression_style');
        });
    }

    public function down(): void
    {
        Schema::table('routines', function (Blueprint $table) {
            $table->dropColumn(['progression_style', 'progressive_mid_block']);
        });
    }
};
