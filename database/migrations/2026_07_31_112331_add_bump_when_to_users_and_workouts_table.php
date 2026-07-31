<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bump_when_default', 32)->default('any_set')->after('progression_target_default');
        });

        Schema::table('workouts', function (Blueprint $table) {
            $table->string('bump_when', 32)->default('any_set')->after('mode');
        });
    }

    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropColumn('bump_when');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('bump_when_default');
        });
    }
};
