<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_exercise', function (Blueprint $table) {
            $table->foreignId('exercise_id');
            $table->foreignId('workout_id');
            $table->integer('sets');
            $table->integer('reps');
            $table->integer('weight');
            $table->boolean('to_failure')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_exercise');
    }
};
