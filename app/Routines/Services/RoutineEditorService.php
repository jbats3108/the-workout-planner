<?php

namespace App\Routines\Services;

use App\Routines\Data\Editor\SyncBlockExerciseData;
use App\Routines\Data\Editor\SyncRoutineBlockData;
use App\Routines\Data\Editor\SyncRoutineData;
use App\Routines\Data\Editor\SyncWarmUpData;
use App\Shared\Enums\SetGroupType;
use App\Exercises\Models\Exercise;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineSetGroup;
use App\Routines\Models\RoutineWarmUpStep;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RoutineEditorService
{
    public function sync(Routine $routine, SyncRoutineData $data): Routine
    {
        return DB::transaction(function () use ($routine, $data): Routine {
            $routine->update([
                'name' => $data->name,
                'deload_weight_factor' => $data->deloadWeightFactor ?? $routine->deload_weight_factor,
                'deload_reps_factor' => $data->deloadRepsFactor ?? $routine->deload_reps_factor,
            ]);

            $routine->blocks()->each(function (RoutineBlock $block): void {
                $block->delete();
            });

            $blocks = $data->blocks ?? [];
            foreach (array_values(iterator_to_array($blocks)) as $position => $blockData) {
                /** @var SyncRoutineBlockData $blockData */
                $this->createBlock($routine, $position + 1, $blockData);
            }

            return $routine->fresh([
                'blocks.blockExercises.exercise',
                'blocks.setGroups.warmUpSteps',
            ]) ?? $routine;
        });
    }

    private function createBlock(Routine $routine, int $position, SyncRoutineBlockData $blockData): void
    {
        $exercises = $blockData->exercises->all();

        if ($blockData->isSuperset && count($exercises) !== 2) {
            throw new InvalidArgumentException('A superset block must have exactly two exercises.');
        }

        if (! $blockData->isSuperset && count($exercises) !== 1) {
            throw new InvalidArgumentException('A single block must have exactly one exercise.');
        }

        $block = RoutineBlock::create([
            'routine_id' => $routine->id,
            'position' => $position,
            'is_superset' => $blockData->isSuperset,
            'has_setup_after' => $blockData->hasSetupAfter,
        ]);

        foreach (array_values($exercises) as $index => $exerciseData) {
            /** @var SyncBlockExerciseData $exerciseData */
            $this->assertExerciseAvailable($routine, $exerciseData->exerciseId);

            RoutineBlockExercise::create([
                'routine_block_id' => $block->id,
                'exercise_id' => $exerciseData->exerciseId,
                'position' => $index + 1,
                'working_weight_g' => $exerciseData->workingWeightGrams(),
                'prescribed_reps' => $exerciseData->prescribedReps,
                'achievement_floor_override' => $exerciseData->achievementFloor,
                'progression_target_override' => $exerciseData->progressionTarget,
            ]);
        }

        RoutineSetGroup::create([
            'routine_block_id' => $block->id,
            'type' => SetGroupType::Working,
            'set_count' => $blockData->working->setCount,
            'rest_seconds' => $blockData->working->restSeconds,
        ]);

        $warmUp = $blockData->warmUp ?? new SyncWarmUpData;
        $percents = array_values(array_filter(
            $warmUp->percents,
            fn (int $p): bool => $p > 0
        ));

        $warmUpGroup = RoutineSetGroup::create([
            'routine_block_id' => $block->id,
            'type' => SetGroupType::WarmUp,
            'set_count' => max(count($percents), $warmUp->setCount),
            'rest_seconds' => $warmUp->restSeconds,
        ]);

        foreach ($percents as $stepIndex => $percent) {
            RoutineWarmUpStep::create([
                'routine_set_group_id' => $warmUpGroup->id,
                'position' => $stepIndex + 1,
                'percent_of_working' => min(100, max(1, $percent)),
            ]);
        }
    }

    private function assertExerciseAvailable(Routine $routine, int $exerciseId): void
    {
        $exists = Exercise::query()
            ->whereKey($exerciseId)
            ->where(function ($query) use ($routine): void {
                $query->whereNull('user_id')->orWhere('user_id', $routine->user_id);
            })
            ->exists();

        if (! $exists) {
            throw new InvalidArgumentException("Exercise {$exerciseId} is not available for this routine.");
        }
    }
}
