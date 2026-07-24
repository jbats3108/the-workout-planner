<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workout_set_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workout_block_id')->constrained('workout_blocks')->cascadeOnDelete();
            $table->string('type', 16); // warm_up | working
            $table->unsignedTinyInteger('set_count');
            $table->unsignedInteger('rest_seconds')->default(0);
            $table->timestamps();

            $table->unique(['workout_block_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workout_set_groups');
    }
};
