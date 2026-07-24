<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_set_group_id')->constrained('workout_set_groups')->cascadeOnDelete();
            $table->foreignId('workout_block_exercise_id')->constrained('workout_block_exercises')->cascadeOnDelete();
            $table->unsignedTinyInteger('set_index');
            $table->unsignedSmallInteger('reps')->nullable();
            $table->unsignedInteger('weight_g')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['workout_set_group_id', 'workout_block_exercise_id', 'set_index'],
                'workout_sets_group_exercise_index_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sets');
    }
};
