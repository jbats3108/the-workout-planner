<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('routine_block_exercises', function (Blueprint $table) {
            $table->string('note', 64)->nullable()->after('working_weight_g');
            $table->string('deload_note', 64)->nullable()->after('deload_working_weight_g');
        });

        Schema::table('workout_block_exercises', function (Blueprint $table) {
            $table->string('note', 64)->nullable()->after('working_weight_g');
        });

        Schema::table('workout_sets', function (Blueprint $table) {
            $table->string('note', 64)->nullable()->after('weight_g');
        });
    }

    public function down(): void
    {
        Schema::table('workout_sets', function (Blueprint $table) {
            $table->dropColumn('note');
        });

        Schema::table('workout_block_exercises', function (Blueprint $table) {
            $table->dropColumn('note');
        });

        Schema::table('routine_block_exercises', function (Blueprint $table) {
            $table->dropColumn(['note', 'deload_note']);
        });
    }
};
