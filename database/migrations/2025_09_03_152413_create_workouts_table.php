<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('routine_id')->constrained('routines')->restrictOnDelete();
            $table->string('mode', 16)->default('normal'); // normal | deload
            $table->string('status', 16)->default('in_progress'); // in_progress | finished | discarded
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // At most one in-progress workout per user (SQLite / Postgres).
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            DB::statement(
                'CREATE UNIQUE INDEX workouts_one_in_progress_per_user ON workouts (user_id) WHERE status = \'in_progress\' AND deleted_at IS NULL'
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('workouts');
    }
};
