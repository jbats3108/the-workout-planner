<?php

use App\Models\Exercise;
use App\Models\Workout;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_sets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Workout::class)->constrained('workouts');
            $table->foreignIdFor(Exercise::class)->constrained('exercises');
            $table->integer('set');
            $table->decimal('weight')->nullable();
            $table->integer('reps');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_sets');
    }
};
