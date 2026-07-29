<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routine_warm_up_steps', function (Blueprint $table) {
            $table->boolean('has_setup_after')->default(false)->after('reps');
        });

        Schema::table('workout_warm_up_steps', function (Blueprint $table) {
            $table->boolean('has_setup_after')->default(false)->after('reps');
        });
    }

    public function down(): void
    {
        Schema::table('workout_warm_up_steps', function (Blueprint $table) {
            $table->dropColumn('has_setup_after');
        });

        Schema::table('routine_warm_up_steps', function (Blueprint $table) {
            $table->dropColumn('has_setup_after');
        });
    }
};
