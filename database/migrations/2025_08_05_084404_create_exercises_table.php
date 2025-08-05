<?php

use App\Enums\Difficulty;
use App\Enums\MovementType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('primary_muscle_group_id')->constrained('muscle_groups');
            $table->foreignId('secondary_muscle_group_id')->nullable()->constrained('muscle_groups');
            $table->enum('movement_type', MovementType::values())->default('push');
            $table->enum('difficulty', Difficulty::values())->default('beginner');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
