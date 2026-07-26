<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_set_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_set_id')->constrained('workout_sets')->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->unsignedInteger('weight_g');
            $table->timestamps();

            $table->unique(
                ['workout_set_id', 'position'],
                'workout_set_segments_set_position_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_set_segments');
    }
};
