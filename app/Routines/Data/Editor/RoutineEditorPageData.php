<?php

namespace App\Routines\Data\Editor;

use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Shared\Enums\SetGroupType;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapName(SnakeCaseMapper::class)]
class RoutineEditorPageData extends Data
{
    /**
     * @param  DataCollection<int, RoutineEditorBlockData>  $blocks
     * @param  DataCollection<int, RoutineEditorExerciseOptionData>  $exercises
     */
    public function __construct(
        public readonly int $id,
        public readonly string $slug,
        public readonly string $name,
        public readonly float $deloadWeightFactor,
        public readonly float $deloadRepsFactor,
        #[DataCollectionOf(RoutineEditorBlockData::class)]
        public readonly DataCollection $blocks,
        #[DataCollectionOf(RoutineEditorExerciseOptionData::class)]
        public readonly DataCollection $exercises,
        public readonly string $weightUnit,
    ) {}

    /**
     * @param  DataCollection<int, RoutineEditorExerciseOptionData>  $exercises
     */
    public static function fromRoutine(Routine $routine, DataCollection $exercises, string $weightUnit): self
    {
        $routine->loadMissing([
            'blocks.blockExercises.exercise',
            'blocks.setGroups.warmUpSteps',
            'blocks.setGroups.dropsetSegments',
        ]);

        $blocks = $routine->blocks->map(function (RoutineBlock $block) {
            $working = $block->setGroups->firstWhere('type', SetGroupType::Working);
            $warmUp = $block->setGroups->firstWhere('type', SetGroupType::WarmUp);

            $dropsets = collect($working?->dropsetSegments ?? [])
                ->groupBy('set_index')
                ->filter(fn ($segments) => $segments->count() >= 2)
                ->map(fn ($segments, $setIndex) => new SyncDropsetData(
                    setIndex: (int) $setIndex,
                    segments: SyncDropsetSegmentData::collect(
                        $segments->sortBy('position')->values()->map(
                            fn ($segment) => new SyncDropsetSegmentData(
                                weightKg: round($segment->weight_g / 1000, 3),
                            )
                        ),
                        DataCollection::class,
                    ),
                ))
                ->values();

            return new RoutineEditorBlockData(
                isSuperset: $block->is_superset,
                hasSetupAfter: $block->has_setup_after,
                hasSetupAfterWarmUp: $block->has_setup_after_warm_up,
                exercises: RoutineEditorBlockExerciseData::collect(
                    $block->blockExercises->map(fn (RoutineBlockExercise $row) => new RoutineEditorBlockExerciseData(
                        exerciseId: $row->exercise_id,
                        workingWeightKg: round($row->working_weight_g / 1000, 3),
                        prescribedReps: $row->prescribed_reps,
                        achievementFloor: $row->achievement_floor_override,
                        progressionTarget: $row->progression_target_override,
                    )),
                    DataCollection::class,
                ),
                working: new SyncSetGroupData(
                    setCount: $working?->set_count ?? 3,
                    restSeconds: $working?->rest_seconds ?? 120,
                    dropsets: SyncDropsetData::collect($dropsets, DataCollection::class),
                ),
                warmUp: new SyncWarmUpData(
                    setCount: $warmUp?->set_count ?? 0,
                    restSeconds: $warmUp?->rest_seconds ?? 60,
                    steps: SyncWarmUpStepData::collect(
                        $warmUp?->warmUpSteps->map(fn ($step) => new SyncWarmUpStepData(
                            percent: (int) $step->percent_of_working,
                            reps: (int) ($step->reps ?? 5),
                            hasSetupAfter: (bool) $step->has_setup_after,
                        )) ?? [],
                        DataCollection::class,
                    ),
                ),
            );
        });

        return new self(
            id: $routine->id,
            slug: $routine->getSlug(),
            name: $routine->getName(),
            deloadWeightFactor: (float) $routine->deload_weight_factor,
            deloadRepsFactor: (float) $routine->deload_reps_factor,
            blocks: RoutineEditorBlockData::collect($blocks, DataCollection::class),
            exercises: $exercises,
            weightUnit: $weightUnit,
        );
    }
}
