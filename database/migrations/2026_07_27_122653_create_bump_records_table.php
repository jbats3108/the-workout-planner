<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bump_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_id')->constrained()->cascadeOnDelete();
            $table->foreignId('routine_block_exercise_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('from_weight_g');
            $table->unsignedInteger('to_weight_g');
            $table->timestamp('undone_at')->nullable();
            $table->timestamps();

            $table->unique(['workout_id', 'routine_block_exercise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bump_records');
    }
};
