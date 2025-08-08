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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_exercise');
    }
};
