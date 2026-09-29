<?php

namespace App\Routines\Services;

use App\ExerciseProfiles\Models\ExerciseProfile;
use App\ExerciseProfiles\Services\ExerciseProfileService;
use App\ExerciseProfiles\Support\ExerciseProfileAssignment;
use App\Exercises\Models\Exercise;
use App\Routines\Data\Editor\SyncBlockExerciseData;
use App\Routines\Data\Editor\SyncDropsetData;
use App\Routines\Data\Editor\SyncRoutineBlockData;
use App\Routines\Data\Editor\SyncWarmUpData;
use App\Routines\Data\Editor\SyncWarmUpStepData;
use App\Routines\Models\Routine;
use App\Routines\Models\RoutineBlock;
use App\Routines\Models\RoutineBlockExercise;
use App\Routines\Models\RoutineDropsetSegment;
use App\Routines\Models\RoutineSetGroup;
use App\Routines\Models\RoutineWarmUpStep;
use App\Routines\Support\RoutineBlockShape;
use App\Shared\Data\WeightKgSegmentData;
use App\Shared\Enums\BlockType;
use App\Shared\Enums\PrescriptionMode;
use App\Shared\Enums\SetGroupType;
use App\Shared\Enums\WarmUpWeightMode;
use App\Shared\Support\WarmUpStepSupport;
use App\Shared\Support\Weight;
use App\Users\Models\User;
use InvalidArgumentException;

final readonly class RoutineBlockWriter
{
    public function __construct(
        private ExerciseProfileService $exerciseProfiles,
    ) {}

    public function create(
        Routine $routine,
        int $position,
        SyncRoutineBlockData $blockData,
        bool $isLastBlock = false,
    ): void {
        $blockType = $blockData->blockType();
        $isSuperset = $blockType === BlockType::Superset;
        $isCircuit = $blockType === BlockType::Circuit;
        $exercises = $blockData->exercises->all();
        $warmUp = $blockData->warmUp ?? new SyncWarmUpData;
        $steps = $warmUp->stepList();
        $dropsets = $blockData->working->dropsetList();

        RoutineBlockShape::assert($blockType, $exercises, $dropsets, $steps, $blockData->sharedProfileId);

        $sharedProfile = $this->resolveSharedProfile($routine->user, $blockData, $exercises, $isSuperset, $isCircuit);
        $this->assertSharedProfileNotTampered($sharedProfile, $blockData, $steps);
        $sharedFingerprint = ExerciseProfileAssignment::sharedProfileFingerprint(
            $sharedProfile,
            $blockData->sharedProfileFingerprint,
        );

        $block = RoutineBlock::create([
            'routine_id' => $routine->id,
            'shared_exercise_profile_id' => $sharedProfile?->id,
            'shared_profile_fingerprint' => $sharedFingerprint,
            'position' => $position,
            'type' => $blockType,
            'is_superset' => $isSuperset,
            'stage_rest_seconds' => $isCircuit ? ($blockData->stageRestSeconds ?? 15) : null,
            'has_setup_after' => $isLastBlock ? false : $blockData->hasSetupAfter,
            'has_setup_after_warm_up' => $isCircuit ? false : $blockData->hasSetupAfterWarmUp,
        ]);

        foreach (array_values($exercises) as $index => $exerciseData) {
            /** @var SyncBlockExerciseData $exerciseData */
            $this->createBlockExercise(
                $routine->user,
                $block,
                $exerciseData,
                $index + 1,
                $isSuperset,
                $isCircuit,
                $sharedProfile,
            );
        }

        $workingGroup = RoutineSetGroup::create([
            'routine_block_id' => $block->id,
            'type' => SetGroupType::Working,
            'set_count' => $blockData->working->setCount,
            'rest_seconds' => $blockData->working->restSeconds,
        ]);

        $this->persistDropsets($workingGroup, $blockData->working->setCount, $dropsets);

        if (! $isCircuit) {
            $this->persistWarmUpGroup($block, $warmUp, $steps);
        }
    }

    /**
     * @param  list<SyncBlockExerciseData>  $exercises
     */
    private function resolveSharedProfile(
        User $user,
        SyncRoutineBlockData $blockData,
        array $exercises,
        bool $isSuperset,
        bool $isCircuit,
    ): ?ExerciseProfile {
        if ($isCircuit) {
            return null;
        }

        $sharedProfile = $this->profileFromId($user, $blockData->sharedProfileId);

        if (! $isSuperset) {
            /** @var SyncBlockExerciseData $single */
            $single = $exercises[0];
            if ($single->exerciseProfileId === null) {
                return null;
            }
        }

        return $sharedProfile;
    }

    private function createBlockExercise(
        User $user,
        RoutineBlock $block,
        SyncBlockExerciseData $exerciseData,
        int $position,
        bool $isSuperset,
        bool $isCircuit,
        ?ExerciseProfile $sharedProfile,
    ): void {
        Exercise::assertAvailableFor($user, $exerciseData->exerciseId);

        if ($isCircuit) {
            RoutineBlockShape::assertCircuitExercise($exerciseData);
        }

        if ($exerciseData->prescriptionMode === PrescriptionMode::Duration) {
            if ($exerciseData->prescribedDurationSeconds === null || $exerciseData->prescribedDurationSeconds < 1) {
                throw new InvalidArgumentException('Timed exercises require a duration of at least 1 second.');
            }
        } elseif ($exerciseData->prescribedReps === null || $exerciseData->prescribedReps < 1) {
            throw new InvalidArgumentException('Rep-based exercises require prescribed reps of at least 1.');
        }

        $exerciseProfile = $isCircuit ? null : $this->profileFromId($user, $exerciseData->exerciseProfileId);
        $this->assertExerciseProfileNotTampered($exerciseProfile, $exerciseData, $isSuperset);

        if ($exerciseData->deloadExerciseId !== null) {
            Exercise::assertAvailableFor($user, $exerciseData->deloadExerciseId);
        }

        $usesSupersetFingerprint = $isSuperset || $sharedProfile === null;
        $exerciseFingerprint = ExerciseProfileAssignment::exerciseFingerprint(
            $exerciseProfile,
            $usesSupersetFingerprint,
        );
        $exerciseAssignmentIsCurrent = $exerciseProfile !== null
            && ExerciseProfileAssignment::assignmentIsCurrent(
                $exerciseData->exerciseProfileFingerprint,
                $exerciseFingerprint,
            );
        $storedExerciseFingerprint = ExerciseProfileAssignment::storedExerciseFingerprint(
            $exerciseData->exerciseProfileFingerprint,
            $exerciseFingerprint,
        );

        RoutineBlockExercise::create([
            'routine_block_id' => $block->id,
            'exercise_profile_id' => $exerciseProfile?->id,
            'exercise_profile_fingerprint' => $storedExerciseFingerprint,
            'exercise_id' => $exerciseData->exerciseId,
            'position' => $position,
            'prescription_mode' => $exerciseData->prescriptionMode,
            'prescribed_duration_seconds' => $exerciseData->prescriptionMode === PrescriptionMode::Duration
                ? $exerciseData->prescribedDurationSeconds
                : null,
            'working_weight_g' => $exerciseData->workingWeightGrams(),
            'note' => self::normalizeNote($exerciseData->note),
            'deload_exercise_id' => $isCircuit ? null : $exerciseData->deloadExerciseId,
            'deload_working_weight_g' => $isCircuit ? null : $exerciseData->deloadWorkingWeightGrams(),
            'deload_note' => $isCircuit || $exerciseData->deloadExerciseId === null
                ? null
                : self::normalizeNote($exerciseData->deloadNote),
            'prescribed_reps' => $exerciseData->prescriptionMode === PrescriptionMode::Reps
                ? $exerciseData->prescribedReps
                : null,
            'achievement_floor_override' => $isCircuit ? null : $this->achievementFloorForStorage($exerciseData),
            'floor_is_derived' => $isCircuit ? null : $this->floorDerivationForAssignment(
                $exerciseProfile,
                $exerciseData,
                $exerciseAssignmentIsCurrent,
            ),
            'progression_target_override' => $isCircuit ? null : $exerciseData->progressionTarget,
            'deload_weight_factor' => $this->deloadWeightFactorForStorage($exerciseProfile, $exerciseData),
            'deload_reps_factor' => $this->deloadRepsFactorForStorage($exerciseProfile, $exerciseData),
        ]);
    }

    private function deloadWeightFactorForStorage(?ExerciseProfile $exerciseProfile, SyncBlockExerciseData $exerciseData): float
    {
        if ($exerciseData->deloadWeightFactor !== null) {
            return $exerciseData->deloadWeightFactor;
        }

        if ($exerciseProfile !== null) {
            return (float) $exerciseProfile->deload_weight_factor;
        }

        return 0.5;
    }

    private function deloadRepsFactorForStorage(?ExerciseProfile $exerciseProfile, SyncBlockExerciseData $exerciseData): float
    {
        if ($exerciseData->deloadRepsFactor !== null) {
            return $exerciseData->deloadRepsFactor;
        }

        if ($exerciseProfile !== null) {
            return (float) $exerciseProfile->deload_reps_factor;
        }

        return 0.5;
    }

    private static function normalizeNote(?string $note): ?string
    {
        $normalized = trim((string) $note);

        return $normalized === '' ? null : $normalized;
    }

    /**
     * @param  list<SyncWarmUpStepData>  $steps
     */
    private function persistWarmUpGroup(RoutineBlock $block, SyncWarmUpData $warmUp, array $steps): void
    {
        $warmUpGroup = RoutineSetGroup::create([
            'routine_block_id' => $block->id,
            'type' => SetGroupType::WarmUp,
            'set_count' => max(count($steps), $warmUp->setCount),
            'rest_seconds' => $warmUp->restSeconds,
        ]);

        foreach ($steps as $stepIndex => $step) {
            RoutineWarmUpStep::create([
                'routine_set_group_id' => $warmUpGroup->id,
                'position' => $stepIndex + 1,
                'weight_mode' => $step->mode,
                'percent_of_working' => $step->mode === WarmUpWeightMode::Percent
                    ? min(100, max(1, $step->percent ?? 1))
                    : null,
                'weight_g' => $step->mode === WarmUpWeightMode::Fixed && $step->weightKg !== null
                    ? Weight::kgToGrams($step->weightKg)
                    : null,
                'reps' => min(100, max(1, $step->reps)),
                'has_setup_after' => $step->hasSetupAfter,
            ]);
        }
    }

    private function profileFromId(User $user, ?int $profileId): ?ExerciseProfile
    {
        if ($profileId === null) {
            return null;
        }

        $profile = ExerciseProfile::query()->findOrFail($profileId);
        $this->exerciseProfiles->assertAssignable($user, $profile);

        return $profile;
    }

    /**
     * @param  list<SyncWarmUpStepData>  $steps
     */
    private function assertSharedProfileNotTampered(
        ?ExerciseProfile $sharedProfile,
        SyncRoutineBlockData $blockData,
        array $steps,
    ): void {
        if ($sharedProfile === null) {
            return;
        }

        if (! ExerciseProfileAssignment::assignmentIsCurrent(
            $blockData->sharedProfileFingerprint,
            $sharedProfile->recipe()->sharedFingerprint(),
        )) {
            return;
        }

        if ($this->sharedValuesMatchProfile($blockData, $sharedProfile, $steps)) {
            return;
        }

        throw new InvalidArgumentException('The shared profile values no longer match this block.');
    }

    private function assertExerciseProfileNotTampered(
        ?ExerciseProfile $exerciseProfile,
        SyncBlockExerciseData $exerciseData,
        bool $isSuperset,
    ): void {
        if ($exerciseProfile === null) {
            return;
        }

        if (! ExerciseProfileAssignment::assignmentIsCurrent(
            $exerciseData->exerciseProfileFingerprint,
            ExerciseProfileAssignment::exerciseFingerprint($exerciseProfile, $isSuperset),
        )) {
            return;
        }

        if ($this->exerciseValuesMatchProfile($exerciseData, $exerciseProfile)) {
            return;
        }

        throw new InvalidArgumentException('The exercise profile values no longer match this exercise.');
    }

    /**
     * @param  list<SyncWarmUpStepData>  $steps
     */
    private function sharedValuesMatchProfile(
        SyncRoutineBlockData $blockData,
        ExerciseProfile $profile,
        array $steps,
    ): bool {
        if ($blockData->working->restSeconds !== $profile->working_rest_seconds) {
            return false;
        }

        $warmUpSteps = array_map(
            WarmUpStepSupport::toStorage(...),
            WarmUpStepSupport::normalizeList(array_values(array_map(
                static fn (SyncWarmUpStepData $step): array => [
                    'mode' => $step->mode->value,
                    'percent' => $step->percent,
                    'weight_kg' => $step->weightKg,
                    'reps' => $step->reps,
                ],
                $steps,
            ))),
        )
            |> array_values(...);

        return $warmUpSteps === $profile->warmUpStepList();
    }

    private function exerciseValuesMatchProfile(SyncBlockExerciseData $data, ExerciseProfile $profile): bool
    {
        if ($data->prescribedReps !== $profile->target_reps) {
            return false;
        }

        $profileFloorIsDerived = $profile->floor_override === null;

        if ($data->floorIsDerived !== $profileFloorIsDerived) {
            return false;
        }

        if ($data->floorIsDerived) {
            return true;
        }

        return $data->achievementFloor === $profile->floor_override;
    }

    private function floorDerivationForAssignment(
        ?ExerciseProfile $profile,
        SyncBlockExerciseData $data,
        bool $assignmentIsCurrent,
    ): ?bool {
        if ($profile === null || ! $assignmentIsCurrent) {
            return $data->floorIsDerived;
        }

        return $profile->floor_override === null;
    }

    private function achievementFloorForStorage(SyncBlockExerciseData $data): ?int
    {
        if ($data->floorIsDerived === true) {
            return null;
        }

        return $data->achievementFloor;
    }

    /**
     * @param  list<SyncDropsetData>  $dropsets
     */
    private function persistDropsets(RoutineSetGroup $workingGroup, int $setCount, array $dropsets): void
    {
        $seenIndexes = [];

        foreach ($dropsets as $dropset) {
            if ($dropset->setIndex < 0 || $dropset->setIndex >= $setCount) {
                throw new InvalidArgumentException(
                    "Dropset set index {$dropset->setIndex} is outside working set count {$setCount}."
                );
            }

            if (isset($seenIndexes[$dropset->setIndex])) {
                throw new InvalidArgumentException(
                    "Duplicate dropset entry for set index {$dropset->setIndex}."
                );
            }

            $seenIndexes[$dropset->setIndex] = true;

            $segments = array_values($dropset->segments->all());

            if (count($segments) < 2) {
                throw new InvalidArgumentException('A dropset requires at least two segments.');
            }

            foreach ($segments as $segmentIndex => $segment) {
                /** @var WeightKgSegmentData $segment */
                RoutineDropsetSegment::create([
                    'routine_set_group_id' => $workingGroup->id,
                    'set_index' => $dropset->setIndex,
                    'position' => $segmentIndex + 1,
                    'weight_g' => $segment->weightGrams(),
                ]);
            }
        }
    }
}
