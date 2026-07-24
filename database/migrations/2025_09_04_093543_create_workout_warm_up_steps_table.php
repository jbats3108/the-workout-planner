<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_warm_up_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_set_group_id')->constrained('workout_set_groups')->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->unsignedTinyInteger('percent_of_working');
            $table->timestamps();

            $table->unique(['workout_set_group_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_warm_up_steps');
    }
};
