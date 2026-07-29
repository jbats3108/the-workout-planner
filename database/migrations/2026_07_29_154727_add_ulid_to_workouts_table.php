<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Avoid Schema::change() on workouts: SQLite rebuilds the table and drops the
     * partial unique index workouts_one_in_progress_per_user (WHERE status/deleted_at).
     * Column stays nullable at the DB layer; backfill + app/factory always set a value.
     */
    public function up(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->string('ulid', 26)->nullable()->after('id');
        });

        DB::table('workouts')
            ->orderBy('id')
            ->get(['id'])
            ->each(function (object $workout): void {
                DB::table('workouts')->where('id', $workout->id)->update([
                    'ulid' => (string) Str::ulid(),
                ]);
            });

        Schema::table('workouts', function (Blueprint $table) {
            $table->unique('ulid');
        });
    }

    public function down(): void
    {
        Schema::table('workouts', function (Blueprint $table) {
            $table->dropUnique(['ulid']);
            $table->dropColumn('ulid');
        });
    }
};
