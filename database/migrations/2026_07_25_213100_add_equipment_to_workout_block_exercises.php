<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workout_block_exercises', function (Blueprint $table) {
            $table->string('equipment')->nullable()->after('exercise_name');
        });
    }

    public function down(): void
    {
        Schema::table('workout_block_exercises', function (Blueprint $table) {
            $table->dropColumn('equipment');
        });
    }
};
