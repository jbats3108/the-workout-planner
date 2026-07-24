<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_block_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_block_id')->constrained('workout_blocks')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->restrictOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('exercise_name'); // denormalized for history if exercise renamed
            $table->unsignedInteger('working_weight_g')->default(0);
            $table->unsignedSmallInteger('prescribed_reps');
            $table->unsignedSmallInteger('achievement_floor')->nullable();
            $table->unsignedSmallInteger('progression_target')->nullable();
            $table->timestamps();

            $table->unique(['workout_block_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_block_exercises');
    }
};
