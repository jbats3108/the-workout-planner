<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('primary_muscle_group_id')->constrained('muscle_groups');
            $table->foreignId('secondary_muscle_group_id')->nullable()->constrained('muscle_groups');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercises');
    }
};
