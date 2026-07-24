<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_block_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_block_id')->constrained('routine_blocks')->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained('exercises')->restrictOnDelete();
            $table->unsignedTinyInteger('position'); // 1, or 1–2 for supersets
            $table->unsignedInteger('working_weight_g')->default(0);
            $table->unsignedSmallInteger('prescribed_reps');
            $table->unsignedSmallInteger('achievement_floor_override')->nullable();
            $table->unsignedSmallInteger('progression_target_override')->nullable();
            $table->timestamps();

            $table->unique(['routine_block_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_block_exercises');
    }
};
