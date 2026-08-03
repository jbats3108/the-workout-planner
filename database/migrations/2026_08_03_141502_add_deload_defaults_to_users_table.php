<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('deload_weight_factor_default', 5, 3)->default(0.5)->after('bump_when_default');
            $table->decimal('deload_reps_factor_default', 5, 3)->default(2)->after('deload_weight_factor_default');
            $table->unsignedTinyInteger('deload_every_n_default')->default(3)->after('deload_reps_factor_default');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'deload_weight_factor_default',
                'deload_reps_factor_default',
                'deload_every_n_default',
            ]);
        });
    }
};
