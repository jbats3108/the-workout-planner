<?php

namespace App\Workouts\Services;

use App\Routines\Models\RoutineBlockExercise;
use App\Shared\Enums\SetGroupType;
use App\Workouts\Data\Progression\BumpProposalData;
use App\Workouts\Enums\WorkoutMode;
use App\Workouts\Models\Workout;
use App\Workouts\Models\WorkoutBlockExercise;
use App\Workouts\Models\WorkoutSet;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\DataCollection;

class WorkoutProgressionService
{
    /** Default bump when no plate calculator step is available yet (2.5 kg). */
    public const DEFAULT_BUMP_G = 2500;

    public function applyCarryForwardAndCollectBumps(Workout $workout): DataCollection
    {
        if ($workout->mode === WorkoutMode::Deload) {
            return BumpProposalData::collect([], DataCollection::class);
        }

        $workout->load([
            'routine.blocks.blockExercises',
            'blocks.blockExercises.sets.setGroup',
        ]);

        $proposals = [];

        foreach ($workout->blocks as $workoutBlock) {
            $routineBlock = $workout->routine->blocks->firstWhere('position', $workoutBlock->position);

            if ($routineBlock === null) {
                continue;
            }

            foreach ($workoutBlock->blockExercises as $workoutExercise) {
                $routineExercise = $this->matchRoutineExercise($routineBlock->blockExercises, $workoutExercise);

                if ($routineExercise === null) {
                    continue;
                }

                $workingSets = $this->completedWorkingSets($workoutExercise);

                $this->carryForward($routineExercise, $workoutExercise, $workingSets);

                if ($this->hitProgressionTarget($workoutExercise, $workingSets)) {
                    $routineExercise->refresh();
                    $from = $routineExercise->working_weight_g;
                    $proposals[] = new BumpProposalData(
                        routineBlockExerciseId: $routineExercise->id,
                        exerciseName: $workoutExercise->exercise_name,
                        fromWeightG: $from,
                        toWeightG: $from + self::DEFAULT_BUMP_G,
                    );
                }
            }
        }

        return BumpProposalData::collect($proposals, DataCollection::class);
    }

    /**
     * @param  list<int>  $routineBlockExerciseIds
     * @param  DataCollection<int, BumpProposalData>  $proposals
     */
    public function applyConfirmedBumps(DataCollection $proposals, array $routineBlockExerciseIds): void
    {
        $selected = collect($routineBlockExerciseIds)->unique()->all();

        DB::transaction(function () use ($proposals, $selected): void {
            foreach ($proposals as $proposal) {
                /** @var BumpProposalData $proposal */
                if (! in_array($proposal->routineBlockExerciseId, $selected, true)) {
                    continue;
                }

                $exercise = RoutineBlockExercise::query()->find($proposal->routineBlockExerciseId);

                if ($exercise === null) {
                    continue;
                }

                $exercise->working_weight_g = $proposal->toWeightG;
                $exercise->save();
            }
        });
    }

    /**
     * @param  Collection<int, RoutineBlockExercise>  $routineExercises
     */
    private function matchRoutineExercise(Collection $routineExercises, WorkoutBlockExercise $workoutExercise): ?RoutineBlockExercise
    {
        return $routineExercises->firstWhere('exercise_id', $workoutExercise->exercise_id)
            ?? $routineExercises->firstWhere('position', $workoutExercise->position);
    }

    /**
     * @return Collection<int, WorkoutSet>
     */
    private function completedWorkingSets(WorkoutBlockExercise $workoutExercise): Collection
    {
        return $workoutExercise->sets
            ->filter(function (WorkoutSet $set): bool {
                return $set->completed_at !== null
                    && $set->setGroup?->type === SetGroupType::Working
                    && $set->reps !== null
                    && $set->weight_g !== null;
            })
            ->values();
    }

    /**
     * @param  Collection<int, WorkoutSet>  $workingSets
     */
    private function carryForward(
        RoutineBlockExercise $routineExercise,
        WorkoutBlockExercise $workoutExercise,
        Collection $workingSets,
    ): void {
        $floor = $workoutExercise->achievement_floor ?? 1;

        $highestAchieved = $workingSets
            ->filter(fn (WorkoutSet $set): bool => $set->reps >= $floor)
            ->max('weight_g');

        if ($highestAchieved === null) {
            return;
        }

        if ($highestAchieved > $routineExercise->working_weight_g) {
            $routineExercise->working_weight_g = (int) $highestAchieved;
            $routineExercise->save();
        }
    }

    /**
     * @param  Collection<int, WorkoutSet>  $workingSets
     */
    private function hitProgressionTarget(WorkoutBlockExercise $workoutExercise, Collection $workingSets): bool
    {
        $target = $workoutExercise->progression_target;

        if ($target === null) {
            return false;
        }

        return $workingSets->contains(
            fn (WorkoutSet $set): bool => $set->weight_g >= $workoutExercise->working_weight_g
                && $set->reps >= $target
        );
    }
}
