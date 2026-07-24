<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routine_warm_up_steps', function (Blueprint $table) {
            $table->unsignedTinyInteger('reps')->default(5)->after('percent_of_working');
        });

        Schema::table('workout_warm_up_steps', function (Blueprint $table) {
            $table->unsignedTinyInteger('reps')->default(5)->after('percent_of_working');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->json('warm_up_steps_default')->nullable()->after('progression_target_default');
        });

        DB::table('routine_warm_up_steps')->whereNull('reps')->update(['reps' => 5]);
        DB::table('workout_warm_up_steps')->whereNull('reps')->update(['reps' => 5]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('warm_up_steps_default');
        });

        Schema::table('workout_warm_up_steps', function (Blueprint $table) {
            $table->dropColumn('reps');
        });

        Schema::table('routine_warm_up_steps', function (Blueprint $table) {
            $table->dropColumn('reps');
        });
    }
};
