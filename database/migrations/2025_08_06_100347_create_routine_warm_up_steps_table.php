<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_warm_up_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_set_group_id')->constrained('routine_set_groups')->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->unsignedTinyInteger('percent_of_working'); // e.g. 50 = 50%
            $table->timestamps();

            $table->unique(['routine_set_group_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_warm_up_steps');
    }
};
