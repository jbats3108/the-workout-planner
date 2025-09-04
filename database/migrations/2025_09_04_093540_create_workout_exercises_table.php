<?php

use App\Models\RoutineExercise;
use App\Models\Workouts\Workout;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Workout::class)->constrained('workouts');
            $table->foreignIdFor(RoutineExercise::class)->constrained('routine_exercise');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_exercises');
    }
};
