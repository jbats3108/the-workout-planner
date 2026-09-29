<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Preset factors from the grill; customs keep column defaults (0.5 / 0.5).
     * RBEs on OVRLOAD presets copy that preset; all others copy the parent routine.
     */
    public function up(): void
    {
        $presetFactors = [
            'preset-strength' => ['deload_weight_factor' => 0.5, 'deload_reps_factor' => 2.0],
            'preset-hypertrophy' => ['deload_weight_factor' => 0.5, 'deload_reps_factor' => 1.5],
            'preset-endurance' => ['deload_weight_factor' => 0.5, 'deload_reps_factor' => 1.0],
        ];

        foreach ($presetFactors as $slug => $factors) {
            DB::table('exercise_profiles')
                ->where('slug_scope', 'system')
                ->where('slug', $slug)
                ->update($factors);
        }

        $driver = DB::getDriverName();

        if ($driver === 'sqlite') {
            DB::statement(<<<'SQL'
                UPDATE routine_block_exercises
                SET
                    deload_weight_factor = (
                        SELECT exercise_profiles.deload_weight_factor
                        FROM exercise_profiles
                        WHERE exercise_profiles.id = routine_block_exercises.exercise_profile_id
                          AND exercise_profiles.kind = 'preset'
                          AND exercise_profiles.user_id IS NULL
                    ),
                    deload_reps_factor = (
                        SELECT exercise_profiles.deload_reps_factor
                        FROM exercise_profiles
                        WHERE exercise_profiles.id = routine_block_exercises.exercise_profile_id
                          AND exercise_profiles.kind = 'preset'
                          AND exercise_profiles.user_id IS NULL
                    )
                WHERE exercise_profile_id IS NOT NULL
                  AND EXISTS (
                    SELECT 1 FROM exercise_profiles
                    WHERE exercise_profiles.id = routine_block_exercises.exercise_profile_id
                      AND exercise_profiles.kind = 'preset'
                      AND exercise_profiles.user_id IS NULL
                  )
            SQL);

            DB::statement(<<<'SQL'
                UPDATE routine_block_exercises
                SET
                    deload_weight_factor = (
                        SELECT routines.deload_weight_factor
                        FROM routine_blocks
                        INNER JOIN routines ON routines.id = routine_blocks.routine_id
                        WHERE routine_blocks.id = routine_block_exercises.routine_block_id
                    ),
                    deload_reps_factor = (
                        SELECT routines.deload_reps_factor
                        FROM routine_blocks
                        INNER JOIN routines ON routines.id = routine_blocks.routine_id
                        WHERE routine_blocks.id = routine_block_exercises.routine_block_id
                    )
                WHERE exercise_profile_id IS NULL
                   OR NOT EXISTS (
                    SELECT 1 FROM exercise_profiles
                    WHERE exercise_profiles.id = routine_block_exercises.exercise_profile_id
                      AND exercise_profiles.kind = 'preset'
                      AND exercise_profiles.user_id IS NULL
                  )
            SQL);

            return;
        }

        DB::statement(<<<'SQL'
            UPDATE routine_block_exercises AS rbe
            INNER JOIN exercise_profiles AS ep ON ep.id = rbe.exercise_profile_id
            SET
                rbe.deload_weight_factor = ep.deload_weight_factor,
                rbe.deload_reps_factor = ep.deload_reps_factor
            WHERE ep.kind = 'preset'
              AND ep.user_id IS NULL
        SQL);

        DB::statement(<<<'SQL'
            UPDATE routine_block_exercises AS rbe
            INNER JOIN routine_blocks AS rb ON rb.id = rbe.routine_block_id
            INNER JOIN routines AS r ON r.id = rb.routine_id
            LEFT JOIN exercise_profiles AS ep ON ep.id = rbe.exercise_profile_id
            SET
                rbe.deload_weight_factor = r.deload_weight_factor,
                rbe.deload_reps_factor = r.deload_reps_factor
            WHERE rbe.exercise_profile_id IS NULL
               OR ep.kind IS NULL
               OR ep.kind <> 'preset'
               OR ep.user_id IS NOT NULL
        SQL);
    }

    public function down(): void
    {
        // Irreversible data backfill — restore via forward fix if needed.
    }
};
