<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_exercise', function (Blueprint $table) {
            $table->foreignId('exercise_id');
            $table->foreignId('routine_id');
            $table->integer('sets')->default(3);
            $table->integer('reps')->default(6);
            $table->integer('weight')->nullable();
            $table->boolean('to_failure')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_exercise');
    }
};
