<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routine_blocks', function (Blueprint $table) {
            $table->boolean('has_setup_after_warm_up')->default(false)->after('has_setup_after');
        });

        Schema::table('workout_blocks', function (Blueprint $table) {
            $table->boolean('has_setup_after_warm_up')->default(false)->after('has_setup_after');
        });
    }

    public function down(): void
    {
        Schema::table('routine_blocks', function (Blueprint $table) {
            $table->dropColumn('has_setup_after_warm_up');
        });

        Schema::table('workout_blocks', function (Blueprint $table) {
            $table->dropColumn('has_setup_after_warm_up');
        });
    }
};
