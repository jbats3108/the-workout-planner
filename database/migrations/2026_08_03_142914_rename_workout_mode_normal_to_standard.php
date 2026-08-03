<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rename workout mode value normal → standard.
     *
     * Avoid Schema::change() on workouts: SQLite rebuilds the table and drops / corrupts the
     * partial unique index workouts_one_in_progress_per_user (WHERE status/deleted_at).
     * Application code always sets mode; only MySQL/Postgres need an explicit default tweak.
     */
    public function up(): void
    {
        DB::table('workouts')->where('mode', 'normal')->update(['mode' => 'standard']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE workouts MODIFY mode VARCHAR(16) NOT NULL DEFAULT 'standard'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE workouts ALTER COLUMN mode SET DEFAULT 'standard'");
        }
    }

    public function down(): void
    {
        DB::table('workouts')->where('mode', 'standard')->update(['mode' => 'normal']);

        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE workouts MODIFY mode VARCHAR(16) NOT NULL DEFAULT 'normal'");
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE workouts ALTER COLUMN mode SET DEFAULT 'normal'");
        }
    }
};
