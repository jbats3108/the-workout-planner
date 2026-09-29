<?php

namespace App\Routines\Support;

use App\Routines\Data\Editor\SyncBlockExerciseData;
use App\Routines\Data\Editor\SyncDropsetData;
use App\Routines\Data\Editor\SyncWarmUpStepData;
use App\Shared\Enums\BlockType;
use InvalidArgumentException;

final class RoutineBlockShape
{
    /**
     * @param  list<SyncBlockExerciseData>  $exercises
     * @param  list<SyncDropsetData>  $dropsets
     * @param  list<SyncWarmUpStepData>  $steps
     */
    public static function assert(
        BlockType $blockType,
        array $exercises,
        array $dropsets,
        array $steps,
        ?int $sharedProfileId,
    ): void {
        $isSuperset = $blockType === BlockType::Superset;
        $isCircuit = $blockType === BlockType::Circuit;

        if ($isSuperset && count($exercises) !== 2) {
            throw new InvalidArgumentException('A superset must have exactly two exercises.');
        }

        if ($blockType === BlockType::Single && count($exercises) !== 1) {
            throw new InvalidArgumentException('A non-superset must have exactly one exercise.');
        }

        if ($isCircuit && count($exercises) < 3) {
            throw new InvalidArgumentException('A circuit must have at least three exercises.');
        }

        if ($isCircuit && $sharedProfileId !== null) {
            throw new InvalidArgumentException('Shared exercise profiles are not supported on circuits.');
        }

        if ($isSuperset && $dropsets !== []) {
            throw new InvalidArgumentException('Dropsets are not supported on supersets.');
        }

        if ($isCircuit && $dropsets !== []) {
            throw new InvalidArgumentException('Dropsets are not supported on circuits.');
        }

        if ($isCircuit && $steps !== []) {
            throw new InvalidArgumentException('Warm-up steps are not supported on circuits.');
        }
    }

    public static function assertCircuitExercise(SyncBlockExerciseData $exerciseData): void
    {
        if ($exerciseData->exerciseProfileId !== null) {
            throw new InvalidArgumentException('Exercise profiles are not supported on circuits.');
        }
        if ($exerciseData->deloadExerciseId !== null || $exerciseData->deloadWorkingWeightKg !== null) {
            throw new InvalidArgumentException('Deload alternate exercises are not supported on circuits.');
        }
        if ($exerciseData->progressionTarget !== null) {
            throw new InvalidArgumentException('Automatic progression targets are not supported on circuits.');
        }
        if ($exerciseData->achievementFloor !== null) {
            throw new InvalidArgumentException('Achievement floor overrides are not supported on circuits.');
        }
    }
}
