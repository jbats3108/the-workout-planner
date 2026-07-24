<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routine_blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('routine_id')->constrained('routines')->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->boolean('is_superset')->default(false);
            $table->boolean('has_setup_after')->default(false);
            $table->timestamps();

            $table->unique(['routine_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routine_blocks');
    }
};
