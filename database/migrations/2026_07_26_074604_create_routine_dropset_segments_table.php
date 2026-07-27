<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_dropset_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_set_group_id')->constrained('routine_set_groups')->cascadeOnDelete();
            $table->unsignedTinyInteger('set_index');
            $table->unsignedTinyInteger('position');
            $table->unsignedInteger('weight_g');
            $table->timestamps();

            $table->unique(
                ['routine_set_group_id', 'set_index', 'position'],
                'routine_dropset_segments_group_index_position_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_dropset_segments');
    }
};
