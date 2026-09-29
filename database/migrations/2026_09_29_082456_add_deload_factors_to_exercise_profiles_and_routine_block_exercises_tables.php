<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('exercise_profiles', function (Blueprint $table) {
            $table->decimal('deload_weight_factor', 5, 3)->default(0.5)->after('warm_up_steps');
            $table->decimal('deload_reps_factor', 5, 3)->default(0.5)->after('deload_weight_factor');
        });

        Schema::table('routine_block_exercises', function (Blueprint $table) {
            $table->decimal('deload_weight_factor', 5, 3)->default(0.5)->after('progression_target_override');
            $table->decimal('deload_reps_factor', 5, 3)->default(0.5)->after('deload_weight_factor');
        });
    }

    public function down(): void
    {
        Schema::table('exercise_profiles', function (Blueprint $table) {
            $table->dropColumn(['deload_weight_factor', 'deload_reps_factor']);
        });

        Schema::table('routine_block_exercises', function (Blueprint $table) {
            $table->dropColumn(['deload_weight_factor', 'deload_reps_factor']);
        });
    }
};
